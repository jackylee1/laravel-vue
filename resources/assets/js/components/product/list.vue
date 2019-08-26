<template>
    <div>
        <el-row>
            <router-link :to="{ name:'product_create' }">
                <el-button type="primary" icon="el-icon-circle-plus">添加产品</el-button>
            </router-link>
        </el-row>
        <el-row v-if="products">
            <el-col>
                <el-table :data="products['products']['data']">
                    <el-table-column
                            prop="id"
                            label="编号">
                    </el-table-column>
                    <el-table-column
                            prop="name"
                            label="产品名称">
                    </el-table-column>
                    <el-table-column
                            prop="category"
                            label="类别">
                        <template slot-scope="scope">
                            <el-tag>{{ products['options']['category'][scope.row.category] }}</el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column
                            prop="price"
                            label="价格">
                        <template slot-scope="scope">
                            ¥{{(scope.row.price / 100).toFixed(2)}}
                        </template>
                    </el-table-column>
                    <el-table-column
                            prop="commission_rate"
                            label="佣金率">
                        <template slot-scope="scope">
                            {{(scope.row.commission_rate * 100).toFixed(2)}}%
                        </template>
                    </el-table-column>
                    <el-table-column
                            prop="inventory"
                            label="库存">
                        <template slot-scope="scope">
                            <span :class="{'notice': scope.row.inventory < 10 }"> {{ scope.row.inventory }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column
                            prop="sale_count"
                            label="销量">
                    </el-table-column>
                    <el-table-column
                            prop="status"
                            label="状态">
                        <template slot-scope="scope">
                            <el-tag :type="scope.row.status == 1?'success':'danger'">{{ products['options']['status'][scope.row.status] }}</el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column label="操作">
                        <template slot-scope="scope">
                            <router-link :to="{name:'product_poster', params:{id:scope.row.id}}">
                                <el-button size="small" type="default" icon="">海报</el-button>
                            </router-link>
                            <router-link :to="{name:'product_detail', params:{id:scope.row.id}}">
                                <el-button size="small" type="info" icon="el-icon-edit">编辑</el-button>
                            </router-link>
                        </template>
                    </el-table-column>
                </el-table>
            </el-col>
        </el-row>
        <el-row v-if="products">
            <el-pagination
                    @current-change="handleCurrentChange"
                    :current-page="products['products']['current_page']"
                    :page-size="products['products']['per_page']"
                    layout="total, prev, pager, next, jumper"
                    :total="products['products']['total']">
            </el-pagination>
        </el-row>
    </div>
</template>
<script>
  export default {
    data () {
      return {
        products: {
          products: {
            data: []
          }
        }
      }
    },
    mounted () {
      this.all()
    },
    methods: {
      handleCurrentChange (val) {
        this.$http.get('product?page=' + val).then(ret => {
          this.products = ret.data
        })
      },
      all () {
        this.$http.get('product').then(ret => {
          this.products = ret.data
        })
      }
    }
  }
</script>
<style>
    .el-transfer-panel {
        height: 350px;
    }

    .el-product {
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
