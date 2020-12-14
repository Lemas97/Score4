<?php
require_once "board.php";
session_start();

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));
$input = json_decode(file_get_contents('php://input'), true);
$r = array_shift($request);




if ($r == 'resetboard'){

    $_SESSION['board']=new Board();

}elseif ($r == 'makemove') {
    $board = $_SESSION["board"];
    $color = $input["color"];
    $x = $input["x"];
    $board->move($color, $x);
    $timi = $board->getBoard(1,1);
    echo "$timi";



} elseif ($r == 'showboard') {
    echo 'reset';

} else {
    header("HTTP/1.1 404 Not Found");
}
exit;

?>