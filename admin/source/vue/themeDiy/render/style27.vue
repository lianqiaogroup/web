<template>
	<section id="style27">
		<div id="page-index">
			<div class="swiper-container">
				<div class="swiper-wrapper">
					<div class="swiper-slide" >
						<img v-bind:src="product.thumb">
					</div>
				</div>
				<div class="swiper-pagination swiper-pagination-fraction">
					<span class="swiper-pagination-current">1 / <span class="swiper-pagination-total">1</span></span>
				</div>
			</div>
			<!--  -->
			<div style="position: relative;">
				<div class="price b_price">
					<span class="top_price">{{ product.currency_code }}{{ product.price }}</span>
					<span class="pre_price">原价{{ product.currency_code }}{{ product.market_price}}</span>
				</div>
				<template v-if="countdown.open=='1'">
					<div class="timeWrap b_timeWrap">
						<span id='clock'>{{ countdown.label }}</span>
						<span id="timer">
							<span id="h">10</span>
							<span class="">:</span>
							<span id="m">00</span>
							<span class="">:</span>
							<span id="s">00</span>
						</span>
					</div>
				</template>
				<div style="clear:both"></div>
			</div>
			<div class="module_container" v-if="flashsale.open=='1'">
				<span class="module_process">
					<span></span>
				</span>
			</div>
			<div class="module_container">
				<h1 class="product_title">{{ product.title }}</h1>
			</div>
			<div class="module_container">
				<ul class="module_tags">
					<li>自定义标签1</li>
					<li>自定义标签2</li>
					<li>自定义标签3</li>
				</ul>
			</div>
			<div class="module_container arrival_time">
				現在下單預計11/1-11/8日送達
			</div>
			<div class="inqcom">
				<div class="inquiry"><a href="/order_quality.php">订单查询</a></div>
				<template v-if="comment.open=='1'">
					<div class="com_pinlun"><a href="#detial-appraise">用户评价</a></div>
				</template>
			</div>
			<div class="module_service">
				<ul>
					<li v-for="item in service.list.split(',')">{{item}}</li>
				</ul>
				<div class="right"></div>
				<div class="service_detail">
					<h3>溫馨提示</h3>
					<p>...</p>
				</div>
				<div style="clear:both"></div>
			</div>
			<div class="product_info">
				<div class="m-img" v-html="product.content"></div>
			</div>
			<template v-if="comment.open=='1'">
				<div class="detail-block" id="detial-appraise">
					<h4>最新评价</h4>
					<div id="mq" data-fn="commentScroll">
						<div>
							<div class="appr-title mqc">
								<span style="color:red; margin:0px 3px">用户名</span>
								<span>满意度：<font color="red">★★★★★</font></span><br/>
							</div>
							<div class="mqc">
								<p><span style="font-family:宋体">评论内容</span><span style="font-family:宋体"></span></p>
							</div>
							<div class="appr-title mqc">
								<span style="color:red; margin:0px 3px">用户名</span>
								<span>满意度：<font color="red">★★★★★</font></span><br/>
							</div>
							<div class="mqc">
								<p><span style="font-family:宋体">评论内容</span><span style="font-family:宋体"></span></p>
							</div>
							<div class="appr-title mqc">
								<span style="color:red; margin:0px 3px">用户名</span>
								<span>满意度：<font color="red">★★★★★</font></span><br/>
							</div>
							<div class="mqc">
								<p><span style="font-family:宋体">评论内容</span><span style="font-family:宋体"></span></p>
							</div>
						</div>
					</div>
				</div>
			</template>
		</div>
	</section>
</template>


<script type="text/javascript">
	
export default {
	data() {
		return {
	  
		}
	}
	, props: ['countdown', 'comment', 'product', 'service', 'flashsale']
	, mounted() {

	}
	, methods: {
	   
	}
}

</script>