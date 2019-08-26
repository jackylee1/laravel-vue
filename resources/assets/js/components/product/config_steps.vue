<template>
    <div class="product">
        <el-row>
            <el-button type="info" icon="el-icon-back" @click="$router.push('/product')">返回列表</el-button>
        </el-row>
        <!--<hr>-->
        <!--<el-row>-->
            <!--<el-col :md="10">-->
                <!--<el-steps :active="atStep" spance='2%' finish-status="success" process-status="process" :center="true" :align-center="true" style="margin-left:2%">-->
                    <!--<el-step title="基本信息" description='编辑产品的基本描述信息'></el-step>-->
                    <!--<el-step title="体检" description="配置产品包含的　体检项"></el-step>-->
                <!--</el-steps>-->
            <!--</el-col>-->
        <!--</el-row>-->
        <hr>
        <el-row>
            <transition
                    name="custom-classes-transition"
                    enter-active-class="animated slideInRight">
                <router-view @next="next"></router-view>
            </transition>
        </el-row>
    </div>
</template>

<script>

  export default {
    data () {
      return {
        atStep: 0
      }
    },
    methods: {
      next () {
        this.atStep++
      }
    }
  }
</script>

<style>
    .text {
        font-size: 14px;
    }

    .item {
        padding: 18px 0;
    }

    .clearfix:before,
    .clearfix:after {
        display: table;
        content: "";
    }

    .clearfix:after {
        clear: both
    }

    .box-card {
        width: 40%
    }
</style>
