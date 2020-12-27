<?php
require_once "board.php";
require_once "player.php";
session_start();

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));
$input = json_decode(file_get_contents('php://input'), true);
$r = array_shift($request);

if ($r == 'resetboard' && $method == 'GET') {

    $_SESSION['board'] = new Board(); //Αρχικοποίηση board σε Session για να μην το χάσω
    $_SESSION['playing'] = 'r'; //Παίζει πρώτος ο κόκκινος

} elseif ($r == 'makemove' && $method == 'POST') {
    if(isset($_SESSION[$input['username']]) && $_SESSION[$input['username']]->checkStatus()) {
        $userColor = $_SESSION[$input['username']]->getColor();
        $x = $input["x"]; //Αποθηκεύεται η στήλη που έγινε η κίνηση
        if ($_SESSION['playing'] == $userColor) { //Αν το χρώμα είναι ίδιο με το χρώμα της σειράς
            $board = $_SESSION["board"];
            $y = $board->checkTopOfX($x); //Έλεγχος αν επιτρέπεται η κίνηση λόγω ύψους στήλης
            if ($y) {
                $winFlag = $board->move(strtoupper($userColor), $x); //Γίνεται η κίνηση
                $_SESSION["board"] = $board; //Σώζεται το νέο board
                if ($winFlag) {
                    print json_encode(['winmesg' => "Κέρδισε ο " . $input['username'] . "!"]);
                    $_SESSION['board'] = new Board();
                }

                if ($_SESSION['playing'] == 'R') {
                    $_SESSION['playing'] = 'Y';
                } else {
                    $_SESSION['playing'] = 'R';
                }
            } else {
                print json_encode(['errormesg' => "Δεν μπορείς να τοποθετήσεις στο $x"]);
            }
        } else {
            print json_encode(['errormesg' => "Δεν είναι η σειρά σου. Σειρά έχει ο " . $_SESSION[$_SESSION['playing']]]);
        }
    }else{
        print json_encode(['errormesg' => "Δεν είσαι συνδεδεμένος και δεν μπορείς να εκτελέσεις αυτήν την λειτουργία!"]);
    }
} elseif ($r == 'showboard' && $method == 'GET') {
    $board = $_SESSION['board'];
    if ($input['outputType'] == "json") {
        print json_encode($board->getBoard(), JSON_PRETTY_PRINT);
    } else {
        $board->show_board();
    }

} elseif ($r == 'login' && $method == 'GET') {

    if(!isset($input['username']) or !isset($input['password'])) {
        header("HTTP/1.1 400 Bad Request");
        print json_encode(['errormesg'=>"Δεν έδωσες username ή password."]);
        exit;
    }else {
        $username = $input['username'];
        $password = $input['password'];

        $_SESSION[$username] = new Player($username,$password);
    }

} elseif ($r == 'logout' && $method == 'GET') {
    if(isset($_SESSION[$input['username']]) && $_SESSION[$input['username']]->checkStatus()) {

        $_SESSION[$input['username']]->logout();




    }else{
        print json_encode(['errormesg' => "Δεν είσαι συνδεδεμένος και δεν μπορείς να εκτελέσεις αυτήν την λειτουργία!"]);
    }
} else {
    header("HTTP/1.1 404 Not Found");
}

exit;

?>