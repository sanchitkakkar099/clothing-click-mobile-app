<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
   
    <title>{{config('constants.SITE_NAME')}}</title>
	<meta property="fb:app_id" content="203256964808211"/>
    <meta property="al:ios:url" content="https://apps.apple.com/us/app/clothing-click-second-hand/id1605715607"/>
    <meta property="al:ios:app_name" content="Clothing Click: Second Hand"/>
    <meta property="al:ios:app_store_id" content="1605715607"/>
    <meta property="al:android:package" content="com.clothingclick.app"/>
    <meta property="al:android:app_name" content="Clothing Click"/>
    <meta property="al:android:url" content="https://play.google.com/store/apps/details?id=com.clothingclick.app"/>
     <meta property="al:web:should_fallback" content="false"/>

  </head>
  <body>
   <?php if($deviceType == 'ios'){ ?>
   <script>
    setTimeout(function(){ 

      window.location.href = "https://apps.apple.com/us/app/clothing-click-second-hand/id1605715607";

}, 1000);
</script>
<?php } else if($deviceType == 'android'){ ?>
<script>
    setTimeout(function(){ 

      window.location.href = "https://play.google.com/store/apps/details?id=com.clothingclick.app";

}, 1000);
</script>
<?php } else { ?>
<script>
    setTimeout(function(){ 

      window.location.href = "https://play.google.com/store/apps/details?id=com.clothingclick.app";

}, 1000);
</script>
<?php } ?>
  </body>
</html>
