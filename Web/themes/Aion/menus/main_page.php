<link type="text/css" href="../themes/Aion/css/skitter.css" media="all" rel="stylesheet" />
<script type="text/javascript" src="../themes/Aion/js/jquery.easing.1.3.js"></script>
<script type="text/javascript" src="../themes/Aion/js/jquery.skitter.js"></script>
<script type="text/javascript">
$(document).ready(function(){ $(".box_skitter_large").skitter({interval: 5000}); });
</script>

<?php
$check_banners = mssql_query("Select * from DTweb_Carousel_Settings");
$is_banner     = mssql_num_rows($check_banners);
$li            = '';
$set           = web_settings();

if ($is_banner == 0) {
    $news  = 'id="news_new"';
    $class = '';
} else {
    $news  = 'id="news"';
    $class = 'class="box_skitter border box_skitter_large"';
}

for ($i = 0, $max = mssql_num_rows($check_banners); $i < $max; $i++) {
    $banners  = mssql_fetch_array($check_banners);
    $filename = 'imgs/banners/' . $banners['filename'];
    if (file_exists($filename)) {
        $li .= '<li>
            <a href="' . base64_decode($banners['link']) . '"><img src="' . $filename . '" class="' . $banners['effect'] . '" /></a>
            <div class="label_text"><p>' . base64_decode($banners['text']) . '</p></div>
        </li>';
    }
}

$online     = mssql_fetch_array(mssql_query("SELECT count(*) as count FROM MEMB_STAT WHERE Connectstat='1'"));
$srv_online = @fsockopen($set['server_ip'], $set['server_port'], $errno, $errstr, 0.5) ? 'srv-online' : 'srv-offline';
$srv_pct    = ($set[10] > 0) ? min(round($online['count'] / $set[10] * 100), 100) : 0;

$status_html = ($srv_online === 'srv-online')
    ? '<span class="si-dot si-online"></span> Online'
    : '<span class="si-dot si-offline"></span> Offline';

echo '
<!-- ======= Server Info Hero Strip ======= -->
<div class="server-info-hero">
    <div class="si-stat">
        <div class="si-label">Status</div>
        <div class="si-value">' . $status_html . '</div>
    </div>
    <div class="si-stat">
        <div class="si-label">' . phrase_characters_online . '</div>
        <div class="si-value si-count">' . $online['count'] . ' <span class="si-max">/ ' . $set[10] . '</span></div>
        <div class="si-bar"><div class="si-bar-fill" style="width:' . $srv_pct . '%"></div></div>
    </div>
    <div class="si-stat">
        <div class="si-label">Experience</div>
        <div class="si-value si-rate">' . $set[9] . 'x</div>
    </div>
    <div class="si-stat">
        <div class="si-label">Drop Rate</div>
        <div class="si-value si-rate">' . $set[11] . '</div>
    </div>
    <div class="si-stat">
        <div class="si-label">Version</div>
        <div class="si-value">' . $set[7] . '</div>
    </div>
    <div class="si-votes">
        <div class="si-label">' . $set[23] . '</div>
        <div class="si-vote-links">
            <a href="' . $set[21] . '" target="_blank" class="si-vote-btn">XtremeTop100</a>
            <a href="' . $set[22] . '" target="_blank" class="si-vote-btn">GTop100</a>
        </div>
    </div>
</div>
<!-- ======= END Server Info Hero ======= -->

<div ' . $class . '>
    <ul>' . $li . '</ul>
</div>

<div ' . $news . '>';
include("mod/home.php");
echo '</div>';
?>
