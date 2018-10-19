<template>
    <div id="page-currency-list">
        <div class="header-panel">
            <div class="control">
                <div class="operations">
                    <el-button @click="handleSync">货币同步 <i class='material-icons'>redo</i></el-button>
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
                >
                <el-table-column prop="currency_id" label="" width="34">
                    <template scope="scope"><div></div></template>
                </el-table-column>
			    <el-table-column prop="currency_id" label="ID" width="100"></el-table-column>
                <el-table-column prop="currency_title" label="货币名称"></el-table-column>
                <el-table-column prop="currency_code" label="货币简称"></el-table-column>
                <el-table-column prop="symbol_left" label="前置货币符号"></el-table-column>
                <el-table-column prop="currency_format" label="货币格式"></el-table-column>
                <el-table-column prop="symbol_right" label="后置货币符号"></el-table-column>
                <el-table-column prop="exchange_rate" label="兑换成USD的汇率"></el-table-column>                
                <el-table-column prop="update_time" label="更新时间" width="200"></el-table-column>
                <el-table-column label="操作"><template scope="scope"><el-button @click.native='showDialogEdit(scope.row)'>编辑</el-button></template></el-table-column>
			</el-table>
            <el-pagination
                small
                layout="prev, pager, next, jumper"
                :page-size="1"
                @current-change="handleCurrentChange"
                :total="page_count"
                >
            </el-pagination>
		</div>
        <el-dialog :visible.sync='dialogTableVisible' title='编辑'>            
            <el-form id='form' name='form' >
                <el-input value='currency_format' type='hidden'></el-input>
                <el-form-item label='货币名称'><br>
                    <el-input v-model='rCurrency.currency_title' disabled="disabled"></el-input>
                </el-form-item>
                <el-form-item label='货币简称'><br>
                    <el-input v-model='rCurrency.currency_code' disabled='disabled'></el-input>
                </el-form-item>
                <el-form-item label='前置货币符号'><br>
                    <el-input v-model='rCurrency.symbol_left' disabled='disabled'></el-input>
                </el-form-item>
                <el-form-item label='价格显示格式'><br>
                    <el-input v-model='rCurrency.currency_format'></el-input>
                </el-form-item>
                <el-form-item label='后置货币符号'><br>
                    <el-input v-model='rCurrency.symbol_right' disabled='disabled'></el-input>
                </el-form-item>
                <el-form-item label='兑换成USD的汇率'><br>
                    <el-input v-model='rCurrency.exchange_rate'></el-input>
                </el-form-item>
                <el-button type="primary" @click="submitForm()">保存</el-button>
            </el-form>
        </el-dialog>
	</div>
</template>

<script>
Vue.http.options.headers = {
  "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8"
};
export default {
  data() {
    return {
      multipleSelection: [],
      keyword: "",
      withDel: false,
      page: 1,
      table_loading: false,
      list: [],
      page_count: 0,
      dialogTableVisible: false,
      rCurrency: {
        currency_title: "",
        currency_code: "",
        symbol_left: "",
        currency_format: "",
        symbol_right: "",
        exchange_rate: ""
      }
    };
  },
  mounted() {
    this.get();
  },
  methods: {
    get() {
      this.table_loading = true;
      this.$http.get("/currency.php?p=" + this.page).then(function(res) {
        this.table_loading = false;
        this.list = res.body.goodsList;
        this.page_count = res.body.pageCount;
      });
    },
    handleSync() {
      this.table_loading = true;
      var self = this;
      this.$http.get("/currency.php?act=syncCurrency").then(res => {
        //   console.log(res);
        if (res.body.ret == "1") {
          self.$message({ message: res.body.msg, type: 'success' });
          self.get();
          self.table_loading = false;
        }else{
            self.$message(res.body.msg);
            self.table_loading = false;
        }
      });
    },
    handleCurrentChange(val) {
      this.page = val;
      this.get();
    },
    showDialogEdit(val) {
      this.dialogTableVisible = true;
      this.rCurrency = val;
    },
    submitForm() {
      console.log(this.rCurrency.currency_format);
      console.log(this.rCurrency.exchange_rate);
      if (this.rCurrency.currency_format == null) {
        this.$message("请输入价格显示格式");
        return false;
      }
      if (this.rCurrency.exchange_rate == null) {
        this.$message("请输入兑换成USD的汇率");
        return false;
      }
      this.table_loading = true;
      var formdata = new FormData();
      formdata.append("act", "currency_format");
      formdata.append("currency_id", this.rCurrency.currency_id);
      formdata.append("currency_format", this.rCurrency.currency_format);
      formdata.append("exchange_rate", this.rCurrency.exchange_rate);
      console.log(formdata);
      this.$http.post("/currency.php", formdata).then(res => {
        console.log(res.msg);
        console.log(res);
        this.$message(res.body.msg);
        this.get();
      });
    }
  }
};
</script>

