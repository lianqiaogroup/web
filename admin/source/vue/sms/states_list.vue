<template>
	<div>
		<div class="header-panel">
			<div class="control">
				<router-link active-class="cur" to="/sms/isp_states">
		          <el-button>短信提供商列表</el-button>
		        </router-link>
		        <router-link active-class="cur" to="/sms/states_list">
		          <el-button>所有关联列表</el-button>
		        </router-link>
		        <a class="btn-add" href="sms.php?act=isp_state_edit" target="_blank">增加</a>
			</div>
		</div>
		<div class="search_">
			<el-form :inline="true" :model="formInline" class="demo-form-inline">
			  <el-form-item label="服务提供商">
			    <el-input v-model="formInline.ispname"></el-input>
			  </el-form-item>
			  <el-form-item label="国家地区">
			    <el-input v-model="formInline.nation"></el-input>
			  </el-form-item>
			  <el-form-item label="模版">
			    <el-input v-model="formInline.styles"></el-input>
			  </el-form-item>
			  <el-form-item label="国家区号">
			    <el-input v-model="formInline.ncode"></el-input>
			  </el-form-item>
			  <el-form-item label="前缀">
			    <el-input v-model="formInline.prefix"></el-input>
			  </el-form-item>
			  <el-form-item>
			    <el-button type="primary" @click="onSubmit">查询</el-button>
			  </el-form-item>
			</el-form>
		</div>
		<div class="isp_table">
		  <el-table
		    :data="tableData"
		    border
		    style="width: 100%">
		    <el-table-column
		      prop="id"
		      label="id">
		    </el-table-column>
		    <el-table-column
		      prop="ispname"
		      label="服务提供商">
		    </el-table-column>
		    <el-table-column
		      prop="title"
		      label="国家地区">
		    </el-table-column>
		    <el-table-column
		      prop="styles"
		      label="模版">
		    </el-table-column>
		    <el-table-column
		    	prop="ncode"
		    	label="国家区号">
		    </el-table-column>
		    <el-table-column
		    	prop="prefix"
		    	label="前缀">
		    </el-table-column>
		    <el-table-column
		    	prop="last_modify"
		    	label="添加时间">
		    </el-table-column>
		    <el-table-column
		      label="操作">
		      <template scope="scope">
		        <a class="btn-txt" :href="'/sms.php?id=' + scope.row.id +'&act=isp_state_edit'" target="_blank">编辑</a>
                <a class="btn-txt" href="javascript:void(0)" @click="handdelClick(scope.row.id)">删除</a>
		      </template>
		    </el-table-column>
		  </el-table>
		  </div>
	</div>
</template>
<script>
	Vue.http.options.emulateJSON = true;
	 export default {
	    data() {
	      return {
	        tableData: [],
	        formInline:{
	        	ispname:'',
	        	nation:'',
	        	styles:'',
	        	ncode:'',
	        	prefix:''
	        }
	      }
	    },
	    mounted(){
	    	if(this.$route.params.id){
	    		this.getThatlist();
	    	}else{
    			this.getisp_list();
    		}
	    },
	    methods:{
	    	getisp_list(){
	    		this.$http.get('/sms.php?act=isp_state_list').then(function(result){
	    			var body_list = JSON.parse(result.bodyText);
	    			this.tableData = body_list.list;
	    		})
	    	},
	    	getThatlist(){
	    		var url = '/sms.php?id='+ this.$route.params.id +'&act=isp_rel';
	    		console.log(url)
	    		this.$http.get(url).then(function(result){
	    			var body_list = result.body;
	    			this.formInline.ispname = body_list.ispname;
	    			this.tableData = body_list.list;
	    		})
	    	},
	    	handeditClick(param){
	    		//this.$router.push({path:'/sms/states_edit/'+ param});
	    		this.$router.push({path:'/sms.php?id='+ param +'&act=isp_state_edit'});
	    	},
	    	handdelClick(id){
	    		this.$confirm('此操作将删除该记录, 是否继续?', '提示', {
		          confirmButtonText: '确定',
		          cancelButtonText: '取消',
		          type: 'warning'
		        }).then(() => {
		            this.$http.post('/sms.php?act=delete_isp_state',{id:id}).then(function(result){
			            if(result.body.ret == 1){
			                this.getisp_list();
			                this.$message({
					            type: 'success',
					            message: '删除成功!'
					        });
			            }else{
			            	this.$message({
					            type: 'error',
					            message: result.body.msg
					        });
			            }
			          })
			          
		        }).catch(() => {

		        });
	    	},
	    	onSubmit(){
	    		var baseurl = 'sms.php?act=isp_state_list';
	    			baseurl +='&ispname='+ this.formInline.ispname;
                    baseurl +='&nation='+ this.formInline.nation;
                    baseurl +='&styles='+ this.formInline.styles;
                    baseurl +='&ncode='+ this.formInline.ncode;
                    baseurl +='&prefix='+ this.formInline.prefix;
	    		this.$http.get(baseurl).then(function(result){
	    			var datas = result.body;
	    			this.tableData = datas.list;
	    		})
	    	}
	    }
	}
</script>