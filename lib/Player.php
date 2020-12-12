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

        function get_username(){
            return $this->username;
        }
    }

?>