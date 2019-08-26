<template>
    <div>
        <el-row>
            <router-link :to="{ name:'banner_create' }">
                <el-button type="primary" icon="el-icon-circle-plus">添加广告</el-button>
            </router-link>
        </el-row>
        <el-row v-if="banners">
            <el-col>
                <el-table :data="banners['banners']['data']">
                    <el-table-column
                            prop="id"
                            label="编号"
                            width="80">
                    </el-table-column>
                    <el-table-column
                            prop="title"
                            label="广告名称">
                    </el-table-column>
                    <el-table-column
                            prop="cover"
                            label="封面"
                            width="400">
                        <template slot-scope="scope">
                            <img with="350" height="120" :src="scope.row.cover" alt="">
                        </template>
                    </el-table-column>
                    <el-table-column
                            prop="link"
                            label="链接地址">
                        <template slot-scope="scope">
                            <a target="_blank" :href="scope.row.link">{{ scope.row.link }}</a>
                        </template>
                    </el-table-column>
                    <el-table-column
                            prop="status"
                            label="状态"
                            width="80">
                        <template slot-scope="scope">
                            <el-tag :type="scope.row.status == 1?'success':'danger'"> {{ banners['options']['status'][scope.row.status] }}</el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column
                            prop="begin_at"
                            label="开始时间">
                    </el-table-column>
                    <el-table-column
                            prop="end_at"
                            label="结束时间">
                    </el-table-column>
                    <el-table-column label="操作">
                        <template slot-scope="scope">
                            <router-link :to="{name:'banner_detail', params:{id:scope.row.id}}">
                                <el-button size="small" type="info" icon="el-icon-edit">编辑</el-button>
                            </router-link>
                        </template>
                    </el-table-column>
                </el-table>
            </el-col>
        </el-row>
        <el-row v-if="banners">
            <el-pagination
                    @current-change="handleCurrentChange"
                    :current-page="banners['banners']['current_page']"
                    :page-size="banners['banners']['per_page']"
                    layout="total, prev, pager, next, jumper"
                    :total="banners['banners']['total']">
            </el-pagination>
        </el-row>
    </div>
</template>
<script>
  export default {
    data () {
      return {
        banners: null
      }
    },
    mounted () {
      this.all()
    },
    methods: {
      handleCurrentChange (val) {
        this.$http.get('banner?page=' + val).then(ret => {
          this.banners = ret.data
        })
      },
      all () {
        this.$http.get('banner').then(ret => {
          this.banners = ret.data
        })
      }
    }
  }
</script>
<style>
    .el-transfer-panel {
        height: 350px;
    }

    .el-banner {
        margin: 0 2px;
    }

    .el-table__header, .el-table__body {
        width: 100% !important;
        table-layout: auto !important;
    }

    .el-row {
        margin-bottom: 20px;
    }

    .notice {
        color: red;
        font-weight: bold;
        font-style: italic
    }
</style>
