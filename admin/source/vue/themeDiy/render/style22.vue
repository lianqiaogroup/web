<template>
	<section id="style22">
			<section id="page-index">
			<div class="swiper-container">
				<div class="swiper-wrapper">
					<div class="swiper-slide" >
						<img v-bind:src="product.thumb">
					</div>
				</div>
				<div class="swiper-pagination mun_banner"></div>
			</div>
			<div class="product_title">{{ product.title }}</div>
			<div class="module_comment" v-if="comment.open=='1'">
	            <div class="comment_score">★★★★★<span>Product Comment</span></div>
	            <div class="comment_viewall">Customer reviews</div>
	        </div>
	        <!--  -->
			<div class="m-detailBaseInfo">
				<div class="info">
					<div class="line2" style="text-align: right;">
						<div class="retailPrice">
							<span class="small_price" style="position: relative;top: -3px;">{{ product.currency_code }}</span>
							<span>{{ product.price }}</span>
							<span style="font-size: 12px;color:#91949F;float: right;margin-top: 2px;font-weight: 100 ">
								<i class="this_i"></i>
								last {{ product.stock }}件</span></div>
						<div class="market_price_onr">
							<span>market price</span>
							<del>
								{{ product.currency_code }}{{ product.market_price }}
							</del>
						</div>
					</div>
					<div class="stat">Free Delivery.Estimated arrival time<span id="timer">11/4-11/9</span></div>
				</div>
			</div>
			<!--  -->
			<div class="specSelect">
				<div class="m-listItem" id="val-sel">
					<div class="f-vc">
						<div class="inner"><span class="it">Product parameter</span><span class="taocan"></span></div>
					</div>
					<i class="icon u-icon u-address-right"></i>
				</div>
			</div>
			<ul class="module_service">
				<li class="item" v-for="item in service.list.split(',')">
					<i></i><span>{{ item }}</span>
				</li>
			</ul>
			<div class="dt-section">
				<div class="btn" id="addToCart">
					<a href="javascript:void(0);" class="w-button w-button-block btn-addToCart">buy_now</a>
				</div>
				<div class="btn">
					<a href="javascript:void(0);" class="inquiry">Track order</a>
				</div>
			</div>
			<div class="product-info" id="bTop">
				<div v-html="product.content"></div>
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