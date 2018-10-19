<template>
  <div id="olog" v-loading="loading">
    <div class="control tag">
           
      <el-form :inline="true" :model="goodData" ref="goodData" label-width="80px" class="demo-form-inline demo-ruleForm">
        <el-row>  
            <el-col :span="6">
                <el-form-item  :rules="
                    [
                    { required: false, message: '请选择分类'}
                    ]">
                    <el-cascader
                        :change-on-select="true"
                        :options="options.type"
                        v-model="selected.type"
                        placeholder="分类"
                        style="width:100%;">
                    </el-cascader>
                </el-form-item>
            </el-col>
            <el-col :span="6">
                <el-form-item  :rules="
                    [
                    { required: false, message: '请选择类型'}
                    ]">
                    <el-select
                        style="width:100%;"
                        v-model="selected.tag"
                        placeholder="类型">
                            <el-option
                                v-for="tag in options3.tags"
                                :key="tag.id"
                                :label="tag.type_name"
                                :value="tag.id">
                            </el-option>
                    </el-select>
                </el-form-item>
            </el-col>
            <el-col :span="6">    
                <el-form-item  :rules="
                    [
                    { required: false, message: '类型'}
                    ]">
                    <el-select v-model="format" placeholder="格式">
                        <el-option
                        v-for="item in options3"
                        :key="item.value"
                        :label="item.label"
                        :value="item.value">
                        </el-option>
                    </el-select>
                </el-form-item>
            </el-col> 
            <el-col :span="6">   
                <el-form-item  :rules="
                    [
                    { required: false, message: '请选择类型'}
                    ]">
                    <el-select filterable multiple :multiple-limit="10"  v-model="tag" placeholder="标签">
                        <el-option
                        v-for="item in tagList"
                        :key="item.tag_id"
                        :label="item.tag_name"
                        :value="item.tag_id">
                        </el-option>
                    </el-select>
                </el-form-item>
            </el-col>
            <el-col :span="6">    
                <el-form-item  :rules="
                    [
                    { required: false, message: '请选择类型'}
                    ]">
                    <el-select filterable v-model="member" placeholder="上传人员">
                        <el-option
                        v-for="item in memberList"
                        :key="item.label"
                        :label="item.label"
                        :value="item.label">
                        </el-option>
                    </el-select>
                </el-form-item>
            </el-col>
            <el-col :span="6">    
                <el-form-item  prop="startime">
                    <el-date-picker
                        v-model="goodData.startime"
                        format="yyyy-MM-dd HH:mm:ss"
                        type="datetime"
                        placeholder="开始时间"
                        align="right"
                        :picker-options="pickerOptions1" @change="startime">
                    </el-date-picker>
                </el-form-item>
            </el-col>
            <el-col :span="6">   
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
            </el-col>
            
            </el-form>
            <el-form>
            <el-form-item >
                <el-button @click="postdata()"  type="primary" >搜索</el-button>
                <el-button class="clearSearch" @click="reset"  type="" >重置</el-button>
                <router-link to="/material/updata">
                    <el-button class="fr"  type="primary" >上传</el-button>
                </router-link>
            </el-form-item>
      </el-form>   
        </el-row>    
    </div>
    <div class="container">
      <el-table
        :data="tableData"
        border
        style="width: 100%">
        <el-table-column prop="" align="center" width="150px" label="素材">
            <template scope="scope">
                <img width="100%" @click="preview(scope.row.thumb,2)" v-if="scope.row.format == 'MP4'" :src=`${scope.row.thumb}?x-oss-process=video/snapshot,t_7000,f_jpg,w_800,h_600,m_fast`>
                <img width="100%" v-else-if="scope.row.format == 'PSD'" @click="preview(scope.row.thumb,3)" src="http://front-material.oss-cn-shenzhen.aliyuncs.com/origin/1533892440029803.png" alt="">
                <img width="100%" @click="preview(scope.row.thumb+'?x-oss-process=image/resize,w_600,h_600/watermark,type_d3F5LXplbmhlaQ,size_30,text_5biD6LC36bif6K6-6K6h5Zu-,fill_1,rotate_45,color_CCCCCC,shadow_50,t_100,g_se,x_10,y_10',1)" v-else :src=`${scope.row.thumb}?x-oss-process=image/resize,w_300,h_300/watermark,type_d3F5LXplbmhlaQ,size_30,text_5biD6LC36bif6K6-6K6h5Zu-,color_CCCCCC,shadow_50,t_100,g_se,x_10,y_10`>
            </template>
        </el-table-column>
        <el-table-column
           align="center"
          prop="size"
          label="素材大小">
        </el-table-column>
        <el-table-column
           align="center"
          prop="format"
          label="素材格式">
        </el-table-column>
        <el-table-column
          align="center"
          prop="add_time"
          label="上传时间">
        </el-table-column>
        <el-table-column
          align="center"
          prop="ad_member"
          label="上传人员">
        </el-table-column>
        <el-table-column prop=""  align="center" label="操作" width="100px">
            <template scope="scope">
                <el-button @click="download(scope.row.thumb)" type="text">下载</el-button>
                <br><el-button v-if="administrator || uid == 279" @click="delet(scope.row.id,scope.$index)" type="text">删除</el-button>
            </template>
        </el-table-column>
      </el-table>
    </div>
    <el-dialog title="预览" class="preview" center="true" :visible.sync="dialogTableVisible">
        <img v-if="type == 1" :src="imgSrc" alt="">
        <video height="600px" width="100%" v-else-if="type ===2" controls :src="imgSrc"></video>
        <img v-else src="http://front-material.oss-cn-shenzhen.aliyuncs.com/origin/1533892440029803.png" alt="">
    </el-dialog>
    <div class="block">
      <el-pagination
        v-if="count != 0"
        @current-change="handleCurrentChange"
        @size-change="handleSizeChange"
        :page-size="pageSize"
        :page-sizes="[10, 20, 30, 40]"
        :current-page='p'
        layout="total, sizes, prev, pager, next, jumper"
        :total="count">
      </el-pagination>
    </div>
  </div>
</template>
<script>
import MaterialAPI from '../../Api/Material';
import TagAPI from "../../Api/Tag";

Vue.http.options.headers = {
    "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8"
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
            options3: [{
                value: 'GIF',
                label: 'gif'
                }, {
                value: 'VIDEO',
                label: '视频'
                }, {
                value: 'IMAGE',
                label: '图片'
                }, {
                value: 'PSD',
                label: 'PSD'
                }
            ],
            format: '',
            //选择项
			options: {
				// 分类选择项
				type: [{
					value: '',
					label: '',
					children: []
				}],
                // 分类（同层级）
                typeInline: [],
				// 标签的选择项
				tags: [],
			},
			// 已选择
			selected: {
				type: [],
                typeIncludeChild: [],
				tag: [],
			},
			// 
			form: {
				// 分类
				classification: [],
				// 类别
				type: [],
			},
            tableData: [],
            goodData: {},
            count:1,
            p:1,
            pageSize:10,
            dialogTableVisible:false,
            imgSrc:'',
            type:'',
            loading:false,
            tagList:'',
            tag:[],
            member:'',
            memberList:[],
            // timeList: [new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() ), new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() , 23, 59,59)],
            timeList:'',
            goodData:{
                startime: "",
                endtime: ""
            },
        };
    },
    created(){
        // 获取分类
			MaterialAPI.getType().then(res => {
				if (res.ret==1) {
					this.options.type = res.data.item.children;
					// 递归处理字段的差异性
					this.convertFields(this.options.type);

                    // 多级菜单转换为一级
                    this.convertTreeToList(this.options.type)
				} else {
					this.$message.error(res.msg);
				}
			})

			// 获取类型
			MaterialAPI.getTags().then(res => {
                // console.log(res.typeList)
                this.options3.tags=res.typeList;
				// this.options.tags = res.data.map(row=>{
				// 	return {
				// 		value: row.id,
				// 		label: row.tagname
				// 	}
				// })
            })
            //获取标签
            TagAPI.tagList().then(res =>{
                if(res.code == 200){
                    this.tagList = res.tagsList;
                }else{
                    this.$message.error(res.msg);
                }
            })
    },
    mounted() {
        this.postdata();
        this.getmemberList();
    },
    computed:{
        uid(){
            return this.$store.state.auth.uid ;
        },
        administrator() {
            return this.$store.state.auth.is_admin;
        },
    },
    methods: {
        startime(val){
        this.goodData.startime = val;
      },
      endtime(val){
        this.goodData.endtime = val;
      },
        reset(){
            this.selected.type=[];
            this.selected.tag='';
            this.format='';
            this.member = '';
            this.tag = [];
            // this.timeList = [this.startTime,this.endTime];
            this.timeList ='';
            this.goodData.startime = '';
            this.goodData.endtime = '';
        },
        delet(id,index){
            this.$confirm("是否确认删除该素材？", "删除", {
                confirmButtonText: "确认",
                cancelButtonText: "取消",
                type: "warning"
            }).then(() => {
                MaterialAPI.delet({id}).then((res)=>{
                    
                    if(res.code == 200){
                        // this.postdata();
                        
                        this.tableData.splice(index,1);
                        if(this.tableData.length ==1 && this.p>1){
                            this.postdata({p:this.p--})
                        }else{
                            this.postdata({p:this.p})
                        }
                        this.$message({
                            type: "success",
                            message: "删除成功!"
                        });
                    };
                   
                });
            }).catch(() => {
                    this.$message({
                        type: "info",
                        message: "已取消删除"
                    });
                });
        },
        download(url){
            window.location.href =`/source.php?url=${url}`;
            // window.location.href =`http://www.shopadmin.com/source.php?url=${url}`;
        },
        //获取人员列表
        getmemberList(){
            MaterialAPI.getMember().then((res)=>{
                if(res.code == 200){
                    var arr = [];
                    for(var i = 0;i<res.authorList.length;i++){
                        var obj = {};
                        obj.label= res.authorList[i];
                        arr.push(obj)
                    }
                    this.memberList = arr;
                }
            });
        },
        postdata(obj) {
            var obj = obj?obj :{};
            obj.p = obj.p ? obj.p : 1;
            this.p = obj.p;
            obj.limit = obj.pagesize || this.pageSize;
            if(this.goodData.startime){
                obj.beginDate = this.goodData.startime
            }
            if(this.goodData.endtime){
                obj.endDate = this.goodData.endtime;
            };
            

            let mtype = this.selected.type[this.selected.type.length-1];
            let tag = this.selected.tag;
            let format = this.format;
            let member = this.member;
            if(mtype){obj.product_category_id = parseInt(mtype)};
            if(tag){obj.resource_type_id = parseInt(tag)};
            if(format){obj.format = format};
            if(member){obj.ad_member = member;}
            if(this.tag.length>0){obj.tag_ids = this.tag.join()};

            if (mtype) {
                this.selected.typeIncludeChild = [mtype];
                this.getChildrenId(mtype);
                obj.product_category_id = this.selected.typeIncludeChild.join(',');
            }

            this.loading = true;
            MaterialAPI.getList(obj).then((res)=>{
                this.loading = false;
                if(res.code ==200){
                    this.tableData = res.resourceList;
                    this.count = res.count;
                }
            });
        },
        preview(url,type){
            this.dialogTableVisible = true;
            this.imgSrc = url;
            this.type = type;
        },
        handleSizeChange(val) {

            this.pageSize = val;
            this.postdata({pagesize:val});
        },
        handleCurrentChange(val) {
            var obj = {p:val};
            this.postdata(obj);
        },
        convertFields(source) {
			source.map(row => {
				row['value'] = row.id;
				row['label'] = row.name;
				if (row['children']) {
					this.convertFields(row['children']);
				} ;
			})
		},
        /*
            将层级菜单转换为1级列表，取必要的字段（id, 上级id, 分类名字)
        */
        convertTreeToList(source) {
            source.map(row => {
                if (row.id) {
                    this.options.typeInline.push({
                        id: row.id,
                        parentId: row.parentId,
                        label: row.label
                    });
                }
                if (row.children && row.children.length>0) {
                    this.convertTreeToList(row.children)
                }
            })
        },
        getChildrenId(parentId) {
            this.options.typeInline.map(row=>{
                if (row.parentId==parentId) {
                    this.selected.typeIncludeChild.push(row.id);
                    this.getChildrenId(row.id);
                }
            })
        }
    }
};
</script>

<style scoped>
.fr {
    float: right;
}
#olog .container .el-table img{
    margin-top:10px;
    margin-bottom:10px;
    width:100%;
}
.clearSearch{
    color: #666;
    background-color: rgba(221, 221, 221, 0.94);
    border-color: rgba(221, 221, 221, 0.94);
}

</style>
<style>

.preview .el-dialog__body{
    text-align:center;
}
.tag .el-form--inline .el-form-item{
    width:100%;
}
.tag .el-form--inline .el-form-item__content{
    width:95% ;
}
.tag .el-select{
    width:100%;
}
.tag .el-date-editor.el-input{
    width:100%;
}
.tag .el-button{
    width:70px;
}
</style>

