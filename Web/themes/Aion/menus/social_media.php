<?php 
$set = web_settings();
$fb_img    = img_dir("facebook.png");
$fb_img_on = img_dir("facebook_on.png");
$sh_img    = img_dir("shop.png");
$sh_img_on = img_dir("shop_on.png");
$tw_img    = img_dir("tweeter.png");
$tw_img_on = img_dir("tweeter_on.png");
echo "
<div class='social_media_box'>
   <div class='media'>
      <a class='hvr-buzz-out' target='_blank' href='{$set[18]}'><img onmouseover=\"this.src='{$fb_img_on}'\" onmouseout=\"this.src='{$fb_img}'\" src='{$fb_img}' alt='Facebook'/></a>
      <a class='hvr-wobble-horizontal' target='_blank' href='{$set[19]}'><img onmouseover=\"this.src='{$sh_img_on}'\" onmouseout=\"this.src='{$sh_img}'\" src='{$sh_img}' alt='Shop'/></a>
      <a class='hvr-wobble-vertical' target='_blank' href='{$set[20]}'><img onmouseover=\"this.src='{$tw_img_on}'\" onmouseout=\"this.src='{$tw_img}'\" src='{$tw_img}' alt='Twitter'/></a>
   </div>
</div>
";
?>