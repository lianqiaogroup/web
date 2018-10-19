<template>
	<div class="module_boxer" id="chooseModule">
		<div class="module_title">选择模块</div>
        <ul>
            <li v-for="module in proplist" v-on:click="handleClick(module)">
                <span><label>{{modules[module].module_label}}</label></span>
            </li>
        </ul>
	</div>
</template>
<script type="text/javascript">
export default {
	props: {
        'proplist': {
            type: Array,
            require: true,
            default: [],
        }
        , 'callback': {
            type: Function,
            require: false,
        }
    }
    , data() {
        return {
            collect: [
                'countdown'
                , 'comment'
                , 'service'
                , 'promotion'
                , 'color'
                , 'service'
                , 'flashsale'
            ]
            , modules: {
            	'countdown': {
            		'module_name': 'countdown'
            		, 'module_label': '倒计时模块'
            	}
            	, 'comment': {
            		'module_name': 'comment'
            		, 'module_label': '评论模块'
            	}
            	, 'service': {
            		'module_name': 'service'
            		, 'module_label': '服务说明'
            	}
            	, 'promotion': {
            		'module_name': 'promotion'
            		, 'module_label': '促销信息'
            	}
                , 'color': {
                    'module_name': 'color'
                    , 'module_label': '主题颜色'
                }
                , 'flashsale': {
                    'module_name': 'flashsale'
                    , 'module_label': '限时抢购'
                }
                , label_preset: {
                    'image': '细节图模块'
                    , 'countdown': '倒计时模块'
                    , 'promotion': '促销模块'
                    , 'comment': '用户评论模块'
                    , 'service': '服务说明模块'
                    , 'favorite': '关联推荐模块'
                    , 'product_selection': '属性选择模块'
                    , 'product_content': '图文详情模块'
                    , 'color': '主题颜色'
                }
            }
        }
    }
    , mounted() {
        
    }
    , methods: {
        handleClick(name) {
            this.callback && this.callback(name);
        }
    }
}
</script>