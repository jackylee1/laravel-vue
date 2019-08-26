<template>
    <div>
        <el-row>
            <el-button type="primary" icon="plus" @click="needAction = true">添加用户</el-button>
        </el-row>
        <el-row>
            <el-col :span="12">
                <el-table :data="users" border>
                    <el-table-column
                            prop="name"
                            label="用户名">
                    </el-table-column>
                    <el-table-column
                            prop="email"
                            label="邮箱">
                    </el-table-column>
                    <el-table-column
                            prop="roles"
                            label="拥有角色">
                        <template slot-scope="scope">
                            <el-tag v-for="(role,index) in scope.row.roles" :key="index" type="info" close-transition>{{role.display_name}}</el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column label="操作">
                        <template slot-scope="scope">
                            <el-button size="small" icon="edit" @click="handleEdit(scope.row)">编辑</el-button>
                            <el-button size="small" icon="share" type="info" @click="assign(scope.row.id, scope.row.name)">分配角色</el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </el-col>

        </el-row>
        <el-dialog :title="title" :visible.sync="needAction" size="tiny" @close="cancelAddOrUpdate">
            <el-form :model=" form" ref="form" :rules="rules">
                <el-form-item label="用户名" label-width="80px" prop="name">
                    <el-input v-model="form.name" auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item label="邮箱" label-width="80px" prop="email">
                    <el-input v-model="form.email" auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item label="密码" label-width="80px" prop="password">
                    <el-input type="password" v-model="form.password" auto-complete="off"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button icon="circle-cross" @click="cancelAddOrUpdate">取 消</el-button>
                <el-button icon="circle-check" type="primary" @click="addOrUpdate">确 定</el-button>
            </div>
        </el-dialog>

        <el-dialog :title="assignTitle" :visible.sync="needAssign" size="tiny">
            <el-checkbox-group v-model="assignForm.checkedRoles">
                <ul>
                    <li v-for="role in assignForm.roles" :key="role.id">
                        <el-checkbox :label="role.id">{{ role.display_name }}</el-checkbox>
                    </li>
                </ul>
            </el-checkbox-group>
            <div slot="footer" class="dialog-footer">
                <el-button icon="circle-cross" @click="cancelAssign">取 消</el-button>
                <el-button icon="circle-check" type="primary" @click="doAssign">确 定</el-button>
            </div>
        </el-dialog>
    </div>
</template>
<script>
  export default {
    data () {
      return {
        users: [],
        needAction: false,
        needAssign: false,
        id: '',
        action: 'add',
        form: {
          name: '',
          email: '',
          password: ''
        },
        rules: {
          name: [
            { required: true, message: '请输入用户名', trigger: 'blur' }
          ],
          email: [
            { required: true, message: '请输入邮箱', trigger: 'blur' }
          ],
          password: [
            { required: true, message: '请输入密码', trigger: 'blur' }
          ]
        },
        assignTitle: '',
        assignForm: {
          userId: '',
          checkedRoles: [],
          roles: []
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
      this.getRoles()
    },
    methods: {
      all () {
        this.$http.get('user').then(ret => {
          this.users = ret.data
        })
      },
      cancelAddOrUpdate () {
        this.form.name     = ''
        this.form.email    = ''
        this.form.password = ''
        this.needAction    = false
        this.action        = 'add'
      },
      addOrUpdate () {
        this.$refs['form'].validate((valid) => {
          if (valid) {
            if (this.action == 'add') {
              this.$http.post('user', this.form).then(ret => {
                this.$message.success('添加成功！')
                this.all()
                this.needAction = false
              })
            } else {
              this.$http.patch('user/' + this.id, this.form).then(ret => {
                this.$message.success('更新成功！')
                this.needAction = false
                this.action     = 'add'
                this.users.map(val => {
                  if (val.id == this.id) {
                    val.name  = this.form.name
                    val.email = this.form.email
                  }
                })
              })
            }
          }
        })
      },
      handleEdit (row) {
        this.form.name  = row.name
        this.form.email = row.email
        this.id         = row.id
        this.needAction = true
        this.action     = 'edit'
      },
      getRoles () {
        this.$http.get('role').then((ret) => {
          this.assignForm.roles = ret.data
        })
      },
      assign (userId, name) {
        this.$http.get('user/' + userId).then(ret => {
          this.assignForm.checkedRoles = ret.data
          this.assignForm.userId       = userId
          this.assignTitle             = '当前分配用户：' + name
          this.needAssign              = true
        })
      },
      cancelAssign () {
        this.needAssign              = false
        this.assignForm.userId       = ''
        this.assignForm.checkedRoles = []
      },
      doAssign () {
        this.$http.post('user/' + this.assignForm.userId + '/role', {
          roles: this.assignForm.checkedRoles
        }).then(ret => {
          this.$message.success('分配成功！')
          this.all()
          this.needAssign = false
        })
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
