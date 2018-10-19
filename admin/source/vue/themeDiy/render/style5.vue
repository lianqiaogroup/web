<template>
	<section id="style5">
		<div id="page-index" :class="color.selected">
			<header>
				<h1>{{ product.title }}</h1>
				<a href="/" class="home"></a>
			</header>
			<div class="swiper-container">
				<div class="swiper-wrapper">
					<div class="swiper-slide" >
						<img v-bind:src="product.thumb">
					</div>
				</div>
				<div class="swiper-pagination mun_banner"></div>
			</div>
			<!-- service -->
			<template v-if="service.list!=''">
				<ul class="m-servicePolicy">
					<li class="item" v-for="item in service.list.split(',')">
						<i class="u-icon u-icon-servicePolicyRed"></i><span>{{ item }}</span>
					</li>
				</ul>
			</template>
			<!--  -->
			<div class="dt-section">
				<div class="m-detailBaseInfo">
					<div class="info">
						<div class="line2" style="text-align: right;height: 45px;">
							<em class="retailPrice">{{ product.currency_code }}{{ product.price }}</em>
							<del class="del_price">{{ product.currency_code }}{{ product.market_price }}</del>
							<span style="width: 40px;" class="discount">
								<label style="width: 40px;">折扣</label>
								<label style="width: 40px;font-size: 14px;">3</label>
							</span>
						</div>
						<div class="tagList">
							<ul class="wrap">
								<li class="item"><span>标签</span></li>
								<li class="item"><span>标签</span></li>
								<li class="item"><span>标签</span></li>
							</ul>
						</div>
						<div class="hostsales" style="text-align: left;">
							<span class="soldPart">已搶購<span id="soldNum">{{ product.sales }}</span>件</span>
							<span class="percentBar">
							  <span id="progress"></span>
							</span>
							<span id="percentNum">0%</span>
						</div>
						<template v-if="countdown.open=='1'">
							<div class="module_countdown" style="text-align: left;">
								<span class="flag1">{{ countdown.label || '限時搶購' }}</span>
								<span class="timeWrap">距結束
									<span id="timer">
										<span id="h">8</span>
										<span class="colon">時</span>
										<span id="m">8</span>
										<span class="colon">分</span>
										<span id="s">8</span>
										<span class="colon">秒</span>
									</span>
								</span>
							</div>
						</template>
					</div>
					<div class="specSelect">
						<div class="m-listItem" id="val-sel">
							<div class="f-vc">
								<div class="inner"><span class="it" style="font-weight:normal;">請選擇規格數量</span></div>
							</div>
							<i class="icon u-icon u-address-right"></i>
						</div>
					</div>
				</div>
			</div>
			<!--  -->
			<div class="common_title"><span class="bTitle">圖文詳情</span></div>
			<div class="product-info" v-html="product.content"></div>
			<!-- 评论 -->
			<template v-if="comment.open=='1'">
				<div class="common_title" ><span class="bTitle">用戶評價</span></div>
				<div class="module_comment" style="padding: 8px;background: white">
					<div class="picMarquee-top">
						<div class="bd" data-fn="commentScroll" style="height: 300px; overflow: hidden">
							<ul class="picList">
								<li style="margin-bottom: 8px;height: auto;">
									<span>用戶名</span>
									&nbsp;
									<span>滿意度：<span>5</span></span>
									<div style="border-top:1px dotted #d0d6d6;margin-top: 5px;color: #444;padding:5px 0;font-size: 12px;">评论内容</div>
								</li>
								<li style="margin-bottom: 8px;height: auto;">
									<span>用戶名</span>
									&nbsp;
									<span>滿意度：<span>5</span></span>
									<div style="border-top:1px dotted #d0d6d6;margin-top: 5px;color: #444;padding:5px 0;font-size: 12px;">评论内容</div>
								</li>
								<li style="margin-bottom: 8px;height: auto;">
									<span>用戶名</span>
									&nbsp;
									<span>滿意度：<span>5</span></span>
									<div style="border-top:1px dotted #d0d6d6;margin-top: 5px;color: #444;padding:5px 0;font-size: 12px;">评论内容</div>
								</li>
							</ul>
						</div>
					</div>
					<div style="clear: both"></div>
					<div class="buyinfo_hd">在线留言</div>
				</div>
			</template>
			<!--  -->
			<div class="User_notes" style="margin-top: 10px">
				<h2 class="notes">用戶須知</h2>
				<div class="note_text">本產品的實際使用效果根據個人情況決定，不保證每位用戶都能享受到所宣傳的效果。若有疑問請諮詢在綫客服或通過電子郵箱聯絡我們，本公司享有最終解釋權。</div>
				<div class="note_title">·關於發貨方式</div>
				<div class="note_text">配送範圍全XX。</div>
				<div class="note_title">·關於配送時間</div>
				<div class="note_text">下單成功之後，我們會按照下單先後順序進行配貨，配貨週期為3個工作日左右，一般到達時間為7個工作日左右。</div>
				<div class="note_title">·如何申請退換貨</div>
				<div class="note_text">1.由於質量原因產生的退換貨：至收到商品之日起7天內，向售後服務中心發送郵件至<a class="mailto" href="#">xxx@xxx.com</a>，客服會在收到郵件后的1-3個工作日內受理您的請求，退換貨所產生的運費由我方承擔。</div>
				<div class="note_title">2.退換貨流程:</div>
				<div class="note_text">確認收貨—申請退換貨—客服審核通過—用戶寄回商品—倉庫簽收驗貨—退換貨審核—退款/換貨；</div>
				<div class="note_title">退換貨請註明：訂單號、姓名、電話。</div>
				<div class="note_title">·如何取消訂單</div>
				<div class="note_text">取消訂單需要向售後服務中心發送郵件并注明相关原因，邮件内容应註明您的訂單號、姓名、電話。</div>
			</div>
		</div>
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