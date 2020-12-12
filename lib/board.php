<?php
require_once "lib/dbconnect.php";

    class Board {

        public function __construct(){
            global $mysqli;


        }
        function show_board()
        {
            global $mysqli;
            $sql = 'select * from board';
            $st = $mysqli->prepare($sql);

            $st->execute();
            $res = $st->get_result();
            header('Content-type: application/json');
            print json_ecnode($res->fetch_all(MYSQLI_ASSOC), JSON_PRETTY_PRINT);

        }

        function reset_board()
        {
            global $mysqli;
            $sql = 'call clean board';
            $mysqli->query($sql);
            show_board();
        }
    }
?>