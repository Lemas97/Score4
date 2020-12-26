<?php

    class Player {

        private $playerID;
        private $username;
        private $password;

        private $color;

        public function __construct($username,$password, $playerID,$color)
        {
            $this->username = $username;
            $this->password = $password;
            $this->playerID = $playerID;
            $this->color = $color;

        }

        function show_player($b) {
            global $mysqli;
            $sql = 'select username,piece_color from players where piece_color=?';
            $st = $mysqli->prepare($sql);
            $st->bind_param('s',$b);
            $st->execute();
            $res = $st->get_result();
            header('Content-type: application/json');
            print json_encode($res->fetch_all(MYSQLI_ASSOC), JSON_PRETTY_PRINT);
        }

        function setColor($c){
            $this->color = $c;
        }

        function getColor(){
            return $this->color;
        }

        function getUsername(){
            return $this->username;
        }
    }

?>