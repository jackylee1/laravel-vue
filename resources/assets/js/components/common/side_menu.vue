<template>
    <div>
        <el-row style="margin-bottom: 40px;color: white">
            <h2>代理人管理系统</h2>
        </el-row>
        <el-row>
            <el-menu background-color="#545c64" text-color="#fff" active-text-color="#ffd04b" router :default-active="$route.path">
                <el-submenu index="product">
                    <template slot="title"><i class="el-icon-menu"></i>产品管理</template>
                    <el-menu-item-group>
                        <el-menu-item index="/banner">广告</el-menu-item>
                        <el-menu-item index="/product">产品</el-menu-item>
                        <el-menu-item index="/tag">标签</el-menu-item>
                    </el-menu-item-group>
                </el-submenu>
                <el-submenu index="order">
                    <template slot="title"><i class="el-icon-tickets"></i>订单管理</template>
                    <el-menu-item-group>
                        <el-menu-item index="/order">订单</el-menu-item>
                    </el-menu-item-group>
                </el-submenu>
                <el-submenu index="member">
                    <template slot="title"><i class="el-icon-tickets"></i>代理人管理</template>
                    <el-menu-item-group>
                        <el-menu-item index="/member">用户管理</el-menu-item>
                        <el-menu-item index="/agent">代理人</el-menu-item>
                        <el-menu-item index="/agent_tree">代理人层级图</el-menu-item>
                        <el-menu-item index="/withdraw">提现申请</el-menu-item>
                    </el-menu-item-group>
                </el-submenu>
                <el-submenu index="system">
                    <template slot="title"><i class="el-icon-setting"></i>系统设置</template>
                    <el-menu-item-group>
                        <el-menu-item index="/user">管理员</el-menu-item>
                        <el-menu-item index="/role">角色</el-menu-item>
                        <el-menu-item index="/permission">权限</el-menu-item>
                        <el-menu-item index="/sms">发送短信</el-menu-item>
                    </el-menu-item-group>
                </el-submenu>
            </el-menu>
        </el-row>
    </div>
</template>

<style scoped>

</style>