<template>
	<div class="page-container-boxer" :loading="submitLoading">
		<div class="header-panel">
			<div class="control">
				<div class="operations">
					<el-button @click="handleSave" type="primary">提交</el-button>
					<el-button @click="$router.back(-1)">返回</el-button>
				</div>
			</div>
		</div>
		<div class="container">
			<el-form ref="form" :model="form" label-width="80px">
				<el-form-item label="分类" :rules="
				[
			      { required: true, message: '请选择分类'}
			    ]">
					<el-cascader
                        :change-on-select="true"
						:options="options.type"
						v-model="selected.type"
						style="width:50%;">
					</el-cascader>
				</el-form-item>
				<el-form-item label="类型" :rules="
				[
			      { required: true, message: '请选择类型'}
			    ]">
					<el-select
						style="width:50%;"
						v-model="selected.tag"
						placeholder="请选择">
							<el-option
								v-for="tag in options.tags"
								:key="tag.id"
								:label="tag.type_name"
								:value="tag.id">
							</el-option>
					</el-select>
				</el-form-item>
                 <el-form-item label="标签" :rules="
                [
			      { required: false, message: '请选择类型'}
			    ]">
                <el-select filterable multiple :multiple-limit="10" v-model="tag" placeholder="请选择">
                    <el-option
                    v-for="item in tagList"
                    :key="item.tag_id"
                    :label="item.tag_name"
                    :value="item.tag_id">
                    </el-option>
                </el-select>
            </el-form-item>
			</el-form>
			<div class="uploadDiv">
				<cus-upload
					multiple
				    class="upload-demo"
				    name="file"
				    ref="uploadComponent"
					accept=".jpg,.jpeg,.png,.gif,.bmp,.mp4,.JPG,.JPEG,.GIF,.BMP,.MP4,.PSD,.psd"
				    :data="updata"
				    :limit="20"
				    :action="updata.host"
				    :on-change="handleChange"
				    :before-upload="handleBeforeUpload"
				    :on-progress="onProgress"
				    :on-error="onError"
				    :on-success="handleSuccess"
				    :on-remove="handleRemove"
				    :file-list="fileList">
				    <el-button size="small" type="primary">点击上传</el-button>
				</cus-upload>
			</div>
		</div>
  </div>
</template>
<style scoped>
	.page-container-boxer {
		position: relative;
	}
	.page-container-boxer .control {
		height: 100px;
	}
	.uploadDiv{
		padding-left: 80px;
	}
</style>
<script>

import Upload from '../global/upload/index.js';
import TagAPI from "../../Api/Tag";

import MaterialAPI from '../../Api/Material';

	export default {
		data(){
			return {
				submitLoading: false,
				// 选择项
				options: {
					// 分类选择项
					type: [/*{value: 'zhinan', label: '指南', children: []}*/ ],
					// 标签的选择项
					tags: [],
				},
				// 已选择
				selected: {
					type: [],
					tag: [],
				},
				// 
				form: {
					// 分类
					classification: [],
					// 类别
					type: [],
				},
				// 文件列表
				fileList: [],
				// 成功上传的文件列表
				uploadedFileList: [],
				// 上传中的列表
				uploadingFileList: [],
				updata: {
					host: ''
                },
                tagList:'',
                tag:[]
			}
		},
		components: {
			CusUpload: Upload,
		},
		created() {
			// 检查当前域名是否合规（OSS域名限制）
			this.checkHost()

			// 初始化上传组件配置
			MaterialAPI.getConfig().then(res=>{
				this.updata = res;
			});

			// 获取分类
			MaterialAPI.getType().then(res => {
				if (res.ret==1) {
					this.options.type = res.data.item.children;
					// 递归处理字段的差异性
					this.convertFields(this.options.type);
				} else {
					this.$message.error(res.msg);
				}
			})

			// 获取类型
			MaterialAPI.getTags().then(res => {
				this.options.tags=res.typeList;
            });
            
            //获取标签
            TagAPI.tagList().then(res =>{
                if(res.code == 200){
                    this.tagList = res.tagsList;
                }else{
                    this.$message.error(res.msg);
                }
            })
		},
		methods: {
			checkHost() {
				if (/\s*\.stosz\.com/.test(location.host)!=true) {
					this.$message.error('当前域名不支持阿里云OSS上传');
				}
			},
			// 保存
			handleSave() {
				if (this.selected.type.length==0) {
					this.$message.error('请选择分类');
					return false;
				}
				if (this.selected.tag.length==0) {
					this.$message.error('请选择标签');
					return false;
				}
				if (this.uploadedFileList.length==0) {
					this.$message.error('请上传文件');
					return false;
				}
				// 避免快速提交
				if (this.uploadedFileList.length != this.uploadingFileList.length) {
					this.$message.error('别急，还没上传完成！');
					return false;
				}
				// 避免多次执行
				if (this.submitLoading==true) return false;
				this.submitLoading = true;
                // Ajax
                if(this.tag.length>0){
                    var obj = {
                        tag_ids:this.tag.join(),
                        product_category_id: this.selected.type[this.selected.type.length-1],
                        resource_type_id: parseInt(this.selected.tag[this.selected.tag.length-1]),
                        datas: this.uploadedFileList
                    }
                }else{
                    var obj = {
                        product_category_id: this.selected.type[this.selected.type.length-1],
                        resource_type_id: parseInt(this.selected.tag[this.selected.tag.length-1]),
                        datas: this.uploadedFileList
                    }
                }
				MaterialAPI.add(obj).then(res => {
					this.submitLoading = false;
					if (res.code==200) {
						this.$refs.uploadComponent.clearFiles();
						this.$message(res.msg);
						this.$router.back(-1);
					} else {
						this.$message.error(res.msg);
					}
				})
			},
			// 文件变更监控
			handleChange(file, fileList) {
				this.uploadingFileList = fileList;
			},
			// 上传前的格式验证
			handleBeforeUpload(file) {
			},
			// 删除文件
			handleRemove(file, fileList) {
				this.uploadedFileList = this.uploadedFileList.filter(row => {
					return row.uid != file.uid;
				})
			},
			// 上传中
			onProgress(event, file, fileList) {
				this.uploadingFileList = fileList.map(file => {
					return {
						uid: file.uid,
						name: file.name,
						size: file.size,
					}
				});
			},
			// 上传成功后的回调
			handleSuccess(res, file, fileList) {
				this.uploadedFileList = fileList.map(file => {
					return {
						uid: file.uid,
						thumb: file.key,
						mimeType: file.raw.type ||'image/vnd.adobe.photoshop',
						size: file.size,
					}
                });
			},
			onError(res, file) {
				this.$message.error(res.msg);
			},
			/*
			递归处理分类表的字段的差异性
				name => label
				id => value
			*/
			convertFields(source) {
				source.map(row => {
					row['value'] = row.id;
					row['label'] = row.name;
					if (row['children']) {
						this.convertFields(row['children']);
					} ;
				})
			}

		}
	}
</script>