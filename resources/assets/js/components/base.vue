<template>
    <el-container style="height:100%; border: 1px solid #eee">
        <el-aside width="200px" style="background-color:#545c64">
            <keep-alive>
                <side-menu></side-menu>
            </keep-alive>
        </el-aside>
        <el-container>
            <el-header style="text-align: right; font-size: 12px">
                <span style="margin-right: 10px">{{ admin.name }}</span>
                <el-button type="text" @click="logout">退出登录</el-button>
            </el-header>
            <el-main>
                <div style="padding:10px">
                    <transition
                            name="custom-classes-transition"
                            enter-active-class="animated fadeIn">
                        <router-view></router-view>
                    </transition>
                </div>
            </el-main>
        </el-container>
    </el-container>
</template>

<script>
  import sideMenu from './common/side_menu.vue'

  export default {
    methods: {
      logout () {
        this.$http.delete('login').then(ret => {
          localStorage.removeItem('token')
          localStorage.removeItem('hash')
          this.$router.push('/login')
        })
      }
    },
    computed: {
      admin () {
        return JSON.parse(localStorage.getItem('admin'))
      }
    },
    components: { sideMenu }
  }
</script>

<style>
    .el-header {
        background-color: #B3C0D1;
        color: #333;
        line-height: 60px;
    }

    .el-aside {
        color: #333;
    }
</style>
