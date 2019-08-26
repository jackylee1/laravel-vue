<template>
    <div>
        <el-row>
            <el-form :inline="true" :model="search" class="demo-form-inline">
                <el-form-item label="">
                    <el-input v-model="search.keyword" style="width: 380px" placeholder="手机号/姓名"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="doSearch">查询</el-button>
                </el-form-item>
            </el-form>
        </el-row>

        <el-row v-if="member">
            <el-col>
                <el-table v-loading="loading" :data="member['member']['data']">
                    <el-table-column
                            prop="id"
                            label="编号"
                            width="80">
                    </el-table-column>
                    <el-table-column
                            prop="mobile"
                            label="手机号">
                    </el-table-column>
                    <el-table-column
                            prop="nick_name"
                            label="昵称">
                    </el-table-column>
                    <el-table-column
                            label="操作">
                        <template slot-scope="scope">
                            <router-link :to="{name:'order', query:{member_id:scope.row.id}}">
                                <el-button size="small" type="info">订单列表</el-button>
                            </router-link>
                        </template>
                    </el-table-column>
                </el-table>
            </el-col>
        </el-row>
        <el-row v-if="member">
            <el-pagination
                    @current-change="handleCurrentChange"
                    :current-page="member['member']['current_page']"
                    :page-size="member['member']['per_page']"
                    layout="total, prev, pager, next, jumper"
                    :total="member['member']['total']">
            </el-pagination>
        </el-row>
    </div>
</template>
<script>
  export default {
    data () {
      return {
        member: null,
        search: {
          keyword: ''
        },
        loading: false
      }
    },
    mounted () {
      this.all()
    },
    methods: {
      handleCurrentChange (val) {
        this.loading = true
        this.$http.get('member?page=' + val).then(ret => {
          this.member  = ret.data
          this.loading = false
        })
      },
      all () {
        this.loading = true
        this.$http.get('member').then(ret => {
          this.member  = ret.data
          this.loading = false
        })
      },

      // 查找
      doSearch () {
        this.loading = true
        this.$http.get('member?keyword=' + this.search.keyword).then(ret => {
          this.member  = ret.data
          this.loading = false
        })
      }
    }
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
