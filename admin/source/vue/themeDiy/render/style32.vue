<template>
	<section id="style32">
		<section id="page-index">
			<div class="header">
				<h1>{{ product.seo_description }}</h1>
			</div>
			<div class="img_wrap">
				<h2>商品圖片</h2>
				<div class="swiper-container">
					<div class="swiper-slide">
						<img v-bind:src="product.thumb" width="100%">
					</div>
				</div>
			</div>
			<div class="dom-sales">
				<h2>限時下殺</h2>
				<div class="sales_wrap">
					<div class="sales">
						<div class="price">
							{{ product.currency_code }}<ins>{{ parseInt(product.price) }}</ins>
						</div>
						<div class="sales_info">
							<div>
								<del>{{ product.market_price }}</del>
							</div>
							<div class="sales_num">
								{{ product.sales }}件已售
							</div>
						</div>
					</div>
					<div class="time_wrapper" v-if="countdown.open=='1'">
						<div class="timeWrap">
							<span id='clock'>{{ countdown.label }}</span>
							<span id="timer">
								<span  id="h">10</span>
								<span class="">时</span>
								<span id="m">00</span>
								<span class="">分</span>
								<span id="s">00</span>
								<span class="">秒</span>
							</span>
						</div>
					</div>
				</div>
			</div>
			<div class="module_title">
				<h1>{{ product.title }}</h1>
			</div>
			<div class="service-tips">
				<div class="service">
					<template v-for="item in service.list.split(',')">
						<span>{{ item }}</span>
					</template>
				</div>
			</div>
			<div class="moduel_details">
				<div class="buy_now">
					<div v-if="comment.open=='1'">
						<a href="javascript:;">用戶評論（100）</a>
					</div>
					<a href="javascript:;">立即購買</a>
				</div>
			</div>
			<div class="product_info">
				<h2>商品屬性</h2>
				<div class="m-img" v-html="product.content"></div>
			</div>
			<!--  -->
			<div class="comment-layer" id="comment" v-if="comment.open=='1'">
				<h2>用戶評價</h2>
				<div class="picMarquee-top">
					<div class="bd">
						<ul class="picList">
							<li>
								<span class="fontRed">用戶名</span>&nbsp;
								<span>滿意度：<span class="fontRed">5</span></span>
								<div class="comment_content">内容内容</div>
							</li>
							<li>
								<span class="fontRed">用戶名</span>&nbsp;
								<span>滿意度：<span class="fontRed">5</span></span>
								<div class="comment_content">内容内容</div>
							</li>
							<li>
								<span class="fontRed">用戶名</span>&nbsp;
								<span>滿意度：<span class="fontRed">5</span></span>
								<div class="comment_content">内容内容</div>
							</li>
							<li>
								<span class="fontRed">用戶名</span>&nbsp;
								<span>滿意度：<span class="fontRed">5</span></span>
								<div class="comment_content">内容内容</div>
							</li>
						</ul>
					</div>
				</div>
				<div style="clear: both"></div>
			</div>
		</section>
	</section>
</template>


<script type="text/javascript">
	
export default {
	data() {
		return {
	  
		}
	}
	, props: ['countdown', 'comment', 'product', 'color', 'service']
	, mounted() {

	}
	, methods: {
	   
	}
}

</script>