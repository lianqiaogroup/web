<template>
  <div id="olog">
    <div class="control">
      <el-form :inline="true" :model="goodData" ref="goodData" label-width="80px" class="demo-form-inline demo-ruleForm">
        <el-form-item label="操作时间" prop="startime">
           <el-date-picker
            v-model="goodData.startime"
            format="yyyy-MM-dd HH:mm:ss"
            type="datetime"
            placeholder="开始时间"
            align="right"
            :picker-options="pickerOptions1" @change="startime">
          </el-date-picker>
        </el-form-item>
        <el-form-item>
          <el-date-picker
            v-model="goodData.endtime"
            format="yyyy-MM-dd HH:mm:ss"
            type="datetime"
            placeholder="结束时间"
            align="right"
            :picker-options="pickerOptions1" @change="endtime">
          </el-date-picker>
        </el-form-item>
        <el-form-item label="操作人姓名拼音" label-width="110px">
          <el-input v-model="goodData.name"></el-input>
        </el-form-item>
        <el-form-item label="产品id" prop="product">
          <el-input v-model="goodData.product"></el-input>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="onSubmit('goodData')">查询</el-button>
        </el-form-item>
      </el-form>
    </div>
    <div class="container">
      <el-table
        :data="tableData"
        border
        style="width: 100%">
        <el-table-column
          prop="product_id"
          width="130"
          label="产品id">
        </el-table-column>
        <el-table-column
          prop="act_time"
          label="日期">
        </el-table-column>
        <el-table-column
          prop="act_namecn"
          label="姓名">
        </el-table-column>
        <el-table-column
          prop="act_desc"
          label="动作">
          <template scope="scope">
              <el-row class="scope-time" v-html="scope.row.act_desc"></el-row>
          </template>
        </el-table-column>
      </el-table>
    </div>
    <div class="block">
      <el-pagination
        v-if="goodData.count != 0"
        @current-change="handleCurrentChange"
        :page-size="25"
        layout="prev, pager, next, jumper,slot"
        :total="goodData.count">
      </el-pagination>
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
        pickerOptions1:{
          shortcuts: [{
            text: '今天',
            onClick(picker) {
              picker.$emit('pick', new Date());
            }
          }, {
            text: '昨天',
            onClick(picker) {
              const date = new Date();
              date.setTime(date.getTime() - 3600 * 1000 * 24);
              picker.$emit('pick', date);
            }
          }, {
            text: '一周前',
            onClick(picker) {
              const date = new Date();
              date.setTime(date.getTime() - 3600 * 1000 * 24 * 7);
              picker.$emit('pick', date);
            }
          }]
        },
        tableData: [],
        goodData:{
          startime: "",
          endtime: "",
          name:"",
          product:"",
          p:0,
          count:0
        }
      }
    },
    mounted(){
      var thattime = this.getNowFormatDate();
      this.startime(thattime);
      this.postdata();
    },
    methods:{
      postdata(page){
        var pages = page ? page : this.goodData.p;
        var posturl = '/p_act_log.php?act=query';
                  posturl += '&start_time=' + this.goodData.startime;
                  posturl += '&end_time=' + this.goodData.endtime;
                  posturl += '&act_loginid=' + this.goodData.name;
                  posturl += '&product_id=' + this.goodData.product;
                  posturl += '&p=' + pages;
              this.$http({
                  method:'POST',
                  url:posturl
              }).then(function(response){
                  var datas = response.body;
                  this.tableData = datas.goodsList;
                  this.goodData.count = datas.count;
              },function(error){
                  console.log(error)
              })
      },
      onSubmit(formName){
        var that = this;
        that.$refs[formName].validate((valid) => {
            if (valid) {
                that.postdata();
            } else {
              console.log('error submit!!');
              return false;
            }
        });
      },
      startime(val){
        this.goodData.startime = val;
      },
      endtime(val){
        this.goodData.endtime = val;
      },
      handleCurrentChange(val){
        this.postdata(val);
      },
      getNowFormatDate() {
        var date = new Date();
        var seperator1 = "-";
        var seperator2 = "00:00:00";
        var month = date.getMonth() + 1;
        var strDate = date.getDate();
        if (month >= 1 && month <= 9) {
            month = "0" + month;
        }
        if (strDate >= 0 && strDate <= 9) {
            strDate = "0" + strDate;
        }
        var currentdate = date.getFullYear() + seperator1 + month + seperator1 + strDate + " " + seperator2;
        return currentdate;
    }
    }
  }
</script>