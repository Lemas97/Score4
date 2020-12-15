<?php
require_once "dbconnect.php";
    class Board {

        private $board;
        private $topOfEachColumn;

        public function __construct(){
            global $mysqli;

            $sql = 'select * from board';
            $result = $mysqli->query($sql);
            foreach ($result as $value){
                $i = $value["x"];
                $y = $value["y"];
                $board[$i][$y] = $value["piece_color"];

            }
            $this->topOfEachColumn = array(0,0,0,0,0,0,0);
        }

        function move($color, $x){
            global $mysqli;
            $this->topOfEachColumn[$x]++;
            $sql = "update board set piece_color =  $color where x = $x and y = $this->topOfEachColumn[$x]";
            $mysqli->query($sql);

        }
        private function updateTop(){

        }

        private function isWinningMove($x, $color){
            $flag = false;
            if($this->topOfEachColumn[$x]>=4) {
                $flag = $this->vertical( $x , $color);
            }
            if ($flag==false){
                $flag = $this->backDia($x,$color);
            }
            if ($flag==false){
                $flag = $this->frontDia($x,$color);
            }
            if ($flag==false){
                $flag = $this->horizontal($x,$color);
            }

            return $flag;
        }


        private function vertical($x,$playing){
            $streak=0;
            $i=$this->topOfEachColumn[$x];
            while ($this->board[$x][$i] == $playing and $i>=0){
                $streak++;
                $i--;
            }
            return streakFlag($streak);
        }

        private function backDia($x,$playing){
            $streak=0;
            $i=$x;
            $y=$this->topOfEachColumn[$x];
            while ($this->board[$x][$i] == $playing and $i<7  and $y<6){
                $streak++;
                $i++;
                $y++;
            }
            $i=$x -1 ;
            $y=$this->topOfEachColumn[$x] - 1;
            while ($this->board[$x][$i] == $playing and $i>=0 and $y>=0){
                $streak++;
                $i--;
                $y--;
            }
            return $this->streakFlag($streak);
        }

        private function frontDia($x,$playing){
            $streak=0;
            $i=$x;
            $y=$this->topOfEachColumn[$x];
            while ($this->board[$x][$i] == $playing and $i<7 and $y>=0){
                $streak++;
                $i++;
                $y--;
            }
            $i=$x -1;
            $y=$this->topOfEachColumn[$x] + 1;
            while ($this->board[$x][$i] == $playing and $i>=0 and $y<6){
                $streak++;
                $i--;
                $y++;
            }

            return streakFlag($streak);
        }

        private function show_board(){
            echo "<center> <table style='width: 10%';>";
            for ($i = 0; $i < 7; $i++) {
                echo "<tr style='width: 30%;'>";
                for ($y = 0; $y < 6; $y++) {
                    echo "<td>" . $this->board[$i][$y] . "</td>";
                }
                echo "</tr>";
            }
            echo "</table> </center>";
            echo '<form method="post" action="">
        <input type="text" name="value">
        <input type="submit">
        </form>';

        }
        private function streakFlag($streak){
            if ($streak>=4){
                return true;
            }else{
                return false;
            }
        }
        private function lastMoveColor(){
        }
        function getBoard($x,$y){
            return $this->board[$x][$y];

        }

    }
?>