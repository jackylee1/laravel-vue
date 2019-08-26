<template>
    <el-row>
        <el-col :span="5">
            <h5>所有类型</h5>
            <br>
            <el-tree
                    :data="data"
                    :props="defaultProps"
                    node-key="id"
                    accordion
                    :default-expanded-keys="[0]"
                    @current-change="show"
                    :expand-on-click-node="true">
            </el-tree>
        </el-col>
        <el-col :span="6">
            <h5>添加&更新</h5>
            <br>
            <el-form ref="form" :model="form" :rules="rules" label-width="80px">
                <el-form-item label="权限标识" prop="name">
                    <el-input :disabled="true" v-model="form.name"></el-input>
                </el-form-item>
                <el-form-item label="权限名称" prop="display_name">
                    <el-input v-model="form.display_name"></el-input>
                </el-form-item>
                <el-form-item label="上级权限" prop="parent_id">
                    <el-cascader
                            :options="options"
                            change-on-select
                            v-model="selectedOptions"
                            @change="change"
                    ></el-cascader>
                </el-form-item>
                <el-form-item label="描述">
                    <el-input type="text" v-model="form.description"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-button type="info" @click="update">更新</el-button>
                    <!--<el-button @click="clear">重置表单</el-button>-->
                    <!--权限的添加，是根据seeder文件自动生成，这里只需要修改名称即可-->
                    <!--<el-button v-show="form.id != null" type="warning" @click="remove">删除</el-button>-->
                </el-form-item>
            </el-form>
        </el-col>
    </el-row>
</template>
<script>
  import deepcopy from 'deepcopy'

  export default {
    data () {
      return {
        data: [],
        options: [],
        defaultProps: {
          children: 'children',
          label: 'label'
        },
        selectedOptions: [0],
        form: {
          id: null,
          display_name: '',
          parent_id: [],
          description: ''
        },
        rules: {
          name: [{ required: true, message: '请输入权限标识', trigger: 'blur' }],
          display_name: [{ required: true, message: '请输入权限名称', trigger: 'blur' }],
          parent_id: [{ required: true, message: '请选择上级权限', trigger: 'blur', type: 'array' }]
        }
      }
    },
    created () {
      this.fetchData()
    },
    methods: {
      fetchData () {
        this.$http.get('permission').then((ret) => {
          console.log(ret)
          this.data = ret.data
          let tmp   = deepcopy(this.data) // Js的数组拷贝真是麻烦，引入第三方库
          // 只保留两个层级
          this.options = tmp.map(function(val) {
            if (val.children != undefined) {
              val.children.map(function(v) {
                delete v.children
                return v
              })
            }
            return val
          })
          this.clear()
        })
      },
      change (val) {
        this.form.parent_id = val
      },
      update () {
        this.$refs['form'].validate((valid) => {
          if (!valid) {
            this.$notify({
              title: '错误',
              message: '信息填写不完整',
              type: 'error'
            })
          } else {
            this.$http.patch('permission/' + this.form.id, {
              name: this.form.name,
              display_name: this.form.display_name,
              parent_id: this.form.parent_id.pop(),
              description: this.form.description
            }).then(ret => {
              this.$notify({
                title: '成功',
                message: '更新成功',
                type: 'success'
              })
              this.fetchData()
            })
          }
        })
      },
      show (val) {
        this.form.id         = null
        this.selectedOptions = [0]
        this.$http.get('permission/' + val.id).then(ret => {
          let item               = ret.data
          this.form.id           = item.id
          this.form.name         = item.name
          this.form.display_name = item.display_name
          this.form.description  = item.description
          this.form.parent_id.push(item.parent_id)
          this.selectedOptions.push(item.parent_id)// 这里只支持2级层级，多级层级的话，需要返回父id的父id..
        })
      },
      clear () {
        this.form.id           = null
        this.form.name         = ''
        this.form.display_name = ''
        this.form.parent_id    = []
        this.form.description  = ''
        this.selectedOptions   = [0]
      },
      remove () {
        if (this.form.id == null) {
          return false
        }
        this.$http.delete('permission/' + this.form.id).then(ret => {
          this.$notify({
            title: '成功',
            message: '删除成功',
            type: 'success'
          })
          this.fetchData()
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
