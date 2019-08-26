<template>
    <div>
        <el-row>
            <el-col :span="2">
                <el-form :inline="true" class="demo-form-inline">
                    <el-form-item>
                        <el-upload
                                action=""
                                :http-request="importUpload"
                                accept=".xlsx"
                                :show-file-list="false"
                                :before-upload="importBeforeUpload"
                                :on-progress="importProgress"
                                :on-error="importError"
                                :on-success="importSuccess">
                            <el-button type="info" icon="el-icon-upload2">导入代理人</el-button>
                            <span v-show="importLoading">导入中...</span>
                        </el-upload>
                    </el-form-item>
                </el-form>
            </el-col>
            <el-col :span="8" :offset="2">
                <el-form :inline="true" :model="search" class="demo-form-inline">
                    <el-form-item label="昵称">
                        <el-input v-model="search.nick_name" placeholder=""></el-input>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="all">查询</el-button>
                    </el-form-item>
                </el-form>
            </el-col>
        </el-row>
        <el-row v-if="agents">
            <el-col>
                <el-table :data="agents['agents']['data']">
                    <el-table-column
                            prop="id"
                            label="编号"
                            width="80">
                    </el-table-column>
                    <el-table-column
                            label="上级(昵称)">
                        <template slot-scope="scope">
                            <router-link v-if="scope.row.parent" :to="{name:'agent_tree', query:{nick_name:scope.row.parent.name}}">
                                {{ scope.row.parent.name }}
                            </router-link>
                        </template>
                    </el-table-column>
                    <el-table-column
                            prop="member.mobile"
                            label="手机号">
                    </el-table-column>
                    <el-table-column
                            prop="member.nick_name"
                            label="昵称(点击查看层级关系)">
                        <template slot-scope="scope">
                            <router-link :to="{name:'agent_tree', query:{nick_name:scope.row.name}}">
                                {{ scope.row.name }}
                            </router-link>
                        </template>
                    </el-table-column>
                    <el-table-column
                            prop="member.name"
                            label="姓名">
                    </el-table-column>
                    <el-table-column
                            prop="order_count"
                            label="出单数">
                    </el-table-column>
                    <el-table-column
                            label="身份证">
                        <template slot-scope="scope">
                            {{ scope.row.member.identity ? (scope.row.member.identity.substr(0,6) + '********' + scope.row.member.identity.substr(14)) : ''}}
                        </template>
                    </el-table-column>
                    <el-table-column
                            label="身份认证状态">
                        <template slot-scope="scope">
                            <el-tag :type="scope.row.member.identify_status == 2?'primary':(scope.row.member.identify_status == 3?'success': (scope.row.member.identify_status == 4?'warning':'info'))"
                                    v-if="scope.row.member.identify_status"> {{ agents['options']['identify_status'][scope.row.member.identify_status] }}
                            </el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column label="操作">
                        <template slot-scope="scope">
                            <router-link v-show="scope.row.member.identify_status != 1" :to="{name:'agent_identify', params:{id:scope.row.id}}">
                                <el-button size="small" type="info">身份证认证审核</el-button>
                            </router-link>
                        </template>
                    </el-table-column>
                </el-table>
            </el-col>
        </el-row>
        <el-row v-if="agents">
            <el-pagination
                    @current-change="handleCurrentChange"
                    :current-page="agents['agents']['current_page']"
                    :page-size="agents['agents']['per_page']"
                    layout="total, prev, pager, next, jumper"
                    :total="agents['agents']['total']">
            </el-pagination>
        </el-row>
    </div>
</template>
<script>
  export default {
    data () {
      return {
        search: {
          nick_name: null
        },
        agents: null,
        importLoading: false
      }
    },
    mounted () {
      this.all()
    },

    // 必须要有一定的触发条件才能执行，如点击事件
    methods: {
      handleCurrentChange (val) {
        this.$http.get('agent?page=' + val).then(ret => {
          this.agents = ret.data
        })
      },
      all () {
        this.$http.get('agent', { params: this.search }).then(ret => {
          this.agents = ret.data
        })
      },
      importSearch () {
        console.log('导入代理人')
      },

      // 上传前钩子
      importBeforeUpload (file) {
        this.importLoading = true
      },
      // 覆盖默认上传模式
      importUpload (file) {
        let form = new FormData()
        form.append('file', file.file)

        let config = {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        }
        this.$http.post('agent/import', form, config).then(ret => {
          this.importLoading = false

          this.$notify({
            type: 'success',
            message: '导入代理人成功'
          })

          this.all()
        })
      },
      // 文件上传时触发
      importProgress (event, file, fileList) {
        console.log('importProgress')
      },
      // 上传成功提示
      importSuccess (response, file, fileList) {
        this.$notify({
          type: 'success',
          message: '导入代理人成功'
        })
        this.importLoading = false
      },
      // 上传错误触发
      importError (err, file, fileList) {
        console.log('importError')
        this.importLoading = false
      }
    },

    // HTML DOM加载后马上执行的，如赋值；
    computed: {
      //导入代理人URL
      importUrl () {
        return `admin/agent/import`
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
