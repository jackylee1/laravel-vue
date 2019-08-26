<?php

function path_injection($path, $object)
{
    /** @var  $object \Illuminate\Database\Eloquent\Model */
    $arr = $object->getAttributes();
    foreach ($arr as $key => $value) {
        $path = str_replace('{' . $key . '}', $value, $path);
    }
    //关联关系填充支持 未来简易使用blade模板方式的方式实现
    if (\Illuminate\Support\Str::contains($path, '.')) { //防止不必要的关联和正则查询
        $matches = [];
        if (preg_match_all('/(\{(\w+)\.(\w+)\})/', $path, $matches)) {
            foreach ($matches[2] as $matchKey => $relationName) {
                $relationValue = $matches[3][$matchKey];
                $patten        = sprintf('{%s.%s}', $relationName, $relationValue);
                $rel           = $object->getRelation($relationName);
                if ($rel) {
                    $replacement = $object->getRelation($relationName)->getAttribute($relationValue);
                    $path        = str_replace($patten, $replacement, $path);
                }
            }
        };
    }

    return $path;
}

function isWeChat()
{
    return strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'micromessenger') > 0;
}

/**
 * 获取树型结构
 *
 * @param $id
 * @param $data
 *
 * @return array
 */
function getTree($id, $data)
{
    $ret = [];
    foreach ($data as $key => $val) {
        if ($val['parent_id'] == $id) {
            $tmp = $data[$key];
            unset($data[$key]);
            count(getTree($val['id'], $data)) && $tmp['children'] = getTree($val['id'], $data);
            $ret[] = $tmp;
        }
    }

    return $ret;
}

function gmt_iso8601($time)
{
    $dtStr      = date("c", $time);
    $mydatetime = new \DateTime($dtStr);
    $expiration = $mydatetime->format(DateTime::ISO8601);
    $pos        = strpos($expiration, '+');
    $expiration = substr($expiration, 0, $pos);

    return $expiration . "Z";
}

function request_by_curl($remote_server, $post_string)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $remote_server);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=utf-8'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
}

/**
 * 假设一个数组有1万个，某个接口一次只能最多提交50个，你需要把这个数组切割成 50个小数组依次返回
 * 这个生成器就做了这个工作：典型应用：微信群发，一次只能发50个openID
 * 使用的时候 直接按照数组 foreach 循环使用即可 //ihipop@gmail.com
 *
 * @param  array $array  需要分组的数组
 * @param int    $number 每组数量
 *
 * @return \Generator
 */
function yield_array_by_number(array $array, int $number)
{
    $array         = array_reverse($array);
    $arrayInNumber = [];

    while (count($array) > 0) {
        $arrayInNumber[] = array_pop($array);
        if (count($arrayInNumber) >= $number || (count($array) <= 0)) {
            yield $arrayInNumber;
            $arrayInNumber = [];
        }
    }
}
