require('./bootstrap')

import VueRouter from 'vue-router'
import Vue from 'vue'
import ElementUI from 'element-ui'
import 'element-ui/lib/theme-chalk/index.css'
import routes from './routes'

axios.defaults.baseURL = '/admin/'

Vue.prototype.$http = axios

Vue.use(VueRouter)
Vue.use(ElementUI)

axios.interceptors.request.use(
    config => {
      // 这里写死一个token，你需要在这里取到你设置好的token的值
      let token = localStorage.getItem('token')
      let hash  = localStorage.getItem('hash')
      if (token && hash) {
        config.headers['Api-Token']      = token
        config.headers['Api-Token-Hash'] = hash
      }
      return config
    },
    error => {
      return Promise.reject(error)
    }
)

axios.interceptors.response.use(function(response) {
  //对响应数据做些事
  return response
}, function(error) {
  // //请求错误时做些事
  if (error.response.status == 401) {
    router.push('/login')
  } else if (error.response.status == 403) {
    alert(error.response.data.message)
    return
  }
  return response
})

const router = new VueRouter({
  routes,
  mode: 'history'
})

new Vue({
  router,
  el: '#app'
})
