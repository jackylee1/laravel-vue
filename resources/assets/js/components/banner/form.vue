<template>
    <el-row>
        <h3 v-if="bannerForm.id ==null">添加广告</h3>
        <h3 v-else>编辑广告</h3>
        <el-col :span="8">
            <el-form :model="bannerForm" :rules="rules" ref="bannerForm" label-width="100px" style="margin-top:20px">
                <el-form-item label="标题" prop="title">
                    <el-input v-model="bannerForm.title"></el-input>
                </el-form-item>
                <el-form-item label="图片" prop="cover">
                    <upload :prefix="'banner'" :files="upload.files" :src.sync="bannerForm.cover"></upload>
                </el-form-item>
                <el-form-item label="跳转链接">
                    <el-input type='url' v-model.number="bannerForm.link"></el-input>
                </el-form-item>
                <el-form-item label="">
                    <el-switch inactive-text="禁用" active-text="启用" v-model="tmpStatus"></el-switch>
                </el-form-item>
                <el-form-item label="广告时间">
                    <el-date-picker
                            v-model="tmpTime"
                            type="datetimerange"
                            value-format="yyyy-MM-dd HH:mm:ss"
                            range-separator="至"
                            start-placeholder="开始日期"
                            end-placeholder="结束日期">
                    </el-date-picker>
                </el-form-item>
                <el-form-item label="介绍" prop="introduction">
                    <el-input type="textarea" v-model="bannerForm.introduction"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-button v-if="bannerForm.id ==null" type="primary" @click="add('bannerForm')">添加</el-button>
                    <el-button v-else type="warning" @click="update('bannerForm')">更新</el-button>
                    <el-button @click="resetForm('bannerForm')">重置</el-button>
                </el-form-item>
            </el-form>
        </el-col>
    </el-row>
</template>
<script>
  import upload from '../common/upload.vue'

  export default {
    data () {
      return {
        upload: {
          files: []
        },
        tmpTime: [],
        tmpStatus: true,
        bannerForm: {
          title: '',
          introduction: '',
          cover: '',
          link: '',
          status: 1,
          begin_at: null,
          end_at: null
        },
        rules: {
          title: [
            { required: true, message: '请输入广告标题', trigger: 'blur' }
          ],
          cover: [
            { type: 'url', required: true, message: '请填封面图片地址', trigger: 'blur' }
          ]
        }
      }
    },
    mounted () {
      if (this.$route.params.id) {
        this.getDetail(this.$route.params.id)
      }
    },
    watch: {
      tmpTime (n, o) {
        this.bannerForm.begin_at = n[0]
        this.bannerForm.end_at   = n[1]
      },
      tmpStatus (n, o) {
        if (n == true) {
          this.bannerForm.status = 1
        } else {
          this.bannerForm.status = 2
        }
      }
    },
    methods: {
      getDetail (id) {
        this.$http.get('banner/' + id).then(ret => {
          let data        = ret.data
          this.bannerForm = data
          this.tmpStatus  = data.status == 1 ? true : false

          if (data.begin_at) {
            this.tmpTime.push(data.begin_at)
          }
          if (data.end_at) {
            this.tmpTime.push(data.end_at)
          }
          this.upload.files = []
          this.upload.files.push({ 'url': data.cover })
        })
      },
      add (formName) {
        this.$refs[formName].validate((valid) => {
          if (valid) {
            this.$http.post('banner', this.bannerForm).then(ret => {
              this.$router.push({ name: 'banner' })
            })
          }
        })
      },
      update (formName) {
        this.$refs[formName].validate((valid) => {
          if (valid) {
            this.$http.patch('banner/' + this.bannerForm.id, this.bannerForm).then(ret => {
              this.$message.success('更新成功')
              this.$router.push({ name: 'banner' })
            })
          }
        })
      },
      resetForm (formName) {
        this.$refs[formName].resetFields()
        this.tmpTime = []
      }
    },
    components: { upload }
  }
</script>