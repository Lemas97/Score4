<?php 
    class Player {
        public $name;
        public $username;
        public $level;
        private $score;
        public $banned = false;

        public function __construct($name,$username,$password){
            $this->name = $name;
            $this->username = $username;
            $this->level = 1;
            $this->score = 0;
        }
        

        public function fullname(){
            echo "$this->name ($this->username) <br>";
        }

        public function getScore() {
            return $this->score;
        }
        public function setScore($score){
            $this->score = $score;
            $this->leve = ceil($this->score / 1000);
        }


    }

?>