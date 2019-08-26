<template>
    <div>
        <el-row v-if="withdraw">
            <el-col>
                <el-table :data="withdraw['withdraw']['data']">
                    <el-table-column
                            prop="id"
                            label="编号"
                            width="80">
                    </el-table-column>
                    <el-table-column
                            prop="agent.name"
                            label="代理人">
                    </el-table-column>
                    <el-table-column
                            prop="amount"
                            label="申请金额">
                        <template slot-scope="scope">
                            ¥{{(scope.row.amount / 100).toFixed(2)}}
                        </template>
                    </el-table-column>
                    <el-table-column
                            prop="status"
                            label="状态">
                        <template slot-scope="scope">
                            <el-tag
                                    :type="scope.row.status === 1 ? 'info' : (scope.row.status === 2 ? 'success' : 'danger')">
                                {{ withdraw['options'].status[scope.row.status] }}
                            </el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column
                            prop="bank_name"
                            label="开户姓名">
                    </el-table-column>
                    <el-table-column
                            prop="bank_account"
                            label="银行账户">
                    </el-table-column>
                    <el-table-column
                            prop="created_at"
                            label="申请时间">
                    </el-table-column>
                    <el-table-column
                            prop="remark"
                            label="审核备注">
                    </el-table-column>
                    <el-table-column label="操作">
                        <template slot-scope="scope">
                            <el-popover
                                    placement="bottom-start"
                                    width="360" v-model="scope.row.success">
                                <p>确认打款成功？</p>
                                <el-input type="textarea" v-model="scope.row.successRemark" placeholder="填写打款信息"></el-input>
                                <div style="text-align: right; margin-top: 10px;">
                                    <el-button size="mini" type="text" @click="scope.row.success = false">取消</el-button>
                                    <el-button type="primary" size="mini" @click="withdrawSuccess(scope.$index, scope.row), scope.row.success = false">确定</el-button>
                                </div>
                                <el-button size="small" :disabled="scope.row.status == 1 ? false : true" type="primary" slot="reference">已打款</el-button>
                            </el-popover>

                            <el-popover
                                    placement="bottom-start"
                                    width="360" v-model="scope.row.fail">
                                <p>确认驳回提现申请？</p>
                                <el-input type="textarea" v-model="scope.row.failRemark" placeholder="填写打款信息"></el-input>
                                <div style="text-align: right; margin-top: 10px;">
                                    <el-button size="mini" type="text" @click="scope.row.fail = false">取消</el-button>
                                    <el-button type="primary" size="mini" @click="withdrawFail(scope.$index, scope.row), scope.row.fail = false">确定</el-button>
                                </div>
                                <el-button size="small" :disabled="scope.row.status == 1 ? false : true" type="warning" slot="reference">驳回</el-button>
                            </el-popover>
                        </template>
                    </el-table-column>
                </el-table>
            </el-col>
        </el-row>
        <el-row v-if="withdraw">
            <el-pagination
                    @current-change="handleCurrentChange"
                    :current-page="withdraw['withdraw']['current_page']"
                    :page-size="withdraw['withdraw']['per_page']"
                    layout="total, prev, pager, next, jumper"
                    :total="withdraw['withdraw']['total']">
            </el-pagination>
        </el-row>
    </div>
</template>
<script>
  export default {
    data () {
      return {
        withdraw: null,
        importLoading: false
      }
    },
    mounted () {
      this.all()
    },

    // 必须要有一定的触发条件才能执行，如点击事件
    methods: {
      handleCurrentChange (val) {
        this.$http.get('withdraw?page=' + val).then(ret => {
          this.withdraw = ret.data
        })
      },
      all () {
        this.$http.get('withdraw').then(ret => {
          this.withdraw = ret.data
        })
      },
      // 打款成功
      withdrawSuccess (index, scope) {
        let formData = {
          id: scope.id,
          status: 2,
          remark: scope.successRemark
        }
        this.$http.post('withdraw/status', formData).then(ret => {
          if (ret.data == true) {
            this.withdraw['withdraw']['data'][index].status = formData.status
            this.$notify({
              type: 'success',
              message: '打款成功！'
            })
          } else {
            this.$notify({
              type: 'error',
              message: ret.data.message
            })
          }
        })
      },

      // 打款失败
      withdrawFail (index, scope) {
        let formData = {
          id: scope.id,
          status: 3,
          remark: scope.failRemark
        }
        this.$http.post('withdraw/status', formData).then(ret => {
          if (ret.data == true) {
            this.withdraw['withdraw']['data'][index].status = formData.status
            this.$notify({
              type: 'success',
              message: '驳回成功！'
            })
          } else {
            this.$notify({
              type: 'error',
              message: ret.data.message
            })
          }
        })
      }
    },

    // HTML DOM加载后马上执行的，如赋值；
    computed: {}
  }
</script>
<style>
    .el-transfer-panel {
        height: 350px;
    }

    .el-agent {
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
