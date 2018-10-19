<template>
	<div id="page_theme_diy">
		<div class="page_theme_diy_title">
			模版：{{product.theme }}
			<el-button @click="handleReset">重置数据</el-button>
		</div>
		<section class="render theme_effect">
			<component v-if='false'
				:is="themeRouter"
				v-bind:countdown="countdown"
				v-bind:comment="comment"
				v-bind:color="color"
				v-bind:service="service"
				v-bind:product="product"
				v-bind:flashsale="flashsale">
			</component>
			<component
				ref="iframe"
				:is="themeRouter"
				v-bind:domain="product.domain"
				v-bind:identity_tag="product.identity_tag">
			</component>
		</section>
		<aside>
			<component
				:is="component.choose"
				:proplist="chooseModule.list"
				:callback="handleShowSetting">
			</component>
			<!--  -->
			<div class="module_boxer module_color" v-show="showSetting=='color'">
				<div class="module_title">主题颜色</div>
				<form action="#" name="" onsubmit="return false">
					<div class="color_block">
						<span
							v-for="item in color.list"
							:style="{ background: item.value }"
							:class="{ 'el-icon-check': color.selected==item.className }"
							@click="handleColorChange(item.className)">
						</span>
					</div>
					<div class="button_layer">
						<el-button @click="handleColorSave" type="primary">保存</el-button>
					</div>
				</form>
			</div>
			<!--  -->
			<div class="module_boxer" id="module_countdown" v-show="showSetting=='countdown'">
				<div class="module_title">抢购倒计时</div>
				<form action="#" name="" onsubmit="return false">
					<div>
						<label><input value="1" type="radio" v-model="countdown.open"> 开启</label>
						<label><input value="0" type="radio" v-model="countdown.open"> 关闭</label>
					</div>
					<div>
					<el-input
						placeholder="活动名称，2-4个字"
						v-model="countdown.label">
					</el-input>
					<el-select v-model="countdown.time_step" placeholder="倒计时时长">
						<el-option label="2小时" value="2"></el-option>
						<el-option label="4小时" value="4"></el-option>
						<el-option label="6小时" value="6"></el-option>
						<el-option label="8小时" value="8"></el-option>
					</el-select>
					</div>
					<div class="button_layer">
						<el-button @click="handleCountdownSave" type="primary">保存</el-button>
					</div>
				</form>
			</div>
			<!--  -->
			<div class="module_boxer" id="module_comment" v-show="showSetting=='comment'">
				<div class="module_title">用户评论</div>
				<form onsubmit="return false">
					<div>
						<label><input value="1" type="radio" v-model="comment.open"> 开启</label>
						<label><input value="0" type="radio" v-model="comment.open"> 关闭</label>
					</div>
					<div class="button_layer">
						<el-button @click="handleCommentSave" type="primary">保存</el-button>
					</div>
				</form>
			</div>
			<div class="module_boxer" v-show="showSetting=='service'">
				<div class="module_title">服务说明（用逗号,隔开）</div>
				<form onsubmit="return false">
					<div>
						<el-input
							type="textarea"
							:rows="4"
							placeholder="请输入内容"
							name="servicelist"
							:value="service.list.replace(/,/g,'\n')">
						</el-input>
					</div>
					<div class="button_layer">
						<el-button @click="handleServiceSave" type="primary">保存</el-button>
					</div>
				</form>
			</div>
			<!--  -->
			<div class="module_boxer" v-show="showSetting=='flashsale'">
				<div class="module_title">限时抢购</div>
				<form onsubmit="return false">
					<div>
						<label><input value="1" type="radio" v-model="flashsale.open"> 开启</label>
						<label><input value="0" type="radio" v-model="flashsale.open"> 关闭</label>
					</div>
					<div class="button_layer">
						<el-button @click="handleFlashSaleSave" type="primary">保存</el-button>
					</div>
				</form>
			</div>
			<!--  -->
		</aside>
	</div>
</template>

<script>



// 每个模版允许的配置
import themeSettingConfigDefault from './config.vue';

import cmpt_style5 from './render/style5.vue';
import cmpt_style22 from './render/style22.vue';
import cmpt_style27 from './render/style27.vue';
import cmpt_style32 from './render/style32.vue';
import cmpt_style57 from './render/style57.vue';
import cmpt_style64 from './render/style64.vue';

import cmpt_iframe from './render/iframe.vue';

import cmpt_choose from './setting/choose.vue';


let themesComponent = {
	'style5_2': cmpt_style5
	, 'style27_2': cmpt_style27
	, 'style22': cmpt_style22
	, 'style57': cmpt_style57
	, 'style32_2': cmpt_style32
	, 'style64': cmpt_style64
	, 'iframe': cmpt_iframe
}


