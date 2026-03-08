<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<?php
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {header("Location:../error.php");}else{
include($_SERVER['DOCUMENT_ROOT']."/configs/config.php");
$set = web_settings();

if($set[3] != "Aion"){  ?>

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
        <?php
        $cp = isset($_GET['p']) ? $_GET['p'] : 'home';
        function mu_active($pages, $cp) { return in_array($cp, (array)$pages) ? ' mu-active' : ''; }
        ?>
        <a class="main_menu_default_button border hvr-float<?php echo mu_active('home',$cp)?>" href="?p=home"><?php print phrase_news ?></a>
        <a class="main_menu_default_button border hvr-float<?php echo mu_active('information',$cp)?>" href="?p=information"><?php print phrase_information ?></a>
        <a class="main_menu_default_button border hvr-float<?php echo mu_active('register',$cp)?>" href="?p=register"><?php print phrase_register ?></a>
        <a class="main_menu_default_button border hvr-float<?php echo mu_active('files',$cp)?>" href="?p=files"><?php print phrase_download ?></a>

        <div class="mu-dropdown<?php echo mu_active(['topchars','topguilds','topkillers','topelf','topdl','topmg','topbk','topsm','hof','onlinenow','topbc','topds','topsky','mostonline'],$cp)?>">
            <a class="main_menu_default_button border hvr-float mu-dropdown-toggle"><?php print phrase_ranking ?> &#9662;</a>
            <div class="mu-dropdown-menu">
                <a href="?p=topchars">&#9670; <?php print phrase_top_chars?></a>
                <a href="?p=topguilds">&#9670; <?php print phrase_top_guilds?></a>
                <a href="?p=topkillers">&#9670; <?php print phrase_top_killers?></a>
                <a href="?p=hof">&#9670; Hall of Fame</a>
                <a href="?p=onlinenow">&#9670; Online Now</a>
                <div class="mu-dropdown-divider"></div>
                <a href="?p=topbc">&#9670; Blood Castle</a>
                <a href="?p=topds">&#9670; Devil Square</a>
                <a href="?p=topsky">&#9670; Sky Event</a>
            </div>
        </div>

        <a class="main_menu_default_button border hvr-float<?php echo mu_active('market',$cp)?>" href="?p=market"><?php print phrase_market ?></a>
        <a class="main_menu_default_button border hvr-float<?php echo mu_active('auction',$cp)?>" href="?p=auction"><?php print phrase_auction ?></a>

<?php  }   }?>