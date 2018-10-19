<template>
    <div class="sites edit">
        <div class="header-panel">
            <div class="control">
                <div class="operations">
                    <el-button icon="check" type="primary" @click="handleSave" :disabled=" loading ">保存设置</el-button>
                </div>
                <div class="hero-title">主页设置</div>
                <div class="hero-link" >
                    <router-link to="/sites">
                        <i class="el-icon-arrow-left"></i>返回列表
                    </router-link>
                </div>
            </div>
        </div>
        <div class="header-panel-addition inversed"></div>
        <div class="conten" v-loading.body="loading" element-loading-text="数据加载中">
            <el-form label-position="top">
                <div class="flexbox half">
                    <!-- <el-form-item label="域名">
                        <el-input placeholder='请添加已设置首页的域名' v-model="domain" :disabled=" edit!='' "></el-input>
                    </el-form-item> -->
                    <el-form-item label="域名">
                        <el-select v-model="domain" filterable style="width: 100%;" placeholder="请选择或模糊搜索">
                            <el-option
                              v-for="item in domains"
                              :key="item.theme"
                              :label="'www.' + item.domain"
                              :value="'www.' + item.domain">
                            </el-option>
                        </el-select>
                    </el-form-item>
                    
                    <el-form-item label="首页标题">
                        <el-input v-model="title"></el-input>
                    </el-form-item>
                </div>
                <div class="flexbox half">
                    <el-form-item label="语言">
                        <el-select v-model="lang" style="width: 100%;">
                            <el-option
                              v-for="lang in langs"
                              :key="lang.code"
                              :label="lang.title"
                              :value="lang.code">
                            </el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="主页模版">
                        <el-select v-model="theme" style="width: 100%;">
                            <el-option
                              v-for="item in themes"
                              v-if="item.is_del == 0"
                              :key="item.theme"
                              :label="item.title"
                              :value="item.theme">
                            </el-option>
                        </el-select>
                    </el-form-item>
                </div>
                 <div class="flexbox half">
                    <el-form-item label="邮箱">
                        <el-input v-model="mail" type="email"></el-input>
                    </el-form-item>
                </div>
                <div class="flexbox">
                     <el-form-item label="缩略图">
                        <el-upload
                            action="qiniu_um.php?type=sites"
                            list-type="picture-card"
                            :file-list='editProductImageList'
                            name='upfile'
                            :on-change='handleUploadChange'
                            :on-remove='handleUploadChange'

                            >
                        <i class="el-icon-plus"></i>
                    </el-upload>        
                     </el-form-item>    
                </div>
                <div class="flexbox half">
                    <el-form-item label="SEO标题">
                        <el-input v-model="seo_title"></el-input>
                    </el-form-item>
                    <el-form-item label="SEO描述">
                        <el-input v-model="seo_description"></el-input>
                    </el-form-item>
                </div>
                <div class="flexbox half">
                    <el-form-item label="Google Analytics">
                        <el-input type='textarea' :autosize="{ minRows: 8 }" v-model="google_js"></el-input>
                    </el-form-item>
                </div>
            </el-form>
        </div>
    </div>
</template>


<script>
Vue.http.options.headers = {
  "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8"
};
export default {
  computed: {
    username() {
      return this.$store.state.username;
    },
    administrator() {
      return this.$store.state.auth.is_admin;
    }
  },
  data() {
    return {
      domain: "",
      domains: [],
      title: "",
      lang: "TW",
      langs: [],
      theme: "style1",
      themes: [],
      seo_title: "",
      seo_description: "",
      google_js: "",
      loading: true,
      update: 0,
      mail: "",
      logo: "",
      editProductImageList: [],
      edit: ""
    };
  },
  mounted() {

    // 获取域名下拉菜单
    this.getDomains();

    // 读取首页设置配置文件
    this.$http.get("/shop_theme.php?act=site").then(res => {
      let self = this;
      this.themes = (function(username) {
        return res.body.filter(function(item) {
          if (self.username == "buguniao") {
            return true;
          } else {
            return !item.belong || item.belong == username;
          }
        });
      })(this.username);
    });

    this.$http.get("/template/config/theme_home").then(res => {
      let self = this;
      // 读取可选语言包
      this.langs = res.body.langs;
    });

    // 获取域名数据
    this.domain = this.$route.params.domain || "";
    var varThat = this;
    if (this.domain != "") {
      varThat.edit = 1;
    } else {
      varThat.edit = "";
    }
    this.$http
      .get("site.php?act=edit&json=1&domain=" + this.domain)
      .then(res => {
        for (var key in res.body) {
          this[key] = res.body[key];
        }
        if (res.body.title) {
          this.update = 1;
        }
        this.editProductImageList = this.logo
          ? [
              {
                name: this.logo,
                url: this.logo
              }
            ]
          : [];
        this.loading = false;
      });
  },
    methods: {
        getDomains(){
            if( this.$store.state.auth.id_department && this.$store.state.auth.uid ){
                let url = `/product_new.php?&act=getSeoDomain&key=site&id_department=${this.$store.state.auth.id_department}&loginid=${this.$store.state.auth.uid}`;
                
                this.$http.get(url).then(res => {
                    let self = this;
                    this.domains = (function(username) {
                        return res.body.ret.filter(function(item) {
                            if (self.username == "buguniao") {
                                return true;
                            } else {
                                return !item.belong || item.belong == username;
                            }
                        });
                    })(this.username);
                });
            }else{
                var self = this;
                setTimeout(function(){ self.getDomains() }, 500);
            }
        }
        , handleSave() {
          var that = this;

          if (this.domain == "") {
            this.$message.error("请选择域名");
            return false;
          }
          if (this.title == "") {
            this.$message.error("请填写标题");
            return false;
          }
          if (this.lang == "") {
            this.$message.error("请选择语言");
            return false;
          }
          if (this.seo_title == "") {
            this.$message.error("请填写SEO标题");
            return false;
          }
          if (this.seo_description == "") {
            this.$message.error("请填写SEO描述");
            return false;
          }
        //   var pattern = /^<script>.*?<\/script>$/i;
          var pattern = /^<script>([\s\S]+?)<\/script>$/img;

          if(this.google_js !== "" && !pattern.test(this.google_js)){
              var message = "谷歌代码只能以<script>开始,<";
              message+="/script>结束";
              this.$message.error(message);
              return false;
          }
          //   if(this.domain)
          this.loading = true;
          var formdata = new FormData();
          formdata.append("domain", this.domain);
          formdata.append("title", this.title);
          formdata.append("lang", this.lang);
          formdata.append("seo_title", this.seo_title);
          formdata.append("seo_description", this.seo_description);
          formdata.append("google_js", this.google_js);
          formdata.append("act", "save");
          formdata.append("mail", this.mail);
          formdata.append("update", this.update);
          formdata.append("theme", this.theme);
          formdata.append("logo", this.logo);

          this.$http.get(`/site.php?act=search&json=1&domain=` + that.domain).then(function(res) {
              if( this.edit=='' && res.body.ret==1 ){
                  that.$message.error("请重新选择一个没有用过的域名");
                  that.loading = false;
                  return false;
              }

              // post data
              this.$http.post("site.php?json=1", formdata).then(res => {
                if (res.body.code == 1) {
                  if (window.localStorage) {
                    localStorage.removeItem("sites");
                  }
                  this.loading = false;

                  if (this.edit == "") {
                    this.$router.push("/sites/show/" + this.domain);
                  }else{
                    this.$message.success("编辑成功");
                    this.$router.push("/sites/edit/" + this.domain);
                  }
                }else{
                    this.$message.error(res.body.msg);
                    this.loading = false;
                }
              });
          });
        },
    handleUploadChange(file, filelist) {
      if (file.response) {
        this.logo = file.response.url;
        this.editProductImageList = [{ name: "", url: file.response.url }];
      }
      if (filelist.length >= 2) {
        filelist.splice(0, filelist.length - 1);
      } else if (filelist.length == 0) {
        this.logo = null;
      } else {
        if (file.response) {
          this.logo = file.response.url;
          this.editProductImageList = [{ name: "", url: file.response.url }];
        } else {
          //this.logo = file.response.url;
        }
      }
    }
  }
};
</script>