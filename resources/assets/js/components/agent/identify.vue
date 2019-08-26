<template>
    <el-row>
        <el-col :span="12">
            <table v-if="agent" cellpadding="5" cellspacing="5">
                <tr>
                    <th width="200">姓名：</th>
                    <td>{{ agent.member.name }}</td>
                </tr>
                <tr>
                    <th>身份证：</th>
                    <td>{{ agent.member.identity }}</td>
                </tr>
                <tr>
                    <th>身份证照片：</th>
                    <td>
                        <img width="400" height="200" :src="agent.member.identity_files['front']" alt="">
                        <img width="400" height="200" :src="agent.member.identity_files['back']" alt="">
                    </td>
                </tr>
                <tr>
                    <th>身份证有效期：</th>
                    <td>{{ agent.member.valid_date }}</td>
                </tr>
                <tr>
                    <th>上级：</th>
                    <td>{{agent.parent ? agent.parent.member.name :''}}</td>
                </tr>
                <tr>
                    <th>认证状态：</th>
                    <td>
                        <span v-if="agent.member.identify_status"> {{ options['identify_status'][agent.member.identify_status] }}</span>
                    </td>
                </tr>
                <tr>
                    <th></th>
                    <td v-show="agent.member.identify_status == 2">
                        <el-button type="primary" @click="action('pass')">通过</el-button>
                        <el-button type="warning" @click="action('reject')">拒绝</el-button>
                    </td>
                </tr>
            </table>
        </el-col>
    </el-row>
</template>
<script>
  export default {
    data () {
      return {
        agent: null,
        options: []
      }
    },
    created () {
      this.detail()
    },
    methods: {
      detail () {
        this.$http.get('agent/' + this.$route.params.id).then(ret => {
          this.agent   = ret.data.agent
          this.options = ret.data.options
        })
      },
      action (act) {
        let status = null
        if (act == 'pass') {
          status = 3
        } else {
          status = 4
        }
        this.$http.patch('agent/' + this.$route.params.id, { identify_status: status }).then(ret => {
          this.$message.success('操作成功')
          this.detail()
        })
      }
    }
  }
</script>