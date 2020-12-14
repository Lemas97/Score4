<?php

    $board = array(
        array('.','.','.','.','.','.'),
        array('.','.','.','.','.','.'),
        array('.','.','.','.','.','.'),
        array('.','.','.','.','.','.'),
        array('.','.','.','.','.','.'),
        array('.','.','.','.','.','.'),
        array('.','.','.','.','.','.'),
    );
    $playing='r';
    $moves=0;
    $winFlag=false;
    $top = array(0,0,0,0,0,0,0);
    show_board($board);

    while($moves<42 and $winFlag==false){

        if($playing=='r') {
            $playing = 'y';
        }else{
            $playing='r';
        }

        $moves++;
        $x=0;
        $top[$x]++;

        if($moves>=7){
            $winFlag = isWinningMove($board,$x,$top,$playing);
        }
        if ($winFlag){
            echo "$playing is the winner!";
        }
    }



    function horizontal($board,$x,$top,$playing){
        $streak=0;
        $i=$x;
        while ($board[$i][$top[$x]] == $playing and $i>=0){
            $streak++;
            $i--;
        }

        $i=$x+1;
        while ($board[$i][$top[$x]] == $playing and $i<7){
            $streak++;
            $i++;
        }
        return streakFlag($streak);
    }




?>



