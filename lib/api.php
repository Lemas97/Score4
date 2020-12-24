<?php
require_once "board.php";
session_start();

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));
$input = json_decode(file_get_contents('php://input'), true);
$r = array_shift($request);

if ($r == 'resetboard' && $method == 'GET'){

    $_SESSION['board']=new Board(); //Αρχικοποίηση board σε Session για να μην το χάσω
    $_SESSION['playing']='r'; //Παίζει πρώτος ο κόκκινος

}elseif ($r == 'makemove' && $method == 'POST' ) {
    $color = $input["color"]; //Αποθηκεύεται το χρώμα που έπαιξε
    $x = $input["x"]; //Αποθηκεύεται η στήλη που έγινε η κίνηση
    if($_SESSION['playing']==$color){ //Αν το χρώμα είναι ίδιο με το χρώμα της σειράς
        $board = $_SESSION["board"];
        $y = $board->checkTopOfX($x); //Έλεγχος αν επιτρέπεται η κίνηση λόγω ύψους στήλης
        if($y) {
            $winFlag = $board->move(strtoupper($color), $x); //Γίνεται η κίνηση
            $_SESSION["board"] = $board; //Σώζεται το νέο board
            if($winFlag){

                if($input['outputType'] == "json"){
                    print json_encode(['winmesg'=>"Κέρδισε ο $color"]);
                }else {
                    echo "Κέρδισε ο $color";
                }

                $_SESSION['board']=new Board();
            }

            if ($_SESSION['playing'] == 'r') {
                $_SESSION['playing'] = 'y';
            } else {
                $_SESSION['playing'] = 'r';
            }
        }else {
            if ($input["outputType"]=='json'){
                print json_encode(['errormesg'=>"Δεν μπορείς να τοποθετήσεις στο $x"]);
            }else {
                echo "Δεν μπορείς να τοποθετήσεις στο $x";
            }
        }
    }else{
        if ($input["outputType"]=='json'){
            print json_encode(['errormesg'=>"Δεν είναι η σειρά σου. Σειρά έχει ο ".$_SESSION['playing']]);
        }else {
            echo "Δεν είναι η σειρά σου. Σειρά έχει ο ".$_SESSION['playing'];
        }

    }

} elseif ($r == 'showboard' && $method == 'GET') {
    $board = $_SESSION['board'];
    if($input['outputType'] == "json"){
        print json_encode($board->getBoard(),JSON_PRETTY_PRINT);
    }else {
        $board->show_board();
    }

} else {
    header("HTTP/1.1 404 Not Found");
}

exit;

?>