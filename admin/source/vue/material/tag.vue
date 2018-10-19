<template>
    <div id="olog" v-loading="loading">
        <div class="control"></div>
        <div class="container">
            <p>标签总个数:{{count}}</p>
            <div class="topbox">
                标签名称（双击编辑）
            </div>
            <div class="box">
                <el-tag :key="item"  v-for="item in dynamicTags" closable :disable-transitions="true" 
                    v-on:dblclick.native="clic(item.tag_name,item.tag_id)"
                    @close="handleClose(item.tag_id)">
                    {{item.tag_name}}
                </el-tag>
                <el-input class="input-new-tag" v-if="inputVisible" v-model="inputValue" ref="saveTagInput" size="small" @keyup.enter.native="handleInputConfirm" @blur="handleInputConfirm">
                </el-input>
                <el-button v-else class="button-new-tag" size="small" @click="showInput">+ 新增</el-button>
            </div>
            <el-dialog title="编辑" class="preview" center="true" :visible.sync="dialogTableVisible">
                <el-form>
                    <el-form-item>
                        <el-input v-model="input" placeholder="请输入内容"></el-input>
                    </el-form-item>
                    <el-form-item class="cente">
                        <el-button @click="dialogTableVisible=false" >取消</el-button>
                        <el-button type="primary" @click="doEdit()">确认</el-button>
                    </el-form-item>
                </el-form>
            </el-dialog>
        </div>
    </div>
</template>
<script>
import TagAPI from "../../Api/Tag";

export default {
    data() {
        return {
            loading: false,
            dynamicTags: [],
            inputVisible: false,
            inputValue: "",
            dialogTableVisible: false,
            input: "",
            count:'',
            tag_id:''
        };
    },
    created() {
        this.getList();
    },
    mounted() {},
    methods: {
        getList(){
            TagAPI.tagList().then(res => {
                if(res.code == 200){
                    this.count = res.count;
                    this.dynamicTags = res.tagsList;
                }else{
                    this.$message.error(res.msg);
                }
            });
        },
        doEdit() {
            if (!this.input) {
                this.$message.error("标签不能为空！");
                return false;
            }
            if (this.input.trim().length > 10) {
                this.$message.error("标签最多只能输入10个字符");
                return false;
            }
            var obj = {tag_name:this.input,tag_id:this.tag_id}
            TagAPI.editTag(obj).then(res => {
                if(res.code == 200){
                    var id = this.findIndex(this.tag_id);
                    this.dynamicTags[id] = obj;
                    this.$message({
                        message: res.msg,
                        type: 'success'
                    });
                }else{
                    this.$message.error(res.msg);
                }
                this.dialogTableVisible = false;
            });
        },
        clic(tag_name,tag_id) {
            this.dialogTableVisible = true;
            this.input = tag_name;
            this.tag_id = tag_id;
        },
        findIndex(tag){
            for(var i = 0;i<this.dynamicTags.length;i++){
                if(this.dynamicTags[i].tag_id == tag){
                    return i;
                }
            };
        },
        handleClose(tag) {
            this.$confirm("是否确认删除该标签？", "删除", {
                confirmButtonText: "确认",
                cancelButtonText: "取消",
                type: "warning"
            })
                .then(() => {
                    TagAPI.deleteTag({tag_id:tag}).then(res => {
                       if(res.code == 200){
                           this.$message({
                            type: "success",
                            message: "删除成功!"
                        });
                        var id = this.findIndex(tag);
                        this.count--;
                        this.dynamicTags.splice(id,1);
                       }
                    });
                })
                .catch(() => {
                    this.$message({
                        type: "info",
                        message: "已取消删除"
                    });
                });
        },

        showInput() {
            this.inputVisible = true;
            this.$nextTick(_ => {
                this.$refs.saveTagInput.$refs.input.focus();
            });
        },
        //增加标签
        handleInputConfirm() {
            let inputValue = this.inputValue;
            var tag_name = inputValue.trim();
            if(tag_name &&tag_name.length<=10){
                TagAPI.addTag({tag_name}).then(res => {
                    if(res.code == 200){
                        this.count++;
                        var obj = {tag_name:res.tagInfo.tag_name,tag_id:res.tagInfo.tag_id};
                        this.dynamicTags.unshift(obj);
                        this.$message({
                            message: res.msg,
                            type: 'success'
                        });
                    }else{
                        this.$message.error(res.msg);
                    }

                });
            }else if(tag_name.length>10){
                this.$message.error('标签只能输入10个字符！');
            }
            this.inputVisible = false;
            this.inputValue = "";
        }
    }
};
</script>

<style>
.el-tag + .el-tag {
    margin-left: 10px;
}
.button-new-tag {
    margin-left: 10px;
    height: 32px;
    line-height: 30px;
    padding-top: 0;
    padding-bottom: 0;
}
.input-new-tag {
    width: 90px;
    margin-left: 10px;
    vertical-align: bottom;
}
.container .el-tag {
    background-color: rgba(64,158,255,.1);
    color:#409eff;
    height: 40px;
    line-height: 40px;
    margin-top:10px;
    border: 1px solid rgba(64,158,255,.2);
}
</style>

<style scoped>
.box {
    border: 1px solid #ccc;
    min-height: 500px;
    padding: 12px;
}
.topbox {
    border: 1px solid #ccc;
    height: 40px;
    line-height: 40px;
    text-align: center;
    border-bottom: 0;
}
.box .el-button {
    height: 40px;
}
.cente {
    text-align: right;
}
</style>



