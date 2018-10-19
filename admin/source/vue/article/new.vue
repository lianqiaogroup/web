<template>
  <div class="sites">
        <div class="header-panel">
            <div class="control">
                <div class="operations">
                        <el-button icon="check" type="primary" @click="onSubmit">确认新增</el-button>
                </div>
                <div class="hero-title" >
                        新增文章
                </div>
                <div class="hero-link">
                  <span @click="handleBack">
                      <i class="el-icon-arrow-left"></i>返回列表
                  </span>
                </div>
            </div>
        </div>
        <div class="header-panel-addition inversed"></div>
  <div class="article-new" id="app">
    <div class='flexbox'>
      <el-form ref="form" :model="form" label-width="80px">
        <input v-model="articleid" type="hidden">
        <el-form-item label="域名">
          <el-input v-model="form.domain" placeholder="请添加已设置首页的域名"></el-input>
        </el-form-item>
        <el-form-item label="标题">
          <el-input v-model="form.title" placeholder=""></el-input>
        </el-form-item>
        <el-form-item label="排序">
          <el-input v-model="form.sort" placeholder="数值越大越靠前"></el-input>
        </el-form-item>
        <el-form-item label="文章内容">
          <textarea id="editor" type="textarea">

          </textarea>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="onSubmit">保存</el-button>
          <el-button @click="returnback">取消</el-button>
        </el-form-item>
      </el-form>
    </div>
    </div>
   </div>
</template>
<script>
Vue.http.options.emulateJSON = true;
export default {
    name: "app",
    data() {
        return {
            articleid: "",
            form: {
                domain: "",
                title: "",
                sort: ""
            },
            content: "",
            editor: null
        };
    },
    mounted() {
        //初始化
        //this.$nextTick(function(){
        if (!UE) {
            return false;
        }
        this.editor = UE.getEditor("editor");
        if (this.$route.params.id) {
            this.$http
                .get(
                    "/article.php?json=1&article_id=" +
                        this.$route.params.id +
                        "&act=edit"
                )
                .then(function(result) {
                    this.articleid = result.body.article_id;
                    this.form = {
                        domain: result.body.domain,
                        title: result.body.title,
                        sort: result.body.sort
                    };
                    this.content = result.body.content;

                    this.editor.ready(
                        function() {
                            this.editor.setContent(this.content);
                        }.bind(this)
                    );

                    //this.content.replace({"img.stosz.com":"imgcn.stosz.com"});
                });
        } else {
            this.form = {
                domain: this.$route.params.domain,
                title: "",
                sort: ""
            };
        }
    },
    destroyed() {
        //取消
        this.editor.destroy();
    },
    methods: {
        handleBack() {
            if (this.form.domain != "") {
                this.$router.push({ path: "/sites/" + this.form.domain });
            }
        },
        onSubmit() {
            if (this.form.domain == "") {
                this.$message.error("域名不能为空");
                return false;
            }
            if (this.form.title == "") {
                this.$message.error("标题不能为空");
                return false;
            }
            var ue = UE.getEditor("editor");
            var formdata = new FormData();
            formdata.append("act", "save");
            formdata.append("article_id", this.articleid);
            formdata.append("domain", this.form.domain);
            formdata.append("title", this.form.title);
            formdata.append("sort", this.form.sort);
            var com = ue.getContent();
            if (com == "") {
                this.$message.error("内容不能为空");
                return false;
            }
            if (com.length > 0) {
                var arr = com.split(" ");
                var reg = /^src.*source$/;
                for (var i = 0; i < arr.length; i++) {
                    if (reg.test(arr[i])) {
                        var src = arr[i].match(/src="(\S*)"/)[1];
                        var poster =
                            ' poster="' + src + '?vframe/png/offset/5" ';
                        arr[i] = poster + arr[i];
                    }
                }
                var str = arr.join(" ");
                str = str.replace(/auto/g, "none");
                console.log(str)
                com = str;
            }
            formdata.append("content", com);
            this.$http.post("/article.php", formdata).then(function(result) {
                if (parseInt(result.body.ret) > 0) {
                    this.$message({ type: "success", message: "添加成功!" });
                    this.$router.push({
                        path: "/sites/show/" + this.form.domain
                    });
                } else {
                    this.$message({ type: "error", message: result.body.msg });
                }
            });
        },
        returnback() {
            if (this.form.domain) {
                this.$router.push({ path: "/sites/show/" + this.form.domain });
            } else {
                this.$router.push({ path: "/sites" });
            }
        }
    }
};
</script>