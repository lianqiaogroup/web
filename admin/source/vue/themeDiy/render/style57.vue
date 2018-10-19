<template>
	<div id="style57">
		<div class="title">
			{{ product.title }}
		</div>
		<!--  -->
		<div class="swiper-container">
			<div class="swiper-wrapper">
				<div class="swiper-slide" >
					<img v-bind:src="product.thumb">
				</div>
			</div>
			<div class="swiper-pagination"></div>
		</div>
		<template v-if="countdown.open=='1'">
			<div class="end">{{ product.sales }}人已購買 剩餘{{ product.stock }}件 售完即止</div>
		</template>
		<div class="detail">
			<div class="sales">
				<div class="price">
					活动价：
					<span>{{ product.currency_code }}{{ product.price }}</span>
				</div>
				<div class="discount">
					<div>折扣：<span class="number">7</span>折</div>
					<div class="right">
						<span>自定义标签</span>
						<span>自定义标签</span>
					</div>
				</div>
			</div>
			<template v-if="countdown.open=='1'">
				<div class="processing">
					<div>已抢购{{ product.sales }}件</div>
					<div>
						<span class="process"></span>
						<span class="percent">100%</span>
					</div>
				</div>
				<div class="limit">
					<div>限时优惠</div>
					<span class="timeWrap">距离结束
						<span id="timer">
						   <span  id="h">10</span>
							<span class="">小时</span>
							<span id="m">00</span>
							<span class="">分</span>
							<span id="s">00</span>
							<span class="">秒</span>
					  </span>
					</span>
				</div>
			</template>
			<div class="deliver">
				派送方式：<span>货到付款</span>
			</div>
		</div>
		<div class="product_info">
			<div class="tab_bar">
				<ul>
					<li class="active">商品详情</li>
					<li v-if="comment.open=='1'">评论列表</li>
				</ul>
			</div>
			<div class="m-img">
				<div v-html="product.content"></div>
			</div>
			<!--  -->
			<template v-if="comment.open=='1'">
				<div class="scroll_tt" id="pinlun_tt">客户评价</div>
				<div id="pinlun_content">
					<div class="picMarquee-top">
						<div class="bd" data-fn="userScroll" style="overflow: hidden;font-size:14px;">
							<div class="picList">
								<div class="lab" data-id="0" style="color:#a8aeb7;padding-bottom:10px;">
									<div style="border-bottom: 1px dotted #ddd;padding: 5px;">
										<span class="inm"><span id="wi">客户名字</span></span>
										&nbsp;
										<span>评分：<span style="color: red;">5</span></span>
									</div>
									<P style="color: #666;padding: 5px;">评论内容</P>
								</div>
								<div class="lab" data-id="0" style="color:#a8aeb7;padding-bottom:10px;">
									<div style="border-bottom: 1px dotted #ddd;padding: 5px;">
										<span class="inm"><span id="wi">客户名字</span></span>
										&nbsp;
										<span>评分：<span style="color: red;">5</span></span>
									</div>
									<P style="color: #666;padding: 5px;">评论内容</P>
								</div>
								<div class="lab" data-id="0" style="color:#a8aeb7;padding-bottom:10px;">
									<div style="border-bottom: 1px dotted #ddd;padding: 5px;">
										<span class="inm"><span id="wi">客户名字</span></span>
										&nbsp;
										<span>评分：<span style="color: red;">5</span></span>
									</div>
									<P style="color: #666;padding: 5px;">评论内容</P>
								</div>
							</div>
						</div>
					</div>
				</div>
			</template>
		</div>
	</div>
</template>


<script type="text/javascript">
	
export default {
	data() {
		return {
	  
		}
	}
	, props: ['countdown', 'comment', 'product']
	, mounted() {

	}
	, methods: {
	   
	}
}

</script>