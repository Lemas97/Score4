<?php

    class Player {

        private $playerID;
        private $username;
        private $password;

        public function __construct($username,$password)
        {
            $this->username = $username;
            $this->password = $password;
            $this->playerID = uniqid();

            global $mysqli;
            $sql = "insert into player (playerID , username, password) values ('$this->playerID','$this->username', '$this->password')";
            if ($mysqli->query($sql) == true){
                echo "New record created successfully";
            } else {
                echo "Error: " . $sql . "<br>" . $mysqli->error;
            }

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
    }

?>