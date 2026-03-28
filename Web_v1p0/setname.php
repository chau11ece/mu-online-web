<?php
@session_start();
require_once($_SERVER['DOCUMENT_ROOT']."/configs/config.php");

$name = base64_encode("MU Da Nang");
$welcome = base64_encode("Welcome to MU Da Nang!");

mssql_query("UPDATE DTweb_settings SET title='".$name."', server_name='".$name."', description='".$welcome."' WHERE id=(SELECT TOP 1 id FROM DTweb_settings)");

echo "<b>Done.</b> Server name set to 'MU Da Nang'. Delete this file now.";
?>
