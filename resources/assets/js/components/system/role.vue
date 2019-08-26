<template>
    <div>
        <el-button type="primary" icon="plus" @click="needAction = true">添加角色</el-button>
        <div style="margin-bottom:15px"></div>
        <el-table :data="roles" border style="width: 80%">
            <el-table-column prop="name" label="权限标识">
            </el-table-column>
            <el-table-column prop="display_name" label="角色名称">
            </el-table-column>
            <el-table-column label="操作">
                <template slot-scope="scope">
                    <el-button size="small" icon="edit" @click="handleEdit(scope.row)">编辑</el-button>
                    <el-button size="small" icon="share" type="info" @click="assign(scope.row.id, scope.row.display_name)">分配权限</el-button>
                </template>
            </el-table-column>
        </el-table>
        <el-dialog :title="title" :visible.sync="needAction" size="tiny">
            <el-form :model=" form" ref="form" :rules="rules">
                <el-form-item label="角色代码" label-width="80px" prop="name">
                    <el-input v-model="form.name" auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item label="角色名称" label-width="80px" prop="display_name">
                    <el-input v-model="form.display_name" auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item label="角色描述" label-width="80px">
                    <el-input type="textarea" v-model="form.description"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button icon="circle-cross" @click="cancelAddOrUpdate">取 消</el-button>
                <el-button icon="circle-check" type="primary" @click="addOrUpdate">确 定</el-button>
            </div>
        </el-dialog>
        <el-dialog :title="assignTitle" :visible.sync="needAssign" @close="cancelAssign">
            <el-row v-for="(perm, index) in assignForm.perms" :key="perm.id">
                <el-checkbox-group v-model="assignForm.checkedPerms">
                    <el-checkbox style="font-weight: bold" :label="perm.id" @change="handleCheckAllChange(index,perm.id)">{{ perm.label }}：</el-checkbox>
                    <br>
                    <el-checkbox :class="{lf: i == 0}" v-for="(item,i) in perm.children" :label="item.id" :key="item.id" @change="handleCheckedChange(index)">{{item.label}}</el-checkbox>
                </el-checkbox-group>
                <hr>
            </el-row>
            <el-row>
                <el-button icon="circle-cross" @click="cancelAssign">取 消</el-button>
                <el-button icon="circle-check" type="primary" @click="doAssign">确 定</el-button>
            </el-row>
        </el-dialog>
    </div>
</template>
<script>
  export default {
    data () {
      return {
        roles: [],
        needAction: false,
        needAssign: false,
        id: '',
        action: 'add',
        form: {
          name: '',
          display_name: '',
          description: ''
        },
        rules: {
          name: [
            { required: true, message: '请输入角色代码', trigger: 'blur' }
          ],
          display_name: [
            { required: true, message: '请输入角色名称', trigger: 'blur' }
          ]
        },
        assignTitle: '',
        assignForm: {
          roleId: '',
          checkedPerms: [],
          perms: []
        }
      }
    },
    computed: {
      title () {
        return this.action == 'add' ? '添加' : '编辑'
      }
    },
    created () {
      this.all()
      this.getPerms()
    },
    methods: {
      all () {
        this.$http.get('role').then(ret => {
          this.roles = ret.data
        })
      },
      cancelAddOrUpdate () {
        this.$refs['form'].resetFields()
        this.form.description = '' // 非必填，reset不了
        this.needAction       = false
        this.action           = 'add'
      },
      addOrUpdate () {
        this.$refs['form'].validate((valid) => {
          if (valid) {
            if (this.action == 'add') {
              this.$http.post('role', this.form).then(ret => {
                this.$message.success('添加成功')
                this.all()
                this.needAction = false
              })
            } else {
              this.$http.patch('role/' + this.id, this.form).then(ret => {
                this.$message.success('更新成功！')
                this.needAction = false
                this.action     = 'add'
                this.roles.map(val => {
                  if (val.id == this.id) {
                    val.name         = this.form.name
                    val.display_name = this.form.display_name
                    val.description  = this.form.description
                  }
                })
              })
            }
          }
        })
      },
      handleEdit (row) {
        this.form.name         = row.name
        this.form.display_name = row.display_name
        this.form.description  = row.description
        this.id                = row.id
        this.needAction        = true
        this.action            = 'edit'
      },
      getPerms () {
        this.$http.get('permission').then((ret) => {
          this.assignForm.perms = ret.data[0].children
        })
      },
      assign (roleId, name) {
        this.$http.get('role/' + roleId).then(ret => {
          this.assignForm.checkedPerms = ret.data
          this.assignForm.roleId       = roleId
          this.assignTitle             = '当前分配角色：' + name
          this.needAssign              = true
          this.assignForm.perms.map(val => {
            if (this.assignForm.checkedPerms.indexOf(val.id) >= 0) {
              val.checkAll = true
            }
            return val
          })
        })
      },
      cancelAssign () {
        this.needAssign              = false
        this.assignForm.roleId       = ''
        this.assignForm.checkedPerms = []
        this.getPerms()
      },
      doAssign () {
        this.$http.post('role/' + this.assignForm.roleId + '/permission', {
          perms: this.assignForm.checkedPerms
        }).then(ret => {
          this.$message.success('分配成功！')
          this.all()
          this.needAssign = false
        })
      },
      handleCheckAllChange (index, id) {
        let event    = window.event
        let subPerms = this.assignForm.perms[index].children
        if (event.target.checked) {
          // 全选
          for (let i = 0; i < subPerms.length; i++) {
            // 添加所有子节点
            this.assignForm.checkedPerms.push(subPerms[i].id)
          }
        } else {
          // 全不选
          let tmp = []
          for (let i = 0; i < subPerms.length; i++) {
            tmp.push(subPerms[i].id)
          }
          this.assignForm.checkedPerms = this.assignForm.checkedPerms.filter(val => {
            return tmp.indexOf(val) < 0
          })
        }
      },
      handleCheckedChange (index) {
        let children  = this.assignForm.perms[index].children
        let parent_id = this.assignForm.perms[index].id
        for (let i = 0; i < children.length; i++) {
          let key = this.assignForm.checkedPerms.indexOf(children[i].id)
          let tmp = this.assignForm.checkedPerms.indexOf(parent_id)
          if (key < 0 && tmp > -1) {
            // 不是全选
            this.assignForm.checkedPerms.splice(tmp, 1)
            return
          } else if (tmp < 0) {
            // 全选
            this.assignForm.checkedPerms.push(parent_id)
          }
        }
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

    .lf {
        margin-left: 50px
    }

    hr {
        border-top: 1px solid rgba(0, 0, 0, 0.05);
        margin-top: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .el-table__header, .el-table__body {
        width: 100% !important;
        table-layout: auto !important;
    }
</style>
