<?php 

    class AdminPlayer extends Player{
        
    public $type;

    public function __construct($name,$username,$level,$score,$type){
        $this->type = $type;
        parent::__construct($name,$username,$level,$score);

    }

    public function banPlayer($player){
        $player->banned = true;
    }

    public function unbanPlayer(){
        $player->banned = false;
    }

}

?>