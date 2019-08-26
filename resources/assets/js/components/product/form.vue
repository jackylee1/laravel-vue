<template>
    <el-row :gutter="40">
        <el-form :model="productForm" :rules="rules" ref="productForm" label-width="100px" style="margin-top:20px">
            <el-col :span="13">
                <el-form-item label="名称" prop="name">
                    <el-input v-model="productForm.name"></el-input>
                </el-form-item>
                <el-form-item label="类别" prop="category">
                    <el-radio-group v-model="productForm.category">
                        <el-radio v-for="(category,key) in categories" :key="key" :label="key">{{ category }}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="状态" prop="status">
                    <el-switch inactive-text="下架" active-text="上架" v-model="productForm.status"></el-switch>
                </el-form-item>
                <el-form-item label="标签">
                    <el-select v-model="productForm.tag_id" multiple placeholder="请选择" style="width:100%">
                        <el-option
                                v-for="item in tags"
                                :key="item.id"
                                :label="item.title"
                                :value="item.id">
                        </el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="价格" prop="price">
                    <el-input type='number' step="0.01" v-model.number="productForm.price">
                        <template slot="prepend">¥</template>
                    </el-input>
                </el-form-item>
                <el-form-item label="库存" prop="inventory">
                    <el-input type='number' v-model.number="productForm.inventory"></el-input>
                </el-form-item>
                <el-form-item label="销量">
                    <el-input type='number' v-model.number="productForm.sale_count"></el-input>
                </el-form-item>

                <el-form-item label="产品佣金率" prop="commission_rate">
                    <el-input type='number' :min="0" :max="100" step="0.01" v-model.number="productForm.commission_rate">
                        <template slot="append">%</template>
                    </el-input>
                </el-form-item>

                <el-form-item label="代理人佣金" prop="agent_rate">
                    <el-row type="flex" style="padding-bottom: 10px;">
                        <el-col :span="8">
                            <el-input type='number' :precision="2" :min="0" :max="100" step="0.01" v-model.number="productForm.agent_rate[0][0]">
                                <template slot="prepend">一级</template>
                                <template slot="append">%</template>
                            </el-input>
                        </el-col>
                    </el-row>

                    <el-row type="flex" style="padding-bottom: 10px;">
                        <el-col :span="8">
                            <el-input type='number' :precision="2" :min="0" :max="100" step="0.01" v-model.number="productForm.agent_rate[1][0]">
                                <template slot="prepend">一级</template>
                                <template slot="append">%</template>
                            </el-input>
                        </el-col>
                        <el-col :span="8">
                            <el-input type='number' :precision="2" :min="0" :max="100" step="0.01" v-model.number="productForm.agent_rate[1][1]">
                                <template slot="prepend">二级</template>
                                <template slot="append">%</template>
                            </el-input>
                        </el-col>
                    </el-row>

                    <el-row type="flex" style="padding-bottom: 10px;">
                        <el-col :span="8">
                            <el-input type='number' :precision="2" :min="0" :max="100" step="0.01" v-model.number="productForm.agent_rate[2][0]">
                                <template slot="prepend">一级</template>
                                <template slot="append">%</template>
                            </el-input>
                        </el-col>
                        <el-col :span="8">
                            <el-input type='number' :precision="2" :min="0" :max="100" step="0.01" v-model.number="productForm.agent_rate[2][1]">
                                <template slot="prepend">二级</template>
                                <template slot="append">%</template>
                            </el-input>
                        </el-col>
                        <el-col :span="8">
                            <el-input type='number' :precision="2" :min="0" :max="100" step="0.01" v-model.number="productForm.agent_rate[2][2]">
                                <template slot="prepend">三级</template>
                                <template slot="append">%</template>
                            </el-input>
                        </el-col>
                    </el-row>
                </el-form-item>
            </el-col>
            <el-col :span="10">
                <el-form-item label="封面" prop="cover">
                    <upload :prefix="'product_cover'" :files="upload.files" :src.sync="productForm.cover"></upload>
                </el-form-item>
                <el-form-item label="描述" prop="description">
                    <el-input type="textarea" v-model="productForm.description"></el-input>
                </el-form-item>
                <el-form-item label="微信分享标题" prop="share_title">
                    <el-input v-model="productForm.share_title"></el-input>
                </el-form-item>
                <el-form-item label="微信分享描述" prop="share_desc">
                    <el-input type="textarea" v-model="productForm.share_desc"></el-input>
                </el-form-item>
                <el-form-item label="微信分享图片" prop="share_img">
                    <upload :prefix="'product_share_cover'" :files="upload.wechat_share_files" :src.sync="productForm.share_img"></upload>
                </el-form-item>

                <el-form-item>
                    <el-button v-if="productForm.id ==null" type="primary" @click="add('productForm')">立即创建</el-button>
                    <template v-else>
                        <el-button type="warning" @click="update('productForm')">更新</el-button>
                        <el-button type="info" @click="next">跳过</el-button>
                    </template>
                    <el-button @click="resetForm('productForm')">重置</el-button>
                </el-form-item>
            </el-col>
        </el-form>
    </el-row>
</template>
<script>
  import upload from '../common/upload.vue'

  export default {
    data () {
      return {
        upload: {
          files: [],
          wechat_share_files: []
        },
        categories: [],
        tags: [],
        agent_rate_default: [
          [100],
          [10, 90],
          [5, 15, 80]
        ],
        productForm: {
          tag_id: [],
          id: null,
          name: '',
          price: null,
          commission_rate: null,
          agent_rate: [
            [100],
            [10, 90],
            [5, 15, 80]
          ],
          status: true,
          cover: '',
          description: '',
          inventory: null,
          sale_count: null,
          category: null,
          share_title: '',
          share_desc: '',
          share_img: ''
        },
        rules: {
          name: [
            { required: true, message: '请输入产品名称', trigger: 'blur' }
          ],
          price: [
            { type: 'number', required: true, message: '请填写产品价格', trigger: 'blur' }
          ],
          agent_rate: [
            { validator: this.checkAgentRatesss, required: true, trigger: 'blur' }
          ],
          commission_rate: [
            { required: true, message: '请填写佣金率', trigger: 'blur' }
          ],
          inventory: [
            { type: 'number', required: true, message: '请填写库存', trigger: 'blur' }
          ],
          category: [
            { required: true, message: '请选择产品类型', trigger: 'blur' }
          ],
          cover: [
            { type: 'url', required: true, message: '请添加产品封面图片', trigger: 'blur' }
          ],
          share_title: [
            { required: true, message: '请添加产品分享标题', trigger: 'blur' }
          ],
          share_desc: [
            { required: true, message: '请添加产品分享描述', trigger: 'blur' }
          ],
          share_img: [
            { type: 'url', required: true, message: '请添加产品分享图片', trigger: 'blur' }
          ]
        }
      }
    },
    mounted () {
      this.getCategory()
      this.getTags()
      if (this.$route.params.id) {
        this.getDetail(this.$route.params.id)
      }
    },
    methods: {
      // 代理人佣金自定义验证函数
      checkAgentRatesss (rule, value, callback) {
        let isStart = true
        if (Array.isArray(value)) {
          for (let item in value) {
            if (Array.isArray(value[item])) {
              for (let key in value[item]) {
                let val = value[item][key]
                if (val == '' || val < 0 || val > 100) {
                  isStart = false
                }
              }
            }
          }
        }
        if (!isStart) {
          return callback(new Error('请设置代理人佣金，佣金范围 0 - 100 之间'))
        } else {
          callback()
        }
      },
      getTags () {
        this.$http.get('tag').then(ret => {
          this.tags = ret.data
        })
      },
      getCategory () {
        this.$http.get('product/category').then(ret => {
          this.categories = ret.data
        })
      },
      getDetail (id) {
        this.$http.get('product/' + id).then(ret => {
          let data    = ret.data
          data.tag_id = []

          for (let i = 0; i < data.tags.length; i++) {
            data.tag_id.push(ret.data.tags[i].id)
          }

          this.productForm                 = data
          this.productForm.price /= 100
          this.productForm.commission_rate = (this.productForm.commission_rate * 100).toFixed(2)
          this.productForm.agent_rate      = this.productForm.agent_rate == null ? this.agent_rate_default : this.productForm.agent_rate
          this.productForm.category        = this.productForm.category.toString()
          this.productForm.status          = this.productForm.status == 1 ? true : false
          this.upload.files                = []
          this.upload.files.push({ 'url': this.productForm.cover })
          this.upload.wechat_share_files = []
          this.upload.wechat_share_files.push({ 'url': this.productForm.share_img })
        })
      },
      add (formName) {
        this.$refs[formName].validate((valid) => {
          if (valid) {
            this.$http.post('product', this.productForm).then(ret => {
              // 这里其实需要根据选择的类型去配置不同的产品项
              // 第一期只有体检，就省去判断了
              this.$router.push(ret.data.id + '/pe')
              this.$emit('next')
            })
          }
        })
      },
      update (formName) {
        this.$refs[formName].validate((valid) => {
          if (valid) {
            this.$http.patch('product/' + this.productForm.id, this.productForm).then(ret => {
              // 这里其实需要根据选择的类型去配置不同的产品项
              // 第一期只有体检，就省去判断了
              this.$router.push('pe')
              this.$emit('next')
            })
          }
        })
      },
      resetForm (formName) {
        this.$refs[formName].resetFields()
      },
      next () {
        this.$router.push('pe')
        this.$emit('next')
      }
    },
    components: { upload }
  }
</script>