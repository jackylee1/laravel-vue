<template>
    <el-row>
        <el-col :span="8">
            <el-form ref="form" :model="form" :rules="rules" label-width="120px">
                <el-form-item label="体检人姓名" prop="name">
                    <el-input v-model="form.name"></el-input>
                </el-form-item>
                <el-form-item label="体检人手机号" prop="mobile1">
                    <el-input v-model="form.mobile1"></el-input>
                </el-form-item>
                <el-form-item label="套餐名称" prop="package">
                    <el-input v-model="form.package"></el-input>
                </el-form-item>
                <el-form-item label="体检机构名称" prop="org">
                    <el-input v-model="form.org"></el-input>
                </el-form-item>
                <el-form-item label="体检机构地址" prop="address">
                    <el-input v-model="form.address"></el-input>
                </el-form-item>
                <el-form-item label="预约日期" prop="date">
                    <el-date-picker
                            v-model="form.date"
                            type="date"
                            value-format="yyyy-MM-dd"
                            placeholder="选择日期">
                    </el-date-picker>
                </el-form-item>
                <el-form-item label="下单人手机号" prop="mobile">
                    <el-input v-model="form.mobile"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-button type="info" @click="send">发送短信</el-button>
                </el-form-item>
            </el-form>
        </el-col>
    </el-row>
</template>
<script>

  export default {
    data () {
      return {
        form: {
          name: '',
          org: '',
          address: '',
          package: '',
          date: '',
          mobile1: '',
          mobile: ''
        },
        rules: {
          name: [{ required: true, message: '请输入体检人姓名', trigger: 'blur' }],
          mobile1: [{ required: true, message: '请输入体检人手机号', trigger: 'blur' }],
          package: [{ required: true, message: '请输入套餐名称', trigger: 'blur' }],
          org: [{ required: true, message: '请输入体检机构名称', trigger: 'blur' }],
          address: [{ required: true, message: '请输入体检机构地址', trigger: 'blur' }],
          date: [{ required: true, message: '请选择体检日期', trigger: 'blur' }],
          mobile: [{ required: true, message: '请输入下单人手机号', trigger: 'blur' }]
        }
      }
    },
    methods: {
      send () {
        this.$refs['form'].validate((valid) => {
          if (!valid) {
            this.$notify({
              title: '错误',
              message: '信息填写不完整',
              type: 'error'
            })
          } else {
            this.$http.post('common/sms', this.form).then(ret => {
              this.$message.success('发送成功')
            }).catch(e => {
              console.log(e)
              this.$message.error('发送失败')
            })
          }
        })
      }
    }
  }
</script>

<style scoped>
    .el-cascader {
        width: 100%
    }
</style>
