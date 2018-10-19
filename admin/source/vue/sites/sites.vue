<template>
    <div id="page-comment-list">
        <div class="header-panel">
            <div class="control">
                <div class="operations" style="display: flex;">
                    <router-link to="/sites/del">
                        <el-button type="primary" plain><i class="el-icon-delete"></i> 回收站</el-button>
                    </router-link>
                    
                    <router-link to="/sites/edit">
                        <el-button icon="plus" type="primary" @click='$router.push("/sites/edit")'>增加主页</el-button>
                    </router-link>
                </div>
                <div class='op-flexbox' v-on:keyup.enter="get">
                    <div class='op-box'>
                        <el-dropdown class='select-category' @command='handleSearchCategory'>
                            <span class="el-dropdown-link" trigger='click'>
                                <span ref='category'>域名</span>
                                <i class="el-icon-caret-bottom el-icon--right"></i>
                            </span>
                            <el-dropdown-menu slot="dropdown">
                                <el-dropdown-item command='域名|domain'>域名</el-dropdown-item>
                                <el-dropdown-item command='模版代号|theme_code'>模版代号</el-dropdown-item>
                                <el-dropdown-item command='语种|lang'>语种</el-dropdown-item>
                            </el-dropdown-menu>
                        </el-dropdown>
                        <el-input
                            :placeholder="'输入' + searchKey"
                            icon="search"
                            v-model="keyword"
                            ref='textBox'
                            :on-icon-click="get"
                            v-if="searchCmd=='domain'">
                        </el-input>
                        <el-select
                            v-if="searchCmd=='lang'"
                            v-model="selectedLang"
                            icon="search"
                            @change="get"
                            placeholder="请选择语种">
                            <el-option
                                v-for="lang in langs"
                                :key="lang.code"
                                :label="lang.title"
                                :value="lang.code">
                            </el-option>
                        </el-select>
                        <el-select
                            v-if="searchCmd=='theme_code'"
                            v-model="selectedTheme"
                            icon="search"
                            @change="get"
                            placeholder="请选择模板代号">
                            <el-option
                                v-for="theme in themes"
                                :key="theme"
                                :label="theme"
                                :value="theme">
                            </el-option>
                        </el-select>
                   </div>
                </div>
            </div>
	    </div>
		<div class="container">
			<el-table
			    ref="multipleTable"
			    :data="list"
			    border
			    tooltip-effect="dark"
                v-loading='table_loading'
                element-loading-text="拼命加载中"
                :row-class-name="tableRowClassName"
                :default-sort = "{prop: 'created_at', order: 'descending'}"
                @selection-change="handleSelectionChange"
                >
                <el-table-column prop="" label="" width="50"></el-table-column>
                <el-table-column type="selection" width="55"></el-table-column>
			          <!-- <el-table-column prop="tid" label="ID" width="100"></el-table-column> -->
                <el-table-column prop="title" label="名称"></el-table-column>
                <el-table-column prop="domain" label="域名"></el-table-column>
                <!-- <el-table-column prop="zone" label="地区"></el-table-column> -->
                <el-table-column prop="lang" label="语种"></el-table-column> 
                <el-table-column prop="theme" label="模版代号"></el-table-column>
                <el-table-column prop="ad_member" label="创建者"></el-table-column>
                <el-table-column prop="department" label="所属部门"></el-table-column>
                <el-table-column prop="" label="操作" width="200">
                    <template scope="scope">
                        <a class="scope-op" href="javascript:void(0)" @click='handleSet(scope.row.domain)'>设置</a>
                        <a class="scope-op" href="javascript:void(0)" @click='handleEdit(scope.row.domain)'>编辑</a>
                        <a class="scope-op" href="javascript:void(0)" @click='handleTakeLand(scope.row.domain)'>下架</a>
                    </template>
                </el-table-column>
			</el-table>
      <el-pagination
                small
                layout="prev, pager, next"
                :page-size="1"
                :current-page.sync="page"
                @current-change="handleCurrentChange"
                :total="page_count"
                >
            </el-pagination>
		</div>
	</div>
</template>

<script>
Vue.http.options.headers = {
  "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8"
};
export default {
  data() {
    return {
      keyword: "",
      withDel: false,
      table_loading: false,
      list: [],
      page: 1,
      cmdtext: "域名",
      page_count: 0,
      theme_code:'',
      searchCmd: "domain",
      searchKey: "域名",
      // selected: [],
      // regions: []
      selected: [], 
      selectedLang: '', 
      langs: [
        {
          code: "TW",
          title: "繁体中文"
        },
        {
          code: "CN",
          title: "简体中文"
        },
        {
          code: "EN",
          title: "英语"
        },
        {
          code: "JP",
          title: "日语"
        },
        {
          code: "AR",
          title: "阿拉伯语"
        },
        {
          code: "VNM",
          title: "越南语"
        },
        {
          code: "THA",
          title: "泰语"
        }
      ],
      selectedTheme:'',
      themes:[]
    };
  },
  mounted() {
    var c = [];
    var a = [{code: "ALL",title: "所有语种"}];
    var b = this.langs;
    c = a.concat(b);
    this.langs = c;
    // get theme datas
    this.get();
    
  },
  methods: {
    get() {
      this.table_loading = true;
      if( this.searchCmd=='lang' ){
          if(this.selectedLang == "ALL"){
            this.keyword = "";
          }else{
            this.keyword = this.selectedLang;
          }
      }else if( this.searchCmd=='theme_code' ){
        if(this.selectedTheme == "所有模板"){
          this.keyword = "";
        }else{
          this.keyword = this.selectedTheme;
        }
      }
      this.$http
        .get(
          '/site.php?act=theme_code_list'
        ).then(function(theme){
          var a = ["所有模板"];
          this.themes = [];
          var b = theme.body.list;
          this.themes = a.concat(b);
        })
      
      this.$http
        .get(
          `/site.php?act=search&is_del=0&key=list&p=` +
            this.page +
            "&" +
            this.searchCmd +
            "=" +
            this.keyword
        )
        .then(function(res) {
          this.table_loading = false;
          Array.isArray(res.body.domainList) != true &&
            this.set(res.body.domainList);
        });
    },

    set(response) {
      this.list = [];
      this.list = response.goodsList;
      this.page_count = response.pageCount;
      this.page = parseInt(response.page);
    },
    handleCurrentChange(val) {
      this.page = val;
      this.get();
    },
    handleSet(id) {
      this.$router.push({ path: "/sites/edit/" + id });
    },
    handleEdit(id) {
      this.$router.push({ path: "/sites/show/" + id });
    },
    // 上架
    handleTakeOff(id) {
      var self = this;
      var domain = id ? id : this.selected.join(",");
      if (domain == "") {
        return false;
      }
      this.$http
        .post("/site.php?act=delete&domain=" + domain + "&is_del=0&")
        .then(res => {
          if (res.body.ret == "1") {
            self.$message({ message: "上架成功", type: "success" });
            self.get();
          } else {
            self.$message({ message: "上架失败", type: "error" });
          }
        });
    },
    // 下架
    handleTakeLand(id) {
      var self = this;
      var domain = id ? id : this.selected.join(",");
      if (domain == "") {
        return false;
      }
      this.$http
        .post("/site.php?act=delete&domain=" + domain + "&is_del=1")
        .then(res => {
          if (res.body.ret == "1") {
            self.$message({ message: domain + "已经下架到回收站", type: "success" });
            self.get();
          } else {
            self.$message({ message: "下架失败", type: "error" });
          }
        });
    },
    // 删除颜色
    tableRowClassName(row, index) {
      return row.is_del == 1 ? "del-row" : "";
    },
    handleSearchCategory(cmd) {
      this.keyword = "";
      let [text, cmdkey] = cmd.split("|");
      this.searchKey = text;
      this.searchCmd = cmdkey;
      this.$refs.category.innerText = text;
    },
    handleSelectionChange(val) {
      this.selected = val.map(x => x.tid);
    }
  }
};
</script>

