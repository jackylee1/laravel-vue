<template>
    <div style="background-color:#545c64;width: 100%;height:100%;top:0;left:0;position: absolute">
        <el-row>
            <el-col :span="6" :offset="8" style="background-color:white;margin-top:10%;padding:30px;border-radius:10px">
                <h2>代理人系统</h2>
                <br>
                <el-form inline :model="loginForm" :rules="rules" ref="form">
                    <el-form-item label="邮箱" prop="email">
                        <el-input type="email" v-model="loginForm.email" placeholder=""></el-input>
                    </el-form-item>
                    <el-form-item label="密码" prop="password">
                        <el-input type="password" v-model="loginForm.password" placeholder=""></el-input>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="login">登录</el-button>
                    </el-form-item>
                </el-form>
            </el-col>
        </el-row>
    </div>
</template>
<script>
  export default {
    data () {
      return {
        loginForm: {
          email: null,
          password: null
        },
        rules: {
          email: [
            { required: true, type: 'email', message: '邮箱格式不正确', trigger: 'blur' }
          ],
          password: [
            { required: true, message: '请输入密码', trigger: 'blur' }
          ]
        }
      }
    },
    methods: {
      login () {
        this.$refs['form'].validate((valid) => {
          if (valid) {
            this.$http.post('login', this.loginForm).then(ret => {
              console.log(ret.headers['api-token'])
              localStorage.setItem('token', ret.headers['api-token'])
              localStorage.setItem('hash', ret.headers['api-token-hash'])
              localStorage.setItem('admin', JSON.stringify(ret.data))
              this.$router.push('/')
            }).catch(err => {
              console.log(err)
              this.$message.error('账号或密码错误！')
            })
          }
        })
      }
    }
  }
</script>