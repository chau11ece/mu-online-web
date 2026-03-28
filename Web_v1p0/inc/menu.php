<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<?php
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {header("Location:../error.php");}else{
include($_SERVER['DOCUMENT_ROOT']."/configs/config.php");
$set = web_settings();

// Get current page for active menu highlighting
$current_page = isset($_GET['p']) ? $_GET['p'] : 'home';

// Override theme from environment variable if set
$current_theme = !empty($option['theme']) ? $option['theme'] : $set[3];

// Helper function for active class
function isActive($page, $current) {
    return ($page == $current) ? 'active' : '';
}

if($current_theme != "Aion"){  ?>

         <ul>
          	<li><a href="?p=home" ><? print phrase_news ?></a></li>
          	<li><a onclick="page('register')"><? print phrase_register ?></a></li>			
          	<li><a onclick="page('downloads')"><? print phrase_download ?></a></li>
          	<li><a onclick="page('statistics')"><? print phrase_statistic ?></a></li>
          	<li><a onclick="page('information')"><? print phrase_information ?></a></li>
			<li><a onclick="window.location.href='?p=topchars'"><? print phrase_ranking ?></a></li>
			<li><a onclick="window.location.href='?p=market'"><? print phrase_market ?></a></li>
			<li><a onclick="window.location.href='?p=auction'"><? print phrase_auction ?></a></li>
         </ul>
         
<?php  } else{  ?>
        <a class="main_menu_default_button border hvr-float <?php echo isActive('home', $current_page); ?>" onclick="window.location.href='?p=home'"><?php print phrase_news ?></a>  
        <a class="main_menu_default_button border hvr-float <?php echo isActive('register', $current_page); ?>" onclick="window.location.href='?p=register'"> <?php print phrase_register ?></a>         
        <a class="main_menu_default_button border hvr-float <?php echo isActive('downloads', $current_page); ?>" onclick="page('downloads')"><?php print phrase_download ?></a>		    
        <a class="main_menu_default_button border hvr-float <?php echo isActive('statistics', $current_page); ?>" onclick="page('statistics')"><?php print phrase_statistic ?></a>       
        <a class="main_menu_default_button border hvr-float <?php echo isActive('information', $current_page); ?>" onclick="page('information')"><?php print phrase_information ?></a>	
        <a class="main_menu_default_button border hvr-float <?php echo isActive('topchars', $current_page); ?>" onclick="window.location.href='?p=topchars'"><?php print phrase_ranking ?></a>          
        <a class="main_menu_default_button border hvr-float <?php echo isActive('market', $current_page); ?>" onclick="window.location.href='?p=market'"><?php print phrase_market ?></a>              
		<a class="main_menu_default_button border hvr-float <?php echo isActive('auction', $current_page); ?>" onclick="window.location.href='?p=auction'"><?php print phrase_auction ?></a>            
			 
<?php  }   }?>
