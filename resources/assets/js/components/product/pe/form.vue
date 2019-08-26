<template>
    <div>
        <el-row>
            <el-col :span="5">
                <p><strong>① 体检包基础信息：</strong></p>
                <el-form :model="form" :rules="rules" ref="form" label-width="100px" class="demo-form">
                    <el-form-item label="名称" prop="title">
                        <el-input v-model="form.title"></el-input>
                    </el-form-item>
                    <el-form-item label="类别" prop="type">
                        <el-radio-group v-model="form.type">
                            <el-radio :disabled="combo.basic != null" :label="1">基础包</el-radio>
                            <el-radio :label="2">叠加包</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item label="价格" prop="price">
                        <el-input type="number" v-model.number="form.price">
                            <template slot="prepend">¥</template>
                        </el-input>
                    </el-form-item>
                    <el-form-item label="外部编号">
                        <el-input v-model="form.extra_no"></el-input>
                    </el-form-item>
                    <el-form-item label="协议名称">
                        <el-input v-model="form.protocol_name"></el-input>
                    </el-form-item>
                    <el-form-item label="协议地址">
                        <el-input type="url" v-model="form.protocol_url"></el-input>
                    </el-form-item>
                    <el-form-item>
                        <el-button v-if="form.id == null" type="primary" @click="submitForm('form')">添加</el-button>
                        <el-button v-else type="warning" @click="updateForm('form')">更新</el-button>
                        <el-button @click="resetForm">重置</el-button>
                    </el-form-item>
                </el-form>
            </el-col>
            <el-col :span="18" :offset="1">
                <combo v-if="combo.pes.length>0" ref="combo" :pes="combo.pes" :basic="combo.basic" :extra="combo.extra" :form.sync="form"></combo>
            </el-col>
        </el-row>
        <hr>
        <el-row>
            <el-button type="info" @click="$router.go(-1)">返回上一步</el-button>
        </el-row>
    </div>
</template>
<script>
  import combo from './combo.vue'

  export default {
    data () {
      return {
        combo: {
          pes: [],
          basic: null,
          extra: []
        },
        type: [],
        form: {
          id: null,
          title: '',
          price: null,
          type: null,
          extra_no: '',
          protocol_name: '',
          protocol_url: ''
        },
        rules: {
          title: [
            { required: true, message: '请输入体检名称', trigger: 'blur' }
          ],
          price: [
            { type: 'number', required: true, message: '请填写体检价格', trigger: 'blur' }
          ],
          type: [
            { required: true, message: '请选择体检类型', trigger: 'blur' }
          ]
        }
      }
    },
    mounted () {
      this.getPes()
    },
    methods: {
      submitForm (formName) {
        this.$refs[formName].validate((valid) => {
          if (valid) {
            this.$http.post('product/' + this.$route.params.id + '/pe', this.form).then(ret => {
              if (ret.data.error == true) {
                this.$message.error(ret.data.message)
              } else {
                this.resetForm()
                this.$message.success('产品添加体检项成功')
                this.getPes()
              }
            })
          }
        })
      },
      updateForm (formName) {
        this.$refs[formName].validate((valid) => {
          if (valid) {
            this.$http.patch('product/' + this.$route.params.id + '/pe/' + this.form.id, this.form).then(ret => {
              if (ret.data.error == true) {
                this.$message.error(ret.data.message)
              } else {
                this.resetForm()
                this.$message.success('更新成功')
                this.getPes()
              }
            })
          }
        })
      },
      resetForm () {
        this.$refs['form'].resetFields()
        this.form = {
          id: null,
          title: '',
          price: null,
          type: null,
          extra_no: '',
          protocol_name: '',
          protocol_url: ''
        }
      },
      getPes () {
        // 获取产品所有体检项
        this.$http.get('product/' + this.$route.params.id + '/pes').then(ret => {
          // 每次获取，都需要初始化
          this.combo.pes   = []
          this.combo.basic = null
          this.combo.extra = []

          let pes        = ret.data
          this.combo.pes = pes

          for (let i = 0; i < pes.length; i++) {
            if (pes[i].type == 1) {
              this.combo.basic = pes[i]
            } else {
              this.combo.extra.push(pes[i])
            }
          }
        })
      }
    },
    components: { combo }
  }
</script>