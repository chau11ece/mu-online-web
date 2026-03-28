<?php
/**
 * Aion Theme for DT Web 2.0 - Twig Template Version (with debug)
 * 
 * This theme uses Twig for the base layout while maintaining
 * compatibility with the existing DT Web content system.
 */

// Protect from direct access
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    header("Location:../../error.php");
    exit();
}

// Check if Twig is enabled (check both getenv and $_ENV for Docker compatibility)
// TEMPORARILY DISABLED - Uncomment to enable Twig
$use_twig = false; // (getenv('USE_TWIG') === 'true') || (isset($_ENV['USE_TWIG']) && $_ENV['USE_TWIG'] === 'true');

// If Twig is NOT enabled, use original PHP rendering
if (!$use_twig) {
    render_original_theme();
    exit;
}

// ============================================
// TWIG RENDERING PATH - WITH DEBUG
// ============================================

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Load Twig initialization
    require_once __DIR__ . '/twig_init.php';
    
    // Get current page
    $current_page = isset($_GET['p']) ? $_GET['p'] : 'home';
    
    // Get website settings (already base64 decoded by web_settings function)
    $set = web_settings();
    
    // Use values directly (web_settings already returns decoded values)
    $page_title = !empty($set[0]) ? $set[0] : 'MU Online';
    $meta_description = !empty($set[2]) ? $set[2] : 'MuOnline Server';
    $meta_keywords = !empty($set[1]) ? $set[1] : 'mu online, game server';
    $server_name = !empty($set[4]) ? $set[4] : 'MU Online';
    
    // Theme URL
    $theme_url = 'themes/Aion';
    
    // Capture language form output
    ob_start();
    if (function_exists('lang_form')) {
        echo lang_form();
    }
    $lang_form = ob_get_clean();
    
    // Capture sidebar content
    ob_start();
    include "menus/social_media.php";
    include "menus/login_form.php";
    include "inc/ranks.php";
    $sidebar = ob_get_clean();
    
    // Capture main content based on current page
    ob_start();
    if (isset($_GET['p'])) {
        switch ($_GET['p']) {
            case 'home':
                include "menus/main_page.php";
                break;
            default:
                include "inc/loader.php";
                break;
        }
    } else {
        include "menus/main_page.php";
    }
    $content = ob_get_clean();
    
    // Prepare phrases for Twig (global language variables)
    $phrases = [
        'phrase_server_time' => isset($phrase_server_time) ? $phrase_server_time : 'Server Time',
        'phrase_loading' => isset($phrase_loading) ? $phrase_loading : 'Loading...',
        'phrase_news' => isset($phrase_news) ? $phrase_news : 'News',
        'phrase_register' => isset($phrase_register) ? $phrase_register : 'Register',
        'phrase_download' => isset($phrase_download) ? $phrase_download : 'Download',
        'phrase_statistic' => isset($phrase_statistic) ? $phrase_statistic : 'Statistics',
        'phrase_information' => isset($phrase_information) ? $phrase_information : 'Information',
        'phrase_ranking' => isset($phrase_ranking) ? $phrase_ranking : 'Ranking',
        'phrase_market' => isset($phrase_market) ? $phrase_market : 'Market',
        'phrase_auction' => isset($phrase_auction) ? $phrase_auction : 'Auction',
        'phrase_rules' => isset($phrase_rules) ? $phrase_rules : 'Rules',
        'phrase_banned' => isset($phrase_banned) ? $phrase_banned : 'Banned List',
        'phrase_warned' => isset($phrase_warned) ? $phrase_warned : 'Warned List',
        'phrase_term_of_service' => isset($phrase_term_of_service) ? $phrase_term_of_service : 'Terms of Service',
        'phrase_privacy' => isset($phrase_privacy) ? $phrase_privacy : 'Privacy Policy',
        'phrase_cotacts' => isset($phrase_cotacts) ? $phrase_cotacts : 'Contacts',
    ];
    
    // Render Twig template
    $twig_data = [
        'page_title' => $page_title,
        'meta_description' => $meta_description,
        'meta_keywords' => $meta_keywords,
        'server_name' => $server_name,
        'theme_url' => $theme_url,
        'content' => $content,
        'sidebar' => $sidebar,
        'lang_form' => $lang_form,
        'phrases' => $phrases,
    ];
    
    echo render_template('base', $twig_data);
    
} catch (Exception $e) {
    // If Twig fails, fall back to original theme
    echo "<!-- Twig Error: " . $e->getMessage() . " -->\n";
    render_original_theme();
} catch (Error $e) {
    // Catch PHP errors
    echo "<!-- Twig Error: " . $e->getMessage() . " -->\n";
    render_original_theme();
}

function render_original_theme() {
    $set = web_settings();
    echo '
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <link rel="icon" type="ico" href="themes/aion/favicon.ico"/>	
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta content="'.$set[2].'" name="description"/>
        <meta name="keywords" content="'.$set[1].'"/>
        <title>'.$set[0].'</title>
        <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css"/>	
        <link rel="stylesheet" href="themes/Aion/css/style.css" />
        <link rel="stylesheet" href="themes/Aion/css/hover.css" media="all"/>
        <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet" media="all"/>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700;900&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="themes/Aion/css/custom.css" />
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
        <script type="text/javascript" src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="js/servertime.js"></script>
        <script type="text/javascript" src="js/ajax.js"></script>
    </head>	
    <body>
        <div id="wrapper"> 
             <div id="container">
              <div id="top_menu">
                  <div class="server-time">'.phrase_server_time.':</div><div id="timer"> '.phrase_loading.'</div>
                  '.lang_form().'
             </div>
               <!-- Logo removed - using header image instead -->
                   <div class="menu list-inline ">'; 
             include ("inc/menu.php"); 
             echo'
             </div>  
                  <div id="contents">
                    <div id="panel_left">';		
               include("menus/social_media.php");
               include("menus/login_form.php");	
               include("inc/ranks.php");	
               echo'</div>		   
               <div id="panel_right">
                  <div id="content">';
                      if(isset($_GET['p'])){
                           switch($_GET['p'])	{
                           case "home":include("menus/main_page.php");	break;						
                           default:include("inc/loader.php");break;
                        }				
                      }	 				  
                      else{
                          include("menus/main_page.php");					 
                      }
                      echo'
                </div> 
                </div>	
              </div>		
           </div>	
       <div id="footer">		    
            <div class="footer_links">
               <a href="#"> '.phrase_rules.' </a>&nbsp;  | &nbsp;     	      
               <a href="#"> '.phrase_banned.' </a>&nbsp;  | &nbsp;
               <a href="#"> '.phrase_warned.'</a>&nbsp;  | &nbsp;		  
               <a href="#"> '.phrase_term_of_service.' </a>&nbsp; | &nbsp;
               <a href="#"> '.phrase_privacy.' </a>&nbsp;  | &nbsp;
               <a href="#"> '.phrase_cotacts.' </a> 
            </div>
        <div class="footer_info"> '.$set[0].' by r00tme | Copyrights &copy; MeMoS</div>
     </div>	 	
       </div>			
      </body>
      </html>
    ';
}
