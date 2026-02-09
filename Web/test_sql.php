<?php
$serverName = "mu-sqlserver"; 
$connectionInfo = array( "Database"=>"MuOnline", "UID"=>"sa", "PWD"=>"Abcd@1234!", "TrustServerCertificate"=>true);
$conn = sqlsrv_connect( $serverName, $connectionInfo);

if( $conn ) {
     echo "Connection established.<br />";
     $query = sqlsrv_query($conn, "SELECT @@VERSION");
     $row = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC);
     echo "SQL Server Version: " . $row[''];
} else {
     echo "Connection could not be established.<br />";
     die( print_r( sqlsrv_errors(), true));
}
?>