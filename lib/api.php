<?php

if (isset($_SERVER['HTTP_ORIGIN'])) {
    // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
    // you want to allow, and if so:
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        // may also be using PUT, PATCH, HEAD etc
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}


require_once "board.php";
require_once "player.php";
session_start();

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));
$input = json_decode(file_get_contents('php://input'), true);
$r = array_shift($request);

if ($r == 'resetboard' && $method == 'POST') {
    $_SESSION['board'] = new Board(); //Αρχικοποίηση board σε Session για να μην το χάσω
    $_SESSION['playing'] = 'R'; //Παίζει πρώτος ο κόκκινος

} elseif ($r == 'makemove' && $method == 'POST') {
    if(isset($_SESSION[$input['username']]) && $_SESSION[$input['username']]->checkStatus()) {
        $userColor = $_SESSION[$input['username']]->getColor();
        $x = $input["x"]; //Αποθηκεύεται η στήλη που έγινε η κίνηση
        $turn = $_SESSION['board']->checkTurn(); //Παίρνει το color και το username που έχει σειρά
        if ($turn['color'] == $userColor) { //Αν το χρώμα είναι ίδιο με το χρώμα της σειράς
            $board = $_SESSION["board"];
            $y = $board->checkTopOfX($x); //Έλεγχος αν επιτρέπεται η κίνηση λόγω ύψους στήλης
            if ($y) {
                $winFlag = $board->move(strtoupper($userColor), $x); //Γίνεται η κίνηση
                $board->updateNextTurn();
                $_SESSION["board"] = $board; //Σώζεται το board μετά την κίνηση
                $board->show_board(); //Εμφανίζει με html το board ώστε να φαίνεται η κίνηση επειδή δεν έχω ui

                if ($winFlag) { //Αν η κίνηση είναι νικητήρια εμφάνισε τον νικητή και κάνε reset το board
                    if ($input['outputType'] == "json") {//Για λόγους debugging και κατανόησης του board τον εμφανίζω κατάλληλα
                        print json_encode($board->getBoard(), JSON_UNESCAPED_UNICODE);
                    }
                    print json_encode(['winmesg' => "The winner is " . $input['username'] . "!"],JSON_UNESCAPED_UNICODE);
                    $_SESSION['board'] = new Board();
                }

            } else {
                //Αν η στήλη x που επιλέχθηκε είναι γεμάτη
                header("HTTP/1.1 400 Bad Request");
                header("Content-type: application/json; charset=utf-8");
                print json_encode(['errormesg' => "Δεν μπορείς να τοποθετήσεις στο $x"],JSON_UNESCAPED_UNICODE);
            }
        } else {
            //Αν το χρώμα του παίκτη που παίζει δεν αντιστοιχεί στο δικό σου
            header("HTTP/1.1 400 Bad Request");
            header("Content-type: application/json; charset=utf-8");
            print json_encode(['errormesg' => "Δεν είναι η σειρά σου. Σειρά έχει ο " . $turn['username']],JSON_UNESCAPED_UNICODE);
        }
    }else{
        //Αν προσπαθήσει να κάνει κίνηση χωρίς να είναι συνδεδεμένος
        header("HTTP/1.1 400 Bad Request");
        header("Content-type: application/json; charset=utf-8");
        print json_encode(['errormesg' => "Δεν είσαι συνδεδεμένος και δεν μπορείς να εκτελέσεις αυτήν την λειτουργία!"],JSON_UNESCAPED_UNICODE);
    }
} elseif ($r == 'showboard' && $method == 'GET') {

    $board = new $_SESSION("board") ;

    if ($_GET['outputType'] == "json") {//Για λόγους debugging και κατανόησης του board εμφανίζεται κατάλληλα
        print json_encode($board->getBoard(),JSON_UNESCAPED_UNICODE);
    } else {
        $board->show_board();
    }

} elseif ($r == 'login' && $method == 'POST') {

    if(!isset($input['username']) or !isset($input['password'])) {
        header("HTTP/1.1 400 Bad Request");
        header("Content-type: application/json; charset=utf-8");
        print json_encode(['errormesg'=>"Δεν έδωσες username ή password."],JSON_UNESCAPED_UNICODE);
        exit;
    }else {
        //Αν δόθηκε password και username
        $username = $input['username'];
        $password = $input['password'];
        //$_SESSION[$username] = new Player($username,$password);
        $player = new Player($username,$password);

        header("HTTP/1.1 200 OK");
        header("Content-type: application/json; charset=utf-8");
        print json_encode(["loginStatus" => "Συνδέθηκες με επιτυχία!", "color" => $player->getColor()], JSON_UNESCAPED_UNICODE);
        exit;

    }

} elseif ($r == 'logout' && $method == 'GET') {
    $username = $_GET['username'];

    if(isset($_SESSION[$username]) && $_SESSION[$username]->checkStatus()) {
        //Αν υπάρχει στο Session το username και είναι συνδεδεμένο το username κάνει logout
        $_SESSION[$username]->logout();
    }else{
        if(isset($_SESSION[$username])) {
            header("HTTP/1.1 400 Bad Request");
            header("Content-type: application/json; charset=utf-8");
            print json_encode(['errormesg' => "Δεν είσαι συνδεδεμένος και δεν μπορείς να εκτελέσεις αυτήν την λειτουργία!"],JSON_UNESCAPED_UNICODE);
        }else{
            header("HTTP/1.1 400 Bad Request");
            header("Content-type: application/json; charset=utf-8");
            print json_encode(['errormesg'=>"Δεν έδωσες username."],JSON_UNESCAPED_UNICODE);

        }
    }
} else {
    header("HTTP/1.1 400 Bad Request");
}
exit;

?>