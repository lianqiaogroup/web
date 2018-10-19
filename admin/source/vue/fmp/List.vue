<template>
    <div id="page-comment-list">
        <div class="header-panel">
        	<div class="breadcrumb">
                <ul>
                    <li class="title">您的位置：</li>
                    <li class="item"><a href="/">首页</a></li>
                    <li class="item"><a href="javascript:;">广告</a></li>
                    <li class="item action"><a href="/#/fmp/list">提交纪录</a></li>
                </ul>
            </div>
            <!--  -->
            <!-- <div class="control">
                <div class="operations" style="display: flex;">
                    <div style="margin-right:8px;">
                        <router-link to="/fmp/add">
                            <el-button icon="plus" type="primary">创建广告</el-button>
                        </router-link>
                    </div>
                </div>
            </div> -->
	    </div>
		<div class="container aa">
			<el-table
			    :data="list"
			    border
			    tooltip-effect="dark"
                v-loading='table_loading'
                element-loading-text="拼命加载中"
                :row-class-name="tableRowClassName">
                <el-table-column prop="" label="" width="50"></el-table-column>
                <el-table-column prop="" label="时间" width="200">
                    <template scope="scope">
                        {{ scope.row.create_at || 'unkown' }}
                    </template>
                </el-table-column>
                <el-table-column class="ha" label="操作记录">
                    <template scope="scope">
                        {{ scope.row.name_cn || 'unkown' }}
                        提交成功，广告系列名称：
                        {{ scope.row.campaign_name || 'unkown' }}
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
        table_loading: false,
        list: [],
        page: 1,
        page_count: 0,
    };
  },
  mounted() {
    // get theme datas
    this.get();
  },
  methods: {
    get() {
    	if (this.table_loading) return false;
        this.table_loading = true;
        // 
        this.$http.get(`/FacebookMarketing.php?act=adList&p=${this.page}`).then(function(res) {
            this.table_loading = false;
            if (res.body.code==200) {
                this.list = res.body.adList;
                this.page_count = res.body.pageCount;
                this.page = parseInt(res.body.page);
            } else {
                this.list = [];
                this.page_count = 0;
                this.page = 1;
            }
        });
    }, 
    handleCurrentChange(val) {
		this.page = val;
      	this.get();
    },
    // 删除颜色
    tableRowClassName(row, index) {
		return row.is_del == 1 ? "del-row" : "";
    },
  }
};
</script>


<style>
	.aa .el-pagination {
		margin: 40px auto !important;
    }
    .aa .el-table .cell {
        line-height: 16px !important;
    }
</style>

