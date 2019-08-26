<?php
/**
 * 善诊
 */

namespace App\Services\Providers;

use App\Models\PhysicalExaminationQualification;
use App\Models\PhysicalExaminationReservation;
use App\Services\Aliyun\AliSmsService;

class ShanZhenService
{

    use CurlTrait;

    public function order(PhysicalExaminationQualification $qualification)
    {
        $data         = [
            'pid'       => config('provider.shanzhen.pid'),
            'timestamp' => time(),
            'nonce'     => (string)time(),
            'equity_id' => (string)$qualification->combo_code,
            'mobile'    => (string)$qualification->member->mobile,
        ];
        $data['sign'] = $this->getSign($data);

        $ret = $this->getClient()->request(config('provider.shanzhen.url.order'), $data, 'post');

        if ($ret['code'] != 'S0000') {
            $qualification->update([
                'reason' => $ret['error'],
                'status' => PhysicalExaminationQualification::STATUS_FAILED
            ]);
        } else {
            $qualification->update([
                'out_order_no' => $ret['order_no'],
                'status'       => PhysicalExaminationQualification::STATUS_ORDERED
            ]);
        }
    }

    public function orderNotify(array $data)
    {
        \Log::notice('善诊订单通知：', $data);
        try {
            $this->verify($data);

            $qualification = PhysicalExaminationQualification::where('out_order_no', $data['order_no'])
                ->first();
            if (!$qualification) {
                return ['code' => 'fail'];
            }

            // 目前订单状态推送 只支持预约成功后 推送，同时需要你们提供推送地址(善诊方说)
            switch ($data['order_status']) {
                case 'ORDERED':
                    // 已下单，不作处理，因为下单接口已经同步处理过了
                    break;
                case 'RESERVED':
                    // 已预约
                    $reservation = [
                        'physical_examination_qualification_id' => $qualification->id,
                        'name'                                  => $data['customer_name'],
                        'mobile'                                => $data['customer_phone'],
                        'identity'                              => $data['customer_identity_no'],
                        'date'                                  => date('Y-m-d H:i:s', strtotime($data['appointment_date'])),
                        'org_id'                                => $data['org_id'] ?? '',
                        'org_name'                              => $data['org_name'] ?? '',
                        'org_address'                           => $data['org_address'] ?? '',
                    ];
                    \DB::transaction(function () use ($reservation, $qualification) {
                        PhysicalExaminationReservation::firstOrCreate($reservation); // 防止重复插入
                        $qualification->update([
                            'status' => PhysicalExaminationQualification::STATUS_RESERVED
                        ]);
                        // 发送预约成功的短信
                        //亲，您的订单${order}已预约。体检人：${name}，体检时间：${date}，套餐：${package}，机构：${org}，地址：${address}。
                        //体检当日需空腹，携带本人身份证直接前往体检中心。在微信公众号个人中心中查看订单状态。如有疑问请拨打客服热线4008-208-243。
                        $sms    = [
                            'order'   => substr($qualification->out_order_no, -6), // 阿里云短信变量长度有20位的最大限制
                            'name'    => $reservation['name'],
                            'package' => $qualification->title,
                            'org'     => $reservation['org_name'],
                            'address' => $reservation['org_address'],
                            'date'    => date('Y-m-d', strtotime($reservation['date'])),
                        ];
                        $smsApi = new AliSmsService();
                        $smsApi->sendSms($reservation['mobile'], 'SMS_133972809', $sms);
                        if ($reservation['mobile'] != $qualification->member->mobile) {
                            // 如果体检和下单人不是同一人,则给下单人也发送
                            $smsApi->sendSms($qualification->member->mobile, 'SMS_133972809', $sms);
                        }
                    });
                    break;
                case 'REPORTED':
                    // 已出报告(目前善诊还没提供这个状态的推送，只是预留)
                    $qualification->update([
                        'status' => PhysicalExaminationQualification::STATUS_REPORTED
                    ]);
                    break;
                case 'CANCELED':
                    $qualification->update([
                        'status' => PhysicalExaminationQualification::STATUS_CANCELED
                    ]);
                    break;
                case 'PROCESSING':
                    break;
            }

            return ['code' => 'success'];
        } catch (\Exception $e) {
            \Log::notice('善诊订单通知，处理失败：' . $e->getMessage());

            return ['code' => 'fail'];
        }
    }

    public function reportNotify(array $data)
    {
        \Log::notice('善诊报告通知：', $data);
        try {
            unset($data['pdf_report_data'], $data['report_voice_data']); // 不参与签名
            $this->verify($data);

            $qualification = PhysicalExaminationQualification::where('out_order_no', $data['order_no'])
                ->first();
            if ($qualification) {
                $qualification->update([
                    'status' => PhysicalExaminationQualification::STATUS_REPORTED
                ]);
                // 发送生成报告的短信
                //您好，${name}的体检报告已生成，微信关注“星邻计划”公众号，个人中心中查看体检报告，快来了解您的健康情况吧。客服热线4008-208-243。
                $sms = [
                    'name' => $qualification->reservation->name
                ];
                (new AliSmsService())->sendSms($qualification->member->mobile, 'SMS_133967862', $sms);
            }

            return ['code' => 'success'];
        } catch (\Exception $e) {
            \Log::notice('善诊报告通知，处理失败：' . $e->getMessage());

            return ['code' => 'fail'];
        }
    }

    public function getReservationUrl(PhysicalExaminationQualification $qualification)
    {
        $data            = [
            'pid'       => config('provider.shanzhen.pid'),
            'timestamp' => time(),
            'nonce'     => (string)time(),
            'orderno'   => $qualification->out_order_no,
        ];
        $data['sign']    = $this->getSign($data);
        $data['orderNo'] = $data['orderno']; // 坑爹的善诊文档。。。 字段名有问题
        unset($data['orderno']);

        return config('provider.shanzhen.url.reserve') . '?' . http_build_query($data);
    }

    /**
     * 查询用户权益列表
     * @param PhysicalExaminationQualification $qualification
     * @return bool
     */
    public function query(PhysicalExaminationQualification $qualification)
    {
        $data         = [
            'pid'       => config('provider.shanzhen.pid'),
            'timestamp' => time(),
            'nonce'     => (string)time(),
            'mobile'    => (string)$qualification->member->mobile
        ];
        $data['sign'] = $this->getSign($data);

        $ret = $this->getClient()->request(config('provider.shanzhen.url.query'), $data, 'post');

        if ($ret['code'] != 'S0000') {
            // 接口返回失败，忽略
            return false;
        }

        foreach ($ret['equity_list'] as $item) {
            // todo 已取消事件
            //if ($item['order_status'] == '') {
            //
            //}
            $reservation = $qualification->reservation;
            if ($item['order_no'] == $qualification->out_order_no && $item['order_status'] == 'ORDERED') {
                // 取消后，状态变回 "已下单"
                \DB::transaction(function () use ($qualification, $reservation) {
                    // 重置状态
                    $qualification->update([
                        'status' => PhysicalExaminationQualification::STATUS_ORDERED
                    ]);
                    $reservation->delete(); // 删除历史预约
                });
            } elseif ($item['order_no'] == $qualification->out_order_no && date('Y-m-d', strtotime($item['appointment_date'])) != $reservation->date) {
                // 如果改了预约日期
                $reservation->update([
                    'date' => $item['appointment_date']
                ]);
            }
        }
    }

    /**
     * 获取数据签名
     * @param array $orign
     * @return string
     */
    private function getSign(array $orign)
    {
        ksort($orign);
        $tmp = http_build_query($orign) . config('provider.shanzhen.key');
        $tmp = urldecode($tmp);

        return strtolower(sha1($tmp));
    }

    /**
     * 验证签名
     * @param $data
     * @throws \Exception
     */
    private function verify($data)
    {
        $sign = array_pull($data, 'sign');

        if ($sign != $this->getSign($data)) {
            throw new \Exception('签名验证错误!');
        }
    }
}
