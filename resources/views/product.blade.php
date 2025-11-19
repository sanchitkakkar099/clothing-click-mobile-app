<!DOCTYPE html>
<html lang="en">
<head>
  <title>Clothing Web page</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
   <link rel="preconnect" href="https://fonts.googleapis.com">
   <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
   <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{URL::to('/')}}/assets/css/style.css">
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css">
</head>
<body>
	<?php 
  $url = '';
	$isAndroid = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "android"));
if($isAndroid){
	$url = 'https://play.google.com/store/apps/details?id=com.clothingclick.app&hl=en_US';
}	
	$isIPhone = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "iphone"));
if($isIPhone){
	$url = 'https://apps.apple.com/in/app/clothing-click-second-hand/id1605715607';
}	
if($isIPhone == false){
	
?>
	<header class="main-header">
	    <div class="container">
		    <div class="top-bar-appopen">
				 <div class="top-logo">
					 <a href="#"><img src="{{URL::to('/')}}/assets/images/logo-1.png" alt="logo" height="40px"></a>
					 
				</div>
				<p><b>ClothingClick</b><br/>Local Fashion Resale</p>
				<a href="{{$url}}" class="btn-secondary">Open App</a>
		  </div>
		</div>  
	 </header>
<?php } ?>
<section class="slider-sctn @if($isIPhone){{'iphone_stats'}} @endif
">	 
    <div class="container">
	    <div class="slick-slider product_slider">
		@if($res['images'])
			@foreach($res['images'] as $img)
		  <div class="element element-1">
		       <img src="{{$img['image']}}" alt="white tshirt"/>
		  </div>
		  @endforeach
		  @endif
		</div>
		<div class="product-descriptn">
		    <div class="product-title-row">
		    <h3 class="product-title">{{$res['title']}}</h3>
			<h4 class="product-price">${{$res['price']}}</h3>
			<span class="wish-icon"><svg width="18" height="16" viewBox="0 0 18 16" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M12.7969 0C12.085 0.000841122 11.3808 0.149847 10.7278 0.437828C10.0748 0.725809 9.48676 1.14666 9 1.67442C8.28667 0.900958 7.36095 0.363222 6.34301 0.131016C5.32508 -0.10119 4.26194 -0.0171403 3.29161 0.372253C2.32128 0.761646 1.48858 1.4384 0.901615 2.31466C0.314645 3.19091 0.000513647 4.2262 0 5.28613C0 10.5494 8.35594 15.7155 8.71875 15.9184C8.80624 15.9718 8.90637 16 9.00844 16C9.1105 16 9.21064 15.9718 9.29813 15.9184C9.64406 15.7155 18 10.5494 18 5.28613C17.9985 3.88462 17.4498 2.54096 16.4744 1.54994C15.4989 0.558928 14.1764 0.00151269 12.7969 0ZM9 14.7554C7.54594 13.8125 1.125 9.40645 1.125 5.28613C1.12572 4.40604 1.40227 3.54905 1.91457 2.83936C2.42686 2.12967 3.14829 1.60415 3.9743 1.33896C4.8003 1.07377 5.68796 1.0827 6.50864 1.36443C7.32932 1.64617 8.04037 2.18609 8.53875 2.90594C8.59067 2.98082 8.65955 3.04192 8.73958 3.0841C8.8196 3.12627 8.90844 3.14829 8.99859 3.14829C9.08875 3.14829 9.17758 3.12627 9.25761 3.0841C9.33764 3.04192 9.40652 2.98082 9.45844 2.90594C9.9564 2.18485 10.6676 1.64377 11.4888 1.36124C12.31 1.07871 13.1985 1.06943 14.0253 1.33476C14.852 1.60009 15.574 2.12621 16.0865 2.83676C16.5989 3.5473 16.8751 4.40527 16.875 5.28613C16.875 9.40359 10.4541 13.8097 9 14.7554Z" fill="#D7D7D7"/>
					</svg> {{$res['post_views']}}
					</span>
			</div>		
			<div class="product-details">
			    <div class="detail-b">
				   <h5>Size</h5>
				   <p>{{$res['gender']}}’s {{$res['size']}}</p>
				</div>
				<div class="detail-b">
				   <h5>Condition</h5>
				   <p>{{$res['condition']}}</p>
				</div>
			</div>
			<div class="product-bottom-row">
			    <div class="product-bottom-text">{{$res['location']}}</div>
				<div class="time-ago"><img src="{{URL::to('/')}}/assets/images/clock.svg" alt="clock icon"/>{{$res['posted_on']}} </div>
			</div>
		</div>
		<div class="product-content">
		   <p>{{$res['description']}}</p>
		</div>
    </div>
</section>
	<footer class="footer-sctn two-btns-wrp">
		<div class="container">
			<div class="footer-inner">
			    <a href="#" class="btn-primary"><img src="{{URL::to('/')}}/assets/images/message.svg" alt="message"/>Chat</a>
				<a href="#" class="btn-primary"><img src="{{URL::to('/')}}/assets/images/map.svg" alt="map"/>View on Map</a>
			</div>
		</div>
	</footer> 
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://cdn.jsdelivr.net/jquery.slick/1.4.1/slick.min.js"></script>
	<script src="{{URL::to('/')}}/assets/js/custom.js"></script>
<style>
.iphone_stats{
	margin-top:10px !important;
}
</style>
</body>
</html>


