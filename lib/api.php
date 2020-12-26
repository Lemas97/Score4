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
    $color = $input["color"]; //Αποθηκεύεται το χρώμα που έπαιξε
    $x = $input["x"]; //Αποθηκεύεται η στήλη που έγινε η κίνηση
    if ($_SESSION['playing'] == $color) { //Αν το χρώμα είναι ίδιο με το χρώμα της σειράς
        $board = $_SESSION["board"];
        $y = $board->checkTopOfX($x); //Έλεγχος αν επιτρέπεται η κίνηση λόγω ύψους στήλης
        if ($y) {
            $winFlag = $board->move(strtoupper($color), $x); //Γίνεται η κίνηση
            $_SESSION["board"] = $board; //Σώζεται το νέο board
            if ($winFlag) {

                if ($input['outputType'] == "json") {
                    print json_encode(['winmesg' => "Κέρδισε ο ". $_SESSION[$_SESSION['playing']]."!"]);
                } else {
                    echo "Κέρδισε ο ". $_SESSION[$_SESSION['playing']]."!";
                }

                $_SESSION['board'] = new Board();
            }

            if ($_SESSION['playing'] == 'R') {
                $_SESSION['playing'] = 'Y';
            } else {
                $_SESSION['playing'] = 'R';
            }
        } else {
            if ($input["outputType"] == 'json') {
                print json_encode(['errormesg' => "Δεν μπορείς να τοποθετήσεις στο $x"]);
            } else {
                echo "Δεν μπορείς να τοποθετήσεις στο $x";
            }
        }
    } else {
        if ($input["outputType"] == 'json') {
            print json_encode(['errormesg' => "Δεν είναι η σειρά σου. Σειρά έχει ο " . $_SESSION[$_SESSION['playing']]]);
        } else {

            echo "Δεν είναι η σειρά σου. Σειρά έχει ο ". $_SESSION[$_SESSION['playing']] ;
        }

    }

} elseif ($r == 'showboard' && $method == 'GET') {
    $board = $_SESSION['board'];
    if ($input['outputType'] == "json") {
        print json_encode($board->getBoard(), JSON_PRETTY_PRINT);
    } else {
        $board->show_board();
    }

} elseif ($r == 'login' && $method == 'GET') {
    global $mysqli;
    $username = $input['username'];
    $password = $input['password'];

    $sql = "select * from player where username = '$username'";
    $result = $mysqli->query($sql);
    $c = 'R';

    foreach ($result as $value) {

        if (isset($value["username"])) {

            if ($password == $value['password']) {

                $sql = "select * from logedin";
                $logedin = $mysqli->query($sql);

                if($logedin->num_rows == 1){
                    foreach ($logedin as $value2 ){
                        if($value2['username'] == $username){
                            echo "Είσαι ήδη συνδεδεμένος";
                        }else{
                            if($value2['color']=='R'){
                                $c='Y';
                            }
                            $playerID = $value['playerID'];
                            $sql = "insert into logedin (username,color) values('$username','$c')";
                            $mysqli->query($sql);

                            $_SESSION[$c] = $username;
                            $_SESSION[$username] = new Player($username, $password, $playerID, $c);
                            echo 'Συνδέθηκες με επιτυχία!';

                        }
                    }

                }else{
                    $playerID = $value['playerID'];
                    $sql = "insert into logedin (username,color) values('$username','$c')";
                    $mysqli->query($sql);

                    $_SESSION[$c] = $username;
                    $_SESSION[$username] = new Player($username, $password, $playerID, $c);
                    echo 'Συνδέθηκες με επιτυχία!';


                }



            }


        } else {
            echo "Λάθος password!";
        }
    }


} elseif ($r == 'logout' && $method == 'GET') {
    global $mysqli;
    $username = $input['username'];
    $player = $_SESSION[$username];

    if ($player->getUsername() == $username) {

        $sql = "delete from logedin where username = '$username'";
        $mysqli->query($sql);

        unset($_SESSION[$username]);
        echo "Αποσυνδέθηκες επιτυχώς!";
    }


} else {
    header("HTTP/1.1 404 Not Found");
}

exit;

?>