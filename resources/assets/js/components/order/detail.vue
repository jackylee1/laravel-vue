<template>
    <div>
        <table style="text-align: right" cellpadding="10">
            <tr>
                <th>用户：</th>
                <td>{{ detail.member.id }}--{{ detail.member.nick_name }}</td>
            </tr>
            <tr>
                <th>订单名称：</th>
                <td>{{ detail.title }}</td>
            </tr>
            <tr>
                <th>购买产品：</th>
                <td>{{ detail.product_id }}</td>
            </tr>
            <tr>
                <th>订单状态：</th>
                <td>{{ options[detail.status] }}</td>
            </tr>
            <tr>
                <th>订单金额：</th>
                <td>{{ (detail.amount/100).toFixed(2) }} 元</td>
            </tr>
            <tr>
                <th>订单创建时间：</th>
                <td>{{ detail.created_at }}</td>
            </tr>
            <template v-if="detail.payment">
                <tr>
                    <th>内部支付编号：</th>
                    <td>{{ detail.payment.inner_transaction_no }}</td>
                </tr>
                <tr>
                    <th>外部支付编号：</th>
                    <td>{{ detail.payment.transaction_no }}</td>
                </tr>
            </template>
        </table>
    </div>
</template>

<script>
  export default {
    data () {
      return {
        detail: {},
        options: []
      }
    },
    created () {
      this.$http.get('order/' + this.$route.params.id).then(ret => {
        this.detail  = ret.data.order
        this.options = ret.data.options
      })
    }
  }
</script>