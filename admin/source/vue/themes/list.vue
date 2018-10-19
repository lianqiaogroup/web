<template>
    <div id="page-comment-list">
        <div class="header-panel">
            <div class="control">
                <div class="operations" style="display: flex;">
                    <template v-if="false">
                        <div style="margin-right:8px;">
                            <el-button @click="handleTakeOff()">上架模版 <i class='material-icons'>flight_takeoff</i></el-button>
                        </div>
                        <div style="margin-right:8px;">
                            <el-button @click="handleTakeLand()">下架模版 <i class='material-icons'>flight_land</i></el-button>
                        </div>
                    </template>
                    <div style="margin-right:8px;">
                        <router-link to="/themes/new">
                            <el-button icon="plus" type="primary">增加模版</el-button>
                        </router-link>
                    </div>
                </div>
                <div class='op-flexbox' v-on:keyup.enter="get" style="width:460px">
                    <div class='op-box'>
                        <el-dropdown class='select-category' @command='handleSearchCategory'>
                            <span class="el-dropdown-link" trigger='click'>
                                <span ref='category'>地区</span>
                                <i class="el-icon-caret-bottom el-icon--right"></i>
                            </span>
                            <el-dropdown-menu slot="dropdown">
                                <el-dropdown-item command='模版代号|theme_code'>代号</el-dropdown-item>
                                <!-- <el-dropdown-item command='模版ID|theme_id'>模版ID</el-dropdown-item> -->
                                <el-dropdown-item command='地区|region_code'>地区</el-dropdown-item>
                                <el-dropdown-item command='语言|lang'>语言</el-dropdown-item>
                            </el-dropdown-menu>
                        </el-dropdown>
                        <el-input
                            :placeholder="'输入' + searchKey"
                            icon="search"
                            v-model="keyword"
                            ref='textBox'
                            :on-icon-click="get"
                            v-if="searchCmd=='theme_code'">
                        </el-input>
                        <el-select
                            v-if="searchCmd=='region_code'"
                            v-model="selectedRegion_code"
                            icon="search"
                            @change="get"
                            placeholder="请选择地区">
                            <el-option
                                v-for="region in regions"
                                :key="region.code"
                                :label="region.title"
                                :value="region.code">
                            </el-option>
                        </el-select>
                        <el-select
                            v-if="searchCmd=='lang'"
                            v-model="selectedLang_code"
                            icon="search"
                            @change="get"
                            placeholder="请选择语种">
                            <el-option
                                v-for="lang in langList"
                                :key="lang.code"
                                :label="lang.title"
                                :value="lang.code">
                            </el-option>
                        </el-select>
                   </div>
                   <div style="margin-left:20px;margin-top:10px"><el-checkbox v-model="isDel" @change="handleSearch">已下架模版</el-checkbox></div>
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
			    <el-table-column prop="tid" label="ID" width="100"></el-table-column>
                <el-table-column prop="theme" label="模版代号"></el-table-column>
                <el-table-column prop="title" label="模版名称"></el-table-column>
                <el-table-column prop="zone" label="地区"></el-table-column>
                <el-table-column prop="lang" label="语种"></el-table-column>
                <el-table-column prop="desc" label="模版简介"></el-table-column>
                <el-table-column prop="theme_count" label="使用数"></el-table-column>
                <el-table-column prop="" label="操作" width="200">
                    <template scope="scope">
                        <div v-if=" scope.row.is_del == 0 ">
                            <a class="scope-op" v-bind:href="scope.row.thumb" target="_blank">预览</a>
                            <a class="scope-op" href="javascript:void(0)" @click='handleEdit(scope.row.tid)'>编辑</a>
                            <a class="scope-op" href='javascript:void(0)' @click='handleTakeLand(scope.row.tid)'>下架</a>
                        </div>
                        <div v-if=" scope.row.is_del == 1 ">
                            <a class="scope-op" v-bind:href="scope.row.thumb">预览</a>
                            <a class="scope-op danger" href='javascript:void(0)' @click='handleTakeOff(scope.row.tid)'>上架</a>
                        </div>
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
        isDel:false,  //已下架套餐 是否选中
        keyword: "",
        withDel: false,
        table_loading: false,
        list: [],
        page: 1,
        page_count: 0,
        searchCmd: "region_code",
        searchKey: "地区",
        selected: [],
        selectedRegion_code: "",
        regions: [],
        langList:[], //语言列表
        selectedLang_code:'', //选中的语种
        theme_count:''
    };
  },
  mounted() {
    // get country
    this.$service("country#list").then(
      res => (this.regions = [{ title: "所有", code: "" }].concat(res.body))
    );
    // 获取语言列表
    this.$http.get('/template/config/theme_language').then(res=>{
        var jsonString = res.bodyText.replace(/\s/g, '');
        var data = JSON.parse(jsonString);
        for(let code in data){
            this.langList.push({
                'code': code,
                'title': data[code]
            });
        }
        this.langList = [{ title: "所有", code: "" }].concat(this.langList);
        // console.log(this.langList);
    });
    // get theme datas
    this.get();
  },
  methods: {
    get() {
        this.table_loading = true;
        if (this.searchCmd == "region_code") {
          this.keyword = this.selectedRegion_code;
        }
        if(this.searchCmd == "lang"){
          this.keyword = this.selectedLang_code;
        }
        //判断是否勾选已下架套餐
        if(this.isDel){
            this.$http.get(`/theme.php?p=${this.page}&is_del=1&${this.searchCmd}=${this.keyword}`).then(res=>{
                this.table_loading = false;
                this.set(res.body);
            });
        }else {
            this.$http.get(`/theme.php?p=${this.page}&is_del=0&${this.searchCmd}=${this.keyword}`).then(function(res) {
                this.table_loading = false;
                this.set(res.body);
                // console.log(res.body);
            });
        }
        
    }, 
    set(response) {
      this.list = response.goodsList;
      this.page_count = response.pageCount;
      this.page = parseInt(response.page);
    },
    handleCurrentChange(val) {
      this.page = val;
      this.get();
    },
    handleEdit(id) {
      this.$router.push({ path: "/themes/edit/" + id });
    },
    // 上架
    handleTakeOff(id) {
      var self = this;
      var tid = id ? id : this.selected.join(",");
      if (tid == "") {
        return false;
      }
      this.$http.get("/theme.php?act=del&is_del=0&tid=" + tid).then(res => {
        if (res.body.ret == "1") {
          self.$message({ message: "上架成功", type: "success" });
          self.get();
        }
      });
    },
    // 下架
    handleTakeLand(id) {
      var self = this;
      var tid = id ? id : this.selected.join(",");
      if (tid == "") {
        return false;
      }
      this.$http.get("/theme.php?act=del&is_del=1&tid=" + tid).then(res => {
        if (res.body.ret == "1") {
          self.$message({ message: "下架成功", type: "success" });
          self.get();
        }
      });
    },
    // 删除颜色
    tableRowClassName(row, index) {
      return row.is_del == 1 ? "del-row" : "";
    },
    handleSearchCategory(cmd) {
      let [text, cmdkey] = cmd.split("|");
      this.searchKey = text;
      this.searchCmd = cmdkey;
      this.$refs.category.innerText = text;
      this.keyword = '';
      // this.page = 1;
      // this.get();
    },
    handleSelectionChange(val) {
      this.selected = val.map(x => x.tid);
    },
    //查询已下架套餐
    handleSearch(){
        console.log(this.isDel);
        this.get();
    }
  }
};
</script>

