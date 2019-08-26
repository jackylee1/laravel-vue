<template>
    <div>
        <!--<el-row>-->
        <!--<el-form :inline="true" :model="search" class="demo-form-inline">-->
        <!--<el-form-item label="代理人昵称">-->
        <!--<el-input v-model="search.nick_name" placeholder=""></el-input>-->
        <!--</el-form-item>-->
        <!--<el-form-item>-->
        <!--<el-button type="primary" @click="doSearch">查询</el-button>-->
        <!--</el-form-item>-->
        <!--</el-form>-->
        <!--</el-row>-->
        <el->
            <h3>代理人层级关系图</h3>
        </el->
        <el-row>
            <ve-tree ref="chartEl" :width="width" :height="height" :data="chartData" :settings="chartSettings" :tooltip-formatter="tooltipFormatter"></ve-tree>
        </el-row>
    </div>
</template>

<script>
  import veTree from 'v-charts/lib/tree'

  export default {
    data () {
      this.chartSettings = {
        seriesMap: {
          tree: {
            symbol: 'emptyCircle',
            initialTreeDepth: 5,
            orient: 'TB',
            label: {
              position: 'left',
              rotate: -90,
              fontSize: 16
            },
            tooltip: {
              position: 'bottom'
            }
          }
        }
      }
      return {
        search: {
          nick_name: null
        },
        width: 'auto',
        height: '800px',
        chartData: {
          columns: ['name', 'value'],
          rows: [
            {
              'name': 'tree',
              'value': []
            }
          ]
        }
      }
    },
    created () {
      if (this.$route.query.nick_name) {
        this.search.nick_name = this.$route.query.nick_name
      }
      this.$http.get('/agent/tree_data', { params: this.search }).then(ret => {
        this.chartData.rows[0].value = []
        this.chartData.rows[0].value.push(ret.data)
      })
    },
    methods: {
      // doSearch () {
      //   this.$http.get('/agent/tree_data?nick_name=' + this.search.nick_name).then(ret => {
      //     this.chartData.rows[0].value = []
      //     this.chartData.rows[0].value.push(ret.data)
      //   })
      // },
      tooltipFormatter (v) {
        return [
          `代理人NO: ${v.data.id}`,
          `订单数： ${v.data.order_count}`
        ].join('<br>')
      }
    },
    components: { veTree }
  }
</script>