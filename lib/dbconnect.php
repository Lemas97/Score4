<?php
$host='localhost';
$db = 'score4';
require_once "dbdetails.php";

$user=$DB_USER;
$pass=$DB_PASS;

global $mysqli;

    if(gethostname()=='users.iee.ihu.gr') {
        $mysqli = new mysqli($host, $user, $pass, $db,null,'/home/student/it164630/2016/mysql/run/mysql.sock');
    } else {
            $mysqli = new mysqli($host, $user, $pass, $db);
    }

    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" .
        $mysqli->connect_errno . ") " . $mysqli->connect_error;
    }
?>
