<template>
    <el-row>
        <el-col :span="8">
            <el-form :model="form" class="demo-form-inline" label-width="100px" ref="form" :rules="rules">
                <el-form-item label="海报图片" prop="src">
                    <upload :prefix="'product_poster'" :files="upload.files" :src.sync="form.src"></upload>
                </el-form-item>
                <el-form-item label="X轴起始位置" prop="x-axis">
                    <el-input type="number" step="0.01" v-model.number="form['x-axis']" placeholder=""></el-input>
                </el-form-item>
                <el-form-item label="Y轴起始位置" prop="y-axis">
                    <el-input type="number" step="0.01" v-model.number="form['y-axis']" placeholder=""></el-input>
                </el-form-item>
                <el-form-item label="二维码宽度" prop="width">
                    <el-input type='number' step="0.01" v-model.number="form.width">
                        <template slot="append">px</template>
                    </el-input>
                </el-form-item>
                <el-form-item>
                    <el-button v-if="add" type="primary" @click="onSubmit">添加</el-button>
                    <el-button v-else type="warning" @click="onEdit">更新</el-button>
                </el-form-item>
            </el-form>
        </el-col>
        <el-col :span="4" :offset="1" :key="poster.id" v-for="poster in posters">
            <el-card :body-style="{ padding: '0px' }">
                <img height="300" :src="poster.src">
                <div style="padding: 14px;">
                    <div class="bottom clearfix">
                        <el-button type="danger" @click="destroy(poster.id)">删除</el-button>
                        <el-button @click="edit(poster)">编辑</el-button>
                    </div>
                </div>
                <br>
            </el-card>
        </el-col>
    </el-row>
</template>
<script>
  import upload from '../common/upload.vue'

  export default {
    data () {
      let src = (rule, value, callback) => {
        if (!value) {
          return callback(new Error('请上传图片'))
        } else {
          callback()
        }
      }
      return {
        upload: {
          files: []
        },
        posters: [],
        add: true,
        form: {
          product_id: null,
          src: '',
          'x-axis': null,
          'y-axis': null,
          width: null
        },
        rules: {
          src: [
            { required: true, validator: src, trigger: 'blur' }
          ],
          'x-axis': [
            { type: 'number', required: true, message: '请填写x轴坐标', trigger: 'blur' }
          ],
          'y-axis': [
            { type: 'number', required: true, message: '请填写y轴坐标', trigger: 'blur' }
          ],
          'width': [
            { type: 'number', required: true, message: '请填写二维码宽度', trigger: 'blur' }
          ]
        }
      }
    },
    created () {
      this.form.product_id = this.$route.params.id
      this.getPosters()
    },
    methods: {
      getPosters () {
        this.$http.get('product/' + this.$route.params.id + '/poster').then(ret => {
          this.posters = ret.data
        })
      },
      edit (poster) {
        this.form         = poster
        this.add          = false
        this.upload.files = []
        this.upload.files.push({ 'url': poster.src })
      },
      onSubmit () {
        this.$refs['form'].validate((valid) => {
          if (valid) {
            this.$http.post('product/poster', this.form).then(ret => {
              this.$message.success('添加成功')
              this.$refs['form'].resetFields()
              this.upload.files = []
              this.getPosters()
            })
          }
        })
      },
      onEdit () {
        this.$refs['form'].validate((valid) => {
          if (valid) {
            this.$http.patch('product/poster/' + this.form.id, this.form).then(ret => {
              this.$message.success('更新成功')
              this.$refs['form'].resetFields()
              this.getPosters()
            })
          }
        })
      },
      destroy (id) {
        this.$confirm('此操作将永久删除, 是否继续?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          this.$http.delete('product/poster/' + id).then(ret => {
            this.$message.success('删除成功')
            this.getPosters()
          })
        }).catch(() => {
          this.$message({
            type: 'info',
            message: '已取消删除'
          })
        })
      }
    },
    components: { upload }
  }
</script>
<style>
    .time {
        font-size: 13px;
        color: #999;
    }

    .bottom {
        margin-top: 13px;
        line-height: 12px;
    }

    .button {
        padding: 0;
        float: right;
    }

    .clearfix:before,
    .clearfix:after {
        display: table;
        content: "";
    }

    .clearfix:after {
        clear: both
    }
</style>