export default {
	data() {
		return {
			product_id: ''
			, theme_code: ''
			, themeRouter: null
			, product: {}
			// 倒计时模块
			, countdown: {
				open: 1
				, sort: 0
				, module_name: 'countdown'
				, module_id: 0
				, label: ''
				, time_step: '8'
			}
			// 评论模块
			, comment: {
				open: 1
				, sort: 0
				, module_name: 'comment'
				, module_id: 0
			}
			// 主题色
			, color: {
				module_name: 'comment'
				, module_id: 0
				, list: []
				, selected: 'class-a'
			}
			, service: {
				module_name: 'service'
				, module_id: 0
				, sort: 0
				, open: 1
				, list: ''
			}
			// 限时抢购
			, flashsale: {
				module_name: 'flashsale'
				, module_id: 0
				, sort: 0
				, open: 1
			}
			// 显示编辑的模块
			, showSetting: ''
			// 加载组件
			, launchModule: []
			// 
			, firstTimeID: 0
			// 选择组件模块数据
			, chooseModule: {
				list: []
			}
			// 是否正在加载
			, loading: false
			// 
			, component: {
				'choose': cmpt_choose
			}
		}
	}
	, mounted() {
		this.get();
	}
	, methods: {
		get(){
			let self = this;
			this.product_id = this.$route.params.id;
			this.$http.get('/theme_edit.php?json=1&product_id='+this.product_id).then(res=>{
				// 加载render
				this.theme_code = res.body.theme || "style57";
				this.product = res.body;
				this.themeRouter = themesComponent['iframe'];
				// this.themeRouter = themesComponent[this.theme_code];

				// 加载可编辑的模块
				// let defaultConfig = themeSettingConfigDefault['style5'];
				let defaultConfig = themeSettingConfigDefault[this.theme_code];
				// 是否支持
				if( !defaultConfig){
					this.$message.error('此模版暂不支持自定义模版');
					this.$router.push({path:'/products'});
					return false;
				}
				// 清空chooseModule组件数据
				this.chooseModule.list = [];
				for(let name in defaultConfig){
					// 初始化chooseModule组件
					this.chooseModule.list.push(name);
					
					// 各组件初始化默认数据
					let options = defaultConfig[name];
					for(let key in options){
						this[name][key] = options[key];
					}
					// 
					this.launchModule.push(name);
				}

				// 获取自定义的数据
				this.$http.post('/theme_edit.php?act=loadModule',
						{ 'product_id': this.product_id }, { emulateJSON: true }
				).then(res=>{
					if( res.body.modules == null ){
						// first time
						this.firstTime(this.launchModule[0]);
					}else{
						res.body.modules.map(row=>{
							let module_name = row.module_name;
							// 避免没有这组件
							if( self[module_name] ){
								self[module_name].module_id = row.module_id;
								self[module_name].module_name = row.module_name;
								for(let key in row.options ){
									if( self[module_name][key]!=null ){
										self[module_name][key] = row.options[key]
									}
								}
							}
						});
					}
				});
				
			});

		}
		// 重置数据
		, handleReset(){
			this.$http.post('/theme_edit.php?act=reset', {product_id: this.product_id}, { emulateJSON: true }).then(res=>{
				if( res.body.ret == '1' ){
					this.$message({'message': '成功', 'type': 'success'});
					this.get();
				}
			});
		}
		// 显示编辑模块
		, handleShowSetting(module_name){
			this.showSetting = module_name || '';
		}
		, handleCountdownSave(){
			this.handleModuleSave('countdown', this.countdown);
		}
		, handleCommentSave(){
			this.handleModuleSave('comment', this.comment);
		}
		, firstTime(module_name){
			let self = this;
			if( !module_name ){
				this.get();
				return false;
			}else{
				let postdata;
				if( module_name!="color" ){
					postdata = this[module_name];
				}else{
					postdata = {}
					postdata.module_name = 'color';
					postdata.sort = 0;
					postdata.module_id = this.color.module_id;
					postdata.selected =this.color.selected;
				}
				postdata.product_id = this.product_id;
				this.$http.post('/theme_edit.php?act=saveModule', postdata, { emulateJSON: true }).then(res=>{
					this.firstTimeID++;
					let _name = this.launchModule[this.firstTimeID];
					this.firstTime.call(self, _name);
				});
			}
		}
		, handleModuleSave(module_name, param, callback){
			if( this.loading==true ){
				return false;
			}else{
				this.loading = true;
			}
			let postdata = param;
				postdata.product_id = this.product_id;
			this.$http.post('/theme_edit.php?act=saveModule', postdata, { emulateJSON: true }).then(res=>{
				this.loading = false;
				if( res.body.ret == '1' ){
					this.$message({'message': '保存成功', 'type': 'success'});
					this[module_name].module_id = res.body.module_id;
					// 刷新
					this.$refs.iframe.onUpdate();
				}else{
					this.$message.error('保存失败');
				}
				callback && callback(res.body);
			});
		}
		//  选择色块
		, handleColorChange(val){
			this.color.selected = val;
		}
		// 保存颜色模块
		, handleColorSave(){
			let postdata = {}
				postdata.module_name = 'color';
				postdata.sort = 0;
				postdata.module_id = this.color.module_id;
				postdata.selected =this.color.selected;
			this.handleModuleSave('color', postdata);
		}
		// 保存服务模块
		, handleServiceSave(){
			let _val = document.getElementsByName('servicelist')[0].value;
			this.service.list = _val.split('\n').join(',');
			this.handleModuleSave('service', this.service);
		}
		, handleFlashSaleSave(){
			this.handleModuleSave('flashsale', this.flashsale);
		}
	}
}
</script>

