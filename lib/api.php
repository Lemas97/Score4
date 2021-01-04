<?php
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
        if ($_SESSION['board']->checkTurn() == $userColor) { //Αν το χρώμα είναι ίδιο με το χρώμα της σειράς
            $board = $_SESSION["board"];
            $y = $board->checkTopOfX($x); //Έλεγχος αν επιτρέπεται η κίνηση λόγω ύψους στήλης
            if ($y) {
                $winFlag = $board->move(strtoupper($userColor), $x); //Γίνεται η κίνηση
                $board->updateNextTurn();
                $_SESSION["board"] = $board; //Σώζεται το board μετά την κίνηση

                if ($winFlag) { //Αν η κίνηση είναι νικητήρια εμφάνισε τον νικητή και κάνε reset το board
                    if ($_GET['outputType'] == "json") {//Για λόγους debugging και κατανόησης του board τον εμφανίζω κατάλληλα.
                        print json_encode($board->getBoard(), JSON_PRETTY_PRINT);
                    } else {
                        $board->show_board();
                    }
                    print json_encode(['winmesg' => "The winner is " . $input['username'] . "!"]);
                    $_SESSION['board'] = new Board();
                }

            } else {
                //Αν η στήλη x που επιλέχθηκε είναι γεμάτη
                header("HTTP/1.1 400 Bad Request");
                print json_encode(['errormesg' => "Δεν μπορείς να τοποθετήσεις στο $x"]);
            }
        } else {
            //Αν το χρώμα του παίκτη που παίζει δεν αντιστοιχεί στο δικό σου
            header("HTTP/1.1 400 Bad Request");
            print json_encode(['errormesg' => "Δεν είναι η σειρά σου. Σειρά έχει ο " . $_SESSION[$_SESSION['playing']]]);
        }
    }else{
        //Αν προσπαθήσει να κάνει κίνηση χωρίς να είναι συνδεδεμένος
        header("HTTP/1.1 400 Bad Request");
        print json_encode(['errormesg' => "Δεν είσαι συνδεδεμένος και δεν μπορείς να εκτελέσεις αυτήν την λειτουργία!"]);
    }
} elseif ($r == 'showboard' && $method == 'GET') {

    $board = $_SESSION['board'];
    if ($_GET['outputType'] == "json") {//Για λόγους debugging και κατανόησης του board τον εμφανίζω κατάλληλα.
        print json_encode($board->getBoard(), JSON_PRETTY_PRINT);
    } else {
        $board->show_board();
    }

} elseif ($r == 'login' && $method == 'POST') {

    if(!isset($input['username']) or !isset($input['password'])) {
        header("HTTP/1.1 400 Bad Request");
        print json_encode(['errormesg'=>"Δεν έδωσες username ή password."]);
        exit;
    }else {
        //Αν δόθηκε password και username
        $username = $input['username'];
        $password = $input['password'];
        $_SESSION[$username] = new Player($username,$password);
    }

} elseif ($r == 'logout' && $method == 'GET') {
    $username = $_GET['username'];
    if(isset($_SESSION[$username]) && $_SESSION[$username]->checkStatus()) {
        //Αν υπάρχει στο Session το username και είναι συνδεδεμένο το username κάνει logout
        $_SESSION[$username]->logout();
    }else{
        if(isset($_SESSION[$username])) {
            header("HTTP/1.1 400 Bad Request");
            print json_encode(['errormesg' => "Δεν είσαι συνδεδεμένος και δεν μπορείς να εκτελέσεις αυτήν την λειτουργία!"]);
        }else{
            header("HTTP/1.1 400 Bad Request");
            print json_encode(['errormesg'=>"Δεν έδωσες username."]);

        }
    }
} else {
    header("HTTP/1.1 400 Bad Request");
}

exit;

?>