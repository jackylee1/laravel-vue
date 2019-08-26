<template>
    <el-row>
        <el-col :span="8">
            <p><strong>② 产品包含体检包：</strong></p>
            <el-form :model="combo" ref="combo" label-width="100px" :rules="rules">
                <el-form-item label="必须基础包" prop="basic_id">
                    <el-radio-group v-model="combo.basic_id">
                        <el-popover
                                placement="right"
                                title="详细"
                                width="200"
                                trigger="click">
                            <div>
                                <p> ID：{{basic.id}} </p>
                                <p> 标题：{{basic.title}} </p>
                                <p> 价格：¥{{ (basic.price / 100).toFixed(2)}} </p>
                                <p> 外部编号：{{basic.extra_no}} </p>
                                <p>
                                    <el-button type="warning" size="mini" @click="editItem(basic)">修改</el-button>
                                </p>
                            </div>
                            <el-radio slot="reference" :label="combo.basic_id" v-if="basic">
                                {{ basic.title }}
                                <el-button icon="el-icon-view" circle size="mini"></el-button>
                            </el-radio>
                        </el-popover>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="可选叠加包">
                    <el-checkbox-group v-model="combo.extra_ids">
                        <p v-for="item in extra" :key="item.id">
                            <el-popover
                                    placement="right"
                                    title="详细"
                                    width="200"
                                    trigger="click">
                                <div>
                        <p> ID：{{item.id}} </p>
                        <p> 标题：{{item.title}} </p>
                        <p> 价格：¥{{ (item.price / 100).toFixed(2)}} </p>
                        <p> 外部编号：{{item.extra_no}} </p>
                        <p>
                            <el-button type="warning" size="mini" @click="editItem(item)">修改</el-button>
                        </p>
                        </div>
                        <el-checkbox :label="item.id" slot="reference">
                            {{ item.title }}
                            <el-button icon="el-icon-view" circle size="mini"></el-button>
                        </el-checkbox>
                        </el-popover>
                        </p>
                    </el-checkbox-group>
                </el-form-item>
                <el-form-item label="组合码" prop="code">
                    <el-input v-model="combo.code"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary"  @click="addCombo">添加组合</el-button>
                </el-form-item>
            </el-form>
        </el-col>
        <el-col :span="8" :offset="1">
            <p><strong>③ 套餐组合编号设置：</strong></p>
            <el-table
                    :data="exsitedCombo"
                    style="width: 100%">
                <el-table-column
                        prop="physical_examination_id"
                        label="叠加包"
                        width="180">
                    <template slot-scope="scope">
                        <el-tag size="mini" v-for="v in pes" :key="v.id" v-if="scope.row.physical_examination_id.indexOf(v.id)>-1">{{ v.title }}</el-tag>
                    </template>
                </el-table-column>
                <el-table-column
                        prop="code"
                        label="组合码"
                        width="100">
                </el-table-column>
                <el-table-column
                        prop="id"
                        label="操作"
                        width="180">
                    <template slot-scope="scope">
                        <el-button type="warning" @click="deleteCombo(scope.row.id)">删除</el-button>
                    </template>
                </el-table-column>
            </el-table>
        </el-col>
    </el-row>
</template>

<script>
  export default {
    data () {
      return {
        combo: {
          basic_id: null,
          extra_ids: [],
          code: ''
        },
        exsitedCombo: [],
        rules: {
          basic_id: [
            { required: true, message: '基础包必选', trigger: 'blur' }
          ],
          code: [
            { required: true, message: '请填写组合码', trigger: 'blur' }
          ]
        }
      }
    },
    props: ['pes', 'basic', 'extra', 'form'],
    mounted () {
      this.combo.basic_id = this.basic ? this.basic.id : null
      this.getExsitedCombo()
    },
    methods: {
      addCombo () {
        this.combo.basic_id = this.basic.id
        this.$refs.combo.validate((valid) => {
          if (valid) {
            this.$http.post('product/' + this.$route.params.id + '/pe_combo', this.combo).then(ret => {
              if (ret.data.error == true) {
                this.$message.error('已存在此组合！')
              } else {
                this.getExsitedCombo()
                this.combo.extra_ids = []
                this.combo.code      = ''
              }
            }).catch(e => {
              this.$message.error('已存在此组合！')
            })
          }
        })
      },
      getExsitedCombo () {
        this.$http.get('product/' + this.$route.params.id + '/pe_combo').then(ret => {
          this.exsitedCombo = ret.data
        })
      },
      deleteCombo (id) {
        this.$confirm('确认删除此组合？', '警告', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          this.$http.delete('product/' + this.$route.params.id + '/pe_combo/' + id).then(ret => {
            this.getExsitedCombo()
          })
        })
      },
      editItem (item) {
        let tmp   = JSON.parse(JSON.stringify(item)) // js的对象简单深拷贝
        tmp.price = parseFloat((tmp.price / 100).toFixed(2))
        this.$emit('update:form', tmp)
      }
    }
  }
</script>