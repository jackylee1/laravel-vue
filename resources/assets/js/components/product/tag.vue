<template>
    <div>
        <el-row>
            <el-button type="primary" icon="plus" @click="needAction = true">添加标签</el-button>
        </el-row>
        <el-row>
            <el-col :span="12">
                <el-table :data="tags" border>
                    <el-table-column
                            prop="title"
                            label="标签名">
                    </el-table-column>
                    <el-table-column
                            prop="description"
                            label="描述">
                    </el-table-column>
                    <el-table-column label="操作">
                        <template slot-scope="scope">
                            <el-button size="small" icon="edit" @click="handleEdit(scope.row)">编辑</el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </el-col>
        </el-row>

        <el-dialog :title="title" :visible.sync="needAction" size="tiny" @close="cancelAddOrUpdate">
            <el-form :model=" form" ref="form" :rules="rules">
                <el-form-item label="标签名" label-width="80px" prop="title">
                    <el-input v-model="form.title" auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item label="描述" label-width="80px">
                    <el-input v-model="form.description" auto-complete="off"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button icon="circle-cross" @click="cancelAddOrUpdate">取 消</el-button>
                <el-button icon="circle-check" type="primary" @click="addOrUpdate">确 定</el-button>
            </div>
        </el-dialog>
    </div>
</template>
<script>
  export default {
    data () {
      return {
        tags: [],
        needAction: false,
        id: '',
        action: 'add',
        form: {
          title: '',
          description: ''
        },
        rules: {
          title: [
            { required: true, message: '请输入标签名', trigger: 'blur' }
          ]
        }
      }
    },
    computed: {
      title () {
        return this.action == 'add' ? '添加' : '编辑'
      }
    },
    mounted () {
      this.all()
    },
    methods: {
      all () {
        this.$http.get('tag').then(ret => {
          this.tags = ret.data
        })
      },
      cancelAddOrUpdate () {
        this.form.title       = ''
        this.form.description = ''
        this.needAction       = false
        this.action           = 'add'
      },
      addOrUpdate () {
        this.$refs['form'].validate((valid) => {
          if (valid) {
            if (this.action == 'add') {
              this.$http.post('tag', this.form).then(ret => {
                this.$message.success('添加成功！')
                this.all()
                this.needAction = false
              })
            } else {
              this.$http.patch('tag/' + this.id, this.form).then(ret => {
                this.$message.success('更新成功！')
                this.needAction = false
                this.action     = 'add'
                this.all()
              })
            }
          }
        })
      },
      handleEdit (row) {
        this.form.title       = row.title
        this.form.description = row.description
        this.id               = row.id
        this.needAction       = true
        this.action           = 'edit'
      }
    }
  }
</script>
<style>
    .el-transfer-panel {
        height: 350px;
    }

    .el-tag {
        margin: 0 2px;
    }

    .el-table__header, .el-table__body {
        width: 100% !important;
        table-layout: auto !important;
    }

    .el-row {
        margin-bottom: 20px;
    }
</style>
