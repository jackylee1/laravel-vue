<template>
    <div>
        <el-row>
            <el-form :inline="true" :model="search" class="demo-form-inline">
                <el-form-item label="购买人">
                    <el-input v-model="search.user" placeholder=""></el-input>
                </el-form-item>
                <el-form-item label="代理人">
                    <el-input v-model="search.agent" placeholder=""></el-input>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="doSearch">查询</el-button>
                </el-form-item>
            </el-form>
        </el-row>
        <el-row v-if="orders['orders']">
            <el-col>
                <el-table :data="orders['orders']['data']">
                    <el-table-column
                            prop="id"
                            label="订单编号">
                    </el-table-column>
                    <el-table-column
                            prop="member"
                            label="购买人(昵称)">
                        <template slot-scope="scope">
                            {{ scope.row.member.nick_name }}
                        </template>
                    </el-table-column>
                    <el-table-column
                            prop="agent"
                            label="代理人(昵称)">
                        <template slot-scope="scope">
                            {{ scope.row.agent ? scope.row.agent.name : '' }}
                        </template>
                    </el-table-column>
                    <el-table-column
                            prop="product"
                            label="产品">
                        <template slot-scope="scope">
                            <router-link :to="{ name:'product_detail', params:{id:scope.row.product_id} }">{{ scope.row.product.name }}</router-link>
                        </template>
                    </el-table-column>
                    <el-table-column
                            prop="category"
                            label="类别">
                        <template slot-scope="scope">
                            <el-tag>{{ orders['options']['category'][scope.row.category] }}</el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column
                            prop="amount"
                            label="金额">
                        <template slot-scope="scope">
                            ¥{{(scope.row.amount / 100).toFixed(2)}}
                        </template>
                    </el-table-column>
                    <el-table-column
                            prop="commission"
                            label="总佣金(代理人根据比例分配)">
                        <template slot-scope="scope">
                            ¥{{(scope.row.commission / 100).toFixed(2)}}
                        </template>
                    </el-table-column>
                    <el-table-column
                            prop="status"
                            label="状态">
                        <template slot-scope="scope">
                            {{ orders['options']['status'][scope.row.status] }}
                        </template>
                    </el-table-column>
                    <el-table-column
                            prop="created_at"
                            label="下单时间">
                    </el-table-column>
                    <el-table-column
                            label="支付时间">
                        <template slot-scope="scope">
                            {{scope.row.payment ? scope.row.payment.paid_at : ''}}
                        </template>
                    </el-table-column>
                    <el-table-column label="操作">
                        <template slot-scope="scope">
                            <router-link :to="{name:'order_detail', params:{id:scope.row.id}}">
                                <el-button size="small" type="info" icon="el-icon-edit">查看</el-button>
                            </router-link>
                        </template>
                    </el-table-column>
                </el-table>
            </el-col>
        </el-row>
        <el-row v-if="orders['orders']">
            <el-pagination
                    @current-change="handleCurrentChange"
                    :current-page="orders['orders']['current_page']"
                    :page-size="orders['orders']['per_page']"
                    layout="total, prev, pager, next, jumper"
                    :total="orders['orders']['total']">
            </el-pagination>
        </el-row>
    </div>
</template>
<script>
  export default {
    data () {
      return {
        orders: [],
        search: {
          user: '',
          agent: ''
        }
      }
    },
    mounted () {
      this.all()
    },
    methods: {
      handleCurrentChange (val) {
        this.$http.get('order?page=' + val + '&user=' + this.search.user + '&agent=' + this.search.agent).then(ret => {
          this.orders = ret.data
        })
      },
      all () {
        let user = this.$route.query.member_id
        let url  = ''
        if (user != undefined) {
          url = 'order?member_id=' + user
        } else {
          url = 'order'
        }
        this.$http.get(url).then(ret => {
          this.orders = ret.data
        })
      },
      doSearch () {
        this.$http.get('order?user=' + this.search.user + '&agent=' + this.search.agent).then(ret => {
          this.orders = ret.data
        })
      }
    }
  }
</script>
<style>
    .el-transfer-panel {
        height: 350px;
    }

    .el-order {
        margin: 0 2px;
    }

    .el-table__header, .el-table__body {
        width: 100% !important;
        table-layout: auto !important;
    }

    .el-row {
        margin-bottom: 20px;
    }

    .notice {
        color: red;
        font-weight: bold;
        font-style: italic
    }
</style>
