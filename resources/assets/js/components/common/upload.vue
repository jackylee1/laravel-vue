<template>
    <el-upload :http-request="Upload" :multiple="false" list-type="picture-card" action="" :limit="1" :file-list="files">
        <el-button size="small" type="primary">点击上传</el-button>
    </el-upload>
</template>
<script>
  import Wrapper from 'ali-oss'

  export default {
    data () {
      return {
        oss: null
      }
    },
    props: ['files', 'prefix'],// 注意files格式：[{ 'url': xxx }]
    created () {
      this.$http.get('upload/policy').then(ret => {
        this.oss = new Wrapper(ret.data)
      })
    },
    methods: {
      Upload (file) {
        let fileName = 'agent/' + this.prefix + file.file.uid
        //定义唯一的文件名，打印出来的uid其实就是时间戳
        //后台获得阿里服务器所需参数参数
        this.oss.put(fileName, file.file).then(
            result => {
              this.$emit('update:src', 'https://image.xinglin.ai/'+result.name)
            }).catch(err => {
        })
      }
    }
  }
</script>
