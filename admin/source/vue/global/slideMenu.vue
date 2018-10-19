 <template>
    <div class="slideMenu">
        <headers :username="username"></headers>
        <el-menu theme="dark" class="el-menu-vertical-demo" :unique-opened="true">
            <template v-if=" username == ga_account ">
                <el-menu-item class="doubleClick" index="1">
                    <router-link to="/">
                        <i class="el-icon-dpz-home"></i>
                        <span>首页</span>
                    </router-link>
                </el-menu-item>
                <el-menu-item class="doubleClick" index="2">
                    <router-link to="/products">
                        <i class="el-icon-dpz-product"></i>
                        <span>单品站管理（所有产品）</span>
                    </router-link>
                </el-menu-item>
                <el-menu-item  index="3">
                    <a href="/document/index.html" target="_blank">
                        <i class="el-icon-dpz-help"></i>
                        <span>帮助中心</span>
                    </a>
                </el-menu-item>
            </template>    

            <template v-else>
                <el-menu-item class="doubleClick" index="1">
                    <router-link to="/">
                        <i class="el-icon-dpz-home"></i>
                        <span>首页</span>
                    </router-link>
                </el-menu-item>
                <el-menu-item class="doubleClick" index="3">
                    <router-link to="/products">
                        <i class="el-icon-dpz-product"></i>
                        <span>单品站管理（所有产品）</span>
                    </router-link>
                </el-menu-item>
                <el-menu-item class="doubleClick" index="2">
                    <router-link to="/sites">
                        <i class="el-icon-dpz-site"></i>
                        <span>集合站管理（主页管理）</span>
                    </router-link>
                </el-menu-item>
                <el-menu-item class="doubleClick" index="12"
                  v-if="administrator ||id_department == 529 || id_department == 497 ||id_department == 530 ||id_department == 675 ||id_department == 685 ||id_department == 90||id_department == 70||id_department == 71||id_department == 91||id_department == 516||id_department == 565">
                    <router-link to="/advert">
                        <i class="el-icon-dpz-theme"></i>
                        <span>软文站管理</span>
                    </router-link>
                </el-menu-item>
                <el-menu-item class="doubleClick" index="4">
                    <router-link to="/comments">
                        <i class="el-icon-dpz-comment"></i>
                        <span>评论管理</span>
                    </router-link>
                </el-menu-item>
                <el-menu-item class="doubleClick" index="5">
                    <router-link to="/analytics">
                        <i class="el-icon-search"></i>
                        <span>订单信息查询</span>
                    </router-link>
                </el-menu-item>

                <el-menu-item index="17"  class="doubleClick"
                v-if="administrator || id_department == 529 || id_department == 497 ||id_department == 530 ||id_department == 661  ||id_department == 668  ||id_department == 669">
                    <router-link to="/cloak">
                        <i class="el-icon-dpz-moban"></i>
                        <i class="el-icon-bell"></i>
                        <span>cloak关联管理</span>
                    </router-link>
                </el-menu-item>
                <el-menu-item  v-if="administrator" index="5">
                        <router-link to="/orders">
                            <i class="el-icon-dpz-order"></i>                            
                            <span>订单明细查询</span>
                        </router-link>
                </el-menu-item>




            <el-submenu  v-if="administrator || id_department == 48|| id_department ==115|| id_department ==721|| id_department ==722|| id_department ==723|| id_department ==724|| id_department ==725|| id_department ==726|| id_department ==727|| id_department ==728|| id_department ==729|| id_department ==730|| id_department ==731" index="80">
                    <template slot="title">
                        <i class="el-icon-menu"></i>
                        <span>素材管理</span>
                    </template>
                    <router-link to="/material">
                        <el-menu-item index="80-1" >素材库</el-menu-item>
                    </router-link>
                    <router-link to="/tag" v-if="administrator || uid == 279">
                        <el-menu-item index="80-2" >标签库</el-menu-item>
                    </router-link>
                </el-submenu>

                <el-submenu v-if="(administrator || id_department == 731 || id_department == 722 ||id_department == 723 ||id_department == 724  ||id_department == 725  ||id_department == 726||id_department == 727||id_department == 728||id_department == 729||id_department == 730||id_department == 721) " index="70">
                    <template slot="title">
                        <i class="el-icon-dpz-currency"></i>
                        <span>广告模块</span>
                    </template>
                    <router-link to="/fmp/add">
                        <el-menu-item index="70-1" >广告素材</el-menu-item>
                    </router-link>    
                    <router-link to="/fmp/list">
                        <el-menu-item index="70-2" >提交纪录</el-menu-item>
                    </router-link>
                </el-submenu>
                
                <el-submenu index="30"  v-if="administrator">
                    <template slot="title">
                        <i class="el-icon-dpz-theme"></i>
                        <span>模板管理</span>
                    </template>
                    <router-link to="/themes">
                        <el-menu-item index="30-1" >单品站模板管理</el-menu-item>
                    </router-link>    
                    <router-link to="/shopThemes">
                        <el-menu-item index="30-2" >集合站模板管理</el-menu-item>
                    </router-link>
                </el-submenu>

                <el-submenu  v-if="administrator" index="40">
                    <template slot="title">
                        <i class="el-icon-dpz-currency"></i>
                        <span>系统设置</span>
                    </template>
                    <router-link to="/pay">
                        <el-menu-item index="40-1" >支付管理</el-menu-item>
                    </router-link>    
                    <router-link to="/country">
                        <el-menu-item index="40-2" >地区管理</el-menu-item>
                    </router-link>
                    <router-link to="/currency">
                        <el-menu-item index="40-3" >货币管理</el-menu-item>
                    </router-link>
                    <router-link to="/sms/isp_states">
                        <el-menu-item index="40-4" >短信配置</el-menu-item>
                    </router-link>
                </el-submenu>

                <el-submenu index="50"  v-if="administrator">
                    <template slot="title">
                        <i class="el-icon-dpz-admin"></i>
                        <span>管理员</span>
                    </template>
                    <router-link to="/user">
                        <el-menu-item index="50-1" >管理员列表</el-menu-item>
                    </router-link>    
                    <router-link to="/userlog">
                        <el-menu-item index="50-2" >管理员操作日志</el-menu-item>
                    </router-link>
                </el-submenu>

                <el-submenu index="60"  v-if="administrator">
                    <template slot="title">
                        <i class="el-icon-dpz-home"></i>
                        <span>操作</span>
                    </template>
                    <router-link to="/operationlog">
                        <el-menu-item index="60-1" >站点操作日志</el-menu-item>
                    </router-link>    
                    <router-link to="/copySite">
                        <el-menu-item index="60-2" >跨系统复制站点</el-menu-item>
                    </router-link>
                    <router-link to="/reportStatistics">
                        <el-menu-item index="60-3" >爆款站查询</el-menu-item>
                    </router-link>
                </el-submenu>

                <el-menu-item  index="20">
                    <a href="/document/index.html" target="_blank">
                        <i class="el-icon-dpz-help"></i>
                        <span>帮助中心</span>
                    </a>
                </el-menu-item>
            </template>
        </el-menu>
    </div>
</template>
<style scoped>
/* 侧边栏修改滚动条颜色 */ 
.slideMenu .el-menu::-webkit-scrollbar-track
{
	-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
	border-radius: 10px;
	background-color: #F5F5F5;
}

.slideMenu .el-menu::-webkit-scrollbar
{
	width: 12px;
	background-color: #F5F5F5;
}

.slideMenu .el-menu::-webkit-scrollbar-thumb
{
	border-radius: 10px;
	-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,.3);
	background-color: #555;
}

</style>


<script>
import headers from "./headers.vue";

export default {
  data() {
    return {
      ga_account: "googleID"
    };
  },
  computed: {
    username() {
      return this.$store.state.auth.username;
    },
    administrator() {
      return this.$store.state.auth.is_admin;
    },
     id_department() {
      return this.$store.state.auth.id_department;
    },
    uid(){
        return this.$store.state.auth.uid
    }
  },
  components: { headers },
};
</script>


