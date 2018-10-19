<template>
  <div class="article-list">
      <div class="article-list-header">
          <el-input
            placeholder="请输入域名"
            icon="search"
            v-model="input2"
            :on-icon-click="handleIconClick">
          </el-input>
          <div class="article-list-btn">
              <router-link to="/article_new">
                  <el-button type="primary" >新增文章</el-button>
              </router-link>
          </div>
      </div>
      <div class="article-list-tatle">
          <el-table
            :data="tableData"
            style="width: 100%">
            <el-table-column
              prop="domain"
              label="域名"
              width="180">
            </el-table-column>
            <el-table-column
              prop=""
              label="排序"
              width="120">
              <template scope="scope">
                <el-input v-model="scope.row.sort"></el-input>
              </template>
            </el-table-column>
            <el-table-column
              prop="article_id"
              label="id">
            </el-table-column>
            <el-table-column
              prop="title"
              label="标题">
            </el-table-column>
            <el-table-column
              prop="add_time"
              label="添加时间">
            </el-table-column>
            <el-table-column
              prop=""
              label="操作">
              <template scope="scope">
                  <div v-if="scope.row.is_del == 0">
                    <a class="btn-txt" href="javascript:void(0)" @click="handeditClick(scope.row.article_id)">编辑</a>
                    <a class="btn-txt" href="javascript:void(0)" @click="handdelClick(scope.row.article_id,1)">删除</a>
                  </div>
                  <div v-if=" scope.row.is_del == 1 ">
                    <a class="btn-txt" href="javascript:void(0)" @click="handreseClick(scope.row.article_id,0)">恢复</a>
                  </div>
              </template>
            </el-table-column>

          </el-table>
      </div>
      <template>
        <div class="sortbtn">
          <el-button type="primary" @click="handsort()">更新排序</el-button>
        </div>
      </template>
      <template>
        <div class="block">
          <el-pagination
            @size-change="handleSizeChange"
            @current-change="handleCurrentChange"
            :current-page.sync="page"
            :page-size="15"
            layout="total, prev, pager, next"
            :total="page_total">
          </el-pagination>
        </div>
      </template>
  </div>
</template>
<script>
Vue.http.options.emulateJSON = true;
export default {
  data() {
    return {
      input2: ''
      ,tableData: []
      ,page:1
      ,page_total:0

    }
  },
  mounted() {
    if(this.$route.params.domain){
        this.input2 = this.$route.params.domain;
        this.getlist();
    }
  },
  methods: {
    handleIconClick(ev) {
      this.getlist();
    },
    getlist(){
        this.$http.get('/article.php?domain='+ this.input2 + '&p=' + this.page).then(function(result){
        this.tableData = result.body.goodsList;
        this.page = parseInt(result.body.page);
        this.page_total = result.body.count;
      })
    },
    handeditClick(param){
      this.$router.push({path:'/article_new/'+param});
    },
    handreseClick(param,id){
        this.$http.post('/article.php?',{article_id:param,is_del:id,act:"del"}).then(function(result){
          if(result.body.ret == 1){
              this.$message({
                type: 'success',
                message: '恢复成功!'
              });
              this.tableData.map(function(item,index){
                if(item.article_id == param){
                    item.is_del = id;
                }
              })
          }
        })
    },
    handdelClick(param,id){
        this.$confirm('此操作将删除该记录, 是否继续?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
            this.$http.post('/article.php?',{article_id:param,is_del:id,act:"del"}).then(function(result){
            if(result.body.ret == 1){
                this.tableData.map(function(item,index){
                  if(item.article_id == param){
                      item.is_del = id;
                  }
                });

            }
          })
          this.$message({
            type: 'success',
            message: '删除成功!'
          });
        }).catch(() => {        
        });
    },
    handleSizeChange(val){
      this.getlist();
    },
    handleCurrentChange(val){
      this.getlist();
    },
    handsort(){
      var article_ids = [],sorts = [];
      this.tableData.map(function(item,index){
          article_ids.push(item.article_id);
          sorts.push(item.sort);
      });
      this.$http.post('/article.php?',{article_id:article_ids,sort:sorts,act:"sort"}).then(function(result){
          if(result.body.ret){
              this.$message({
              type: 'success',
                message: '删除成功!'
              });
              console.log(result.body)
          }
      })
    }
  }
}
</script>