<template>
    <div id="page-home-search">
        <div class="header-panel">
            <div class="control">
                <div>输入要搜索的主页域名</div>
                <el-input
                    placeholder="输入域名"
                    icon="search"
                    v-model="keyword"
                    id="keyword"
                    :on-icon-click="handleSearch">
                </el-input>
            </div>
        </div>
		<div class="container">
			历史记录：
		</div>
	</div>
</template>

<script>
Vue.http.options.headers = {
  'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
};
export default {
    data() {
      return { 
        keyword: ""
      }
    }
    , mounted() {
        document.getElementById("keyword").addEventListener('keydown', function(key){
            if( key.keyCode == 13 ){
                self.handleSearch();
            }
        });
    }
    , methods: {
        handleSearch(){
            if( this.keyword == "" ){ return false; }
            this.$http.get('/site.php?act=search&domain='+this.keyword+"&json=1").then( res => {
                if( res.body.domainList.length > 0 ){
                    this.$router.push({path:'/home/'+this.keyword});
                }
            });
        }
    }
  }
</script>

