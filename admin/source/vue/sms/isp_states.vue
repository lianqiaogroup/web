<template>
	<div>
		<div class="header-panel">
			<div class="control">
				<router-link active-class="cur" to="/sms/isp_states">
		          <el-button >短信提供商列表</el-button>
		        </router-link>
		        <router-link active-class="cur" to="/sms/states_list">
		          <el-button>所有关联列表</el-button>
		        </router-link>
		        <a class="btn-add" href="sms.php?act=isp_edit" target="_blank">增加</a>
			</div>
		</div>
		<div class="search_">
			<el-form :inline="true" :model="formInline" class="demo-form-inline">
			  <el-form-item label="服务提供商">
			    <el-input v-model="formInline.ispname"></el-input>
			  </el-form-item>
			  <el-form-item label="状态">
			    <el-select v-model="check_status">
		            <el-option
		              v-for="item in status_sel"
		              :key="item.check_status"
		              :label="item.s_status"
		              :value="item.s_id">
		            </el-option>
		          </el-select>
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
		      prop="status"
		      label="状态">
		      <template scope="scope">
		      	<span v-if="scope.row.status == 0">关闭</span>
		      	<span v-if="scope.row.status == 1">开启</span>
		      </template>
		    </el-table-column>
		    <el-table-column
		    	prop="last_modify"
		    	label="添加时间">
		    </el-table-column>
		    <el-table-column
		      label="操作">
		      <template scope="scope">
		        <a class="btn-txt" :href="'/sms.php?id=' + scope.row.id +'&act=isp_edit'" target="_blank">编辑</a>
		        <a class="btn-txt" href="javascript:void(0)" @click="isp_relid(scope.row.id)">关联详情</a>
                <a class="btn-txt" href="javascript:void(0)" @click="deleteIspId(scope.row.id,scope.row.ispname)">删除</a>
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
	        currentRoute:this.$route.path,
	        formInline:{
	        	ispname:''
	        },
	        check_status:'',
	        status_sel:[{
	        	s_status:'全部',
	        	s_id:"2"
	        },{
	        	s_status:'开启',
	        	s_id:"1"
	        },{
	        	s_status:'关闭',
	        	s_id:"0"
	        }]
	      }
	    },
	    mounted(){
	    	this.getisp_list();
	    	console.log(this.$route.path)
	    },
	    methods:{
	    	getisp_list(){
	    		this.$http.get('/sms.php?act=isp_list').then(function(result){
	    			var body_list = JSON.parse(result.bodyText);
	    			this.tableData = body_list.list;
	    			//console.log(body_list)
	    		})
	    		
	    	},
	    	isp_relid(param){
	    		this.$router.push({path:'/sms/states_list/'+ param});
	    	},
	    	deleteIspId(id,ispname){
	    		this.$confirm('此操作将删除该记录, 是否继续?', '提示', {
		          confirmButtonText: '确定',
		          cancelButtonText: '取消',
		          type: 'warning'
		        }).then(() => {
		            this.$http.post('/sms.php?act=delete_isp',{id:id}).then(function(result){
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
	    	onSubmit:function(){
	    		var basurl = 'sms.php?act=isp_list';
	    			if(this.formInline.ispname){
	    				basurl += '&ispname=' + this.formInline.ispname;
	    			}
	    			if(this.check_status){
	    				if(this.check_status != "2"){
		    				basurl += '&status=' + this.check_status;
		    			}
	    			}
	    			
	    		this.$http.get(basurl).then(function(result){
	    			var datas = result.body;
	    			this.tableData = datas.list;
	    		})
	    	}
	    }
	}
</script>