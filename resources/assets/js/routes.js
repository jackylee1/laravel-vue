const Base          = resolve => require(['./components/base.vue'], resolve)
const User          = resolve => require(['./components/system/user.vue'], resolve)
const Member        = resolve => require(['./components/member/list.vue'], resolve)
const Permission    = resolve => require(['./components/system/permission.vue'], resolve)
const Role          = resolve => require(['./components/system/role.vue'], resolve)
const Login         = resolve => require(['./components/system/login.vue'], resolve)
const Tag           = resolve => require(['./components/product/tag.vue'], resolve)
const ProductList   = resolve => require(['./components/product/list.vue'], resolve)
const ProductStep   = resolve => require(['./components/product/config_steps.vue'], resolve)
const ProductForm   = resolve => require(['./components/product/form.vue'], resolve)
const ProductPoster = resolve => require(['./components/product/poster.vue'], resolve)
const PE            = resolve => require(['./components/product/pe/form.vue'], resolve)
const Order         = resolve => require(['./components/order/list.vue'], resolve)
const OrderDetail   = resolve => require(['./components/order/detail.vue'], resolve)
const Banner        = resolve => require(['./components/banner/list.vue'], resolve)
const BannerForm    = resolve => require(['./components/banner/form.vue'], resolve)
const Agent         = resolve => require(['./components/agent/list.vue'], resolve)
const AgentTree         = resolve => require(['./components/agent/tree.vue'], resolve)
const AgentIdentify        = resolve => require(['./components/agent/identify.vue'], resolve)
const Withdraw      = resolve => require(['./components/withdraw/list.vue'], resolve)
const SendSms       = resolve => require(['./components/system/sms.vue'], resolve)

const routes = [
  {
    path: '/login',
    component: Login
  },
  {
    path: '/',
    component: Base,
    beforeEnter: (to, from, next) => {
      console.log('routes.beforeEnter')
      if (localStorage.getItem('token')) {
        next()
      } else {
        next('/login')
      }
    },
    children: [
      {
        path: '/user',
        component: User
      }, {
        path: '/permission',
        component: Permission
      }, {
        path: '/role',
        component: Role
      }, {
        path: '/tag',
        component: Tag
      }, {
        path: '/product',
        component: ProductList
      }, {
        path: '/product',
        component: ProductStep,
        children: [
          {
            path: 'create',
            name: 'product_create',
            component: ProductForm
          },
          {
            path: ':id/edit',
            name: 'product_detail',
            component: ProductForm
          },
          {
            path: ':id/poster',
            name: 'product_poster',
            component: ProductPoster
          },
          {
            path: ':id/pe',
            component: PE
          }
        ]
      }, {
        path: '/order',
        name: 'order',
        component: Order
      }, {
        path: '/order/:id',
        name: 'order_detail',
        component: OrderDetail
      }, {
        path: '/banner',
        name: 'banner',
        component: Banner
      },
      {
        path: '/banner/create',
        name: 'banner_create',
        component: BannerForm
      },
      {
        path: '/banner/:id/edit',
        name: 'banner_detail',
        component: BannerForm
      },
      {
        path: '/agent',
        name: 'agent',
        component: Agent
      },
      {
        path: '/withdraw',
        name: 'withdraw',
        component: Withdraw
      },
      {
        path: '/member',
        component: Member
      },
      {
        path: '/agent_tree',
        name: 'agent_tree',
        component: AgentTree
      },
      {
        path: '/agent/:id',
        name: 'agent_identify',
        component: AgentIdentify
      },
      {
        path: 'sms',
        name: 'send_sms',
        component: SendSms
      }
    ]
  }
]

export default routes