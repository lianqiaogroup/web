<template>
	<section id="style64">
		<section id="page-index">
			<div class="img_wrap">
				<div class="swiper-container">
					<div class="swiper-wrapper">
						<div class="swiper-slide">
							<img v-bind:src="product.thumb" width="100%">
						</div>
					</div>
				</div>
				<div class="discount">-40%</div>
			</div>
			<div class="detail">
				<div class="title"><h1>{{ product.title }}</h1></div>
				<div class="rating">
					<div class="stars"></div>96% Positive evaluation
				</div>
			</div>
			<div class="detail">
				<div class="price">
					<del>
						${{ product.market_price }}
					</del>
					<div>
						<span>
							{{ product.currency_code }}<span>{{ product.price }}</span>
						</span>
						<span class="discount">
							40% OFF
						</span>
					</div>
				</div>
			</div>
			<div class="detail" v-if="flashsale.open=='1'">
				<div class="sales">
					<div class="progress">
						<span></span>
					</div>
					<div class="stock">only {{ product.stock }} pieces left</div>
				</div>
			</div>
			<div id="val-sel" class="arrow_con">
				<div class="arrow_d">Choose parameter</div>
			</div>
			<div class="expand">
				<div class="left"><span class="inquiry">View my order</span></div>
				<div class="right" v-if="comment.open=='1'"><span class="userev">Customer reviews</span></div>
			</div>
			<div class="product_info">
				<div class="tt">
					<div class="title">Graphic Details</div>
					<div class="kefu"></div>
				</div>
				<div class="m-img" v-html="product.content"></div>
			</div>
			<template v-if="comment.open=='1'">
				<div class="scroll_tt" id="pinlun_tt">All reviews</div>
				<div class="pinlun_content">
					<ul id="pinlun_content">
						<li class="lab cover" data-id="0">
							<div class="item-wrap">
							<div class="msKeimgBox">
								<div class="item-img box">
									<img src="#">
								</div>
								<div class="mskeTogBtn"></div>
								<div style="clear:both;"></div>
							</div>
							<div class="main-com" style="padding: 5px;">
								<div class="inm">
									<span id="nm" class="nm">user name</span>
								</div>
								<P style="color: #666;padding: 5px;word-break: break-all;">the message</P>
							</div>
							</div>
						</li>
					</ul>
					<div id="loadMore" class="loadMore">View more</div>
				</div>
				<div class="layer_com">
					<div class="big_com">
						<div class="close-btn">×</div>
						<div class="layer-main"></div>
					</div>
				</div>
			</template>
		</section>
	</section>
</template>


<script type="text/javascript">
	
export default {
	data() {
		return {
	  
		}
	}
	, props: ['comment', 'product', 'flashsale']
	, mounted() {

	}
	, methods: {
	   
	}
}

</script>