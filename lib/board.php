<?php
require_once "dbconnect.php";
    class Board {

        private $board;
        private $topOfEachColumn; //Το υψηλότερο πιόνι σε κάθε στήλη

        public function __construct(){
            global $mysqli;

            $sql = 'replace into board select * from board_empty'; //Reset του πίνακα board
            $mysqli->query($sql);

            $this->boardFillFromDB();

            $this->topOfEachColumn = array(0,0,0,0,0,0,0,0); //Αρχικοποίηση του top για κάθε στήλη

            for ($i=1; $i<=7; $i++){
                $sql = "update topofx set top=0 where x=$i"; //Reset του topofx στην βάση
                $mysqli->query($sql);

            }
        }

        private function boardFillFromDB(){
            //Γεμίζει τον πίνακα board με τα δεδομένα της βάσης
            global $mysqli;
            $sql = 'select * from board';
            $result = $mysqli->query($sql);

            foreach ($result as $value){
                $i = $value["x"];
                $y = $value["y"];

                $this->board[7-$y][$i] = $value["piece_color"];
                /* 7-$y διότι θέλω να είναι αντεστραμένος ο πίνακας ως προς το y
                ώστε να στην εμφάνηση να φαίνεται πως τα πιόνια είναι στον πάτο της στήλης*/
            }

        }

        function move($color, $x){
            global $mysqli;
            $this->updateTop($x);   //Με το που γίνεται κίνηση ανανεώνεται το top που έχει πούλι στην συγκεκριμένη στήλη του board


            //Ετοιάζεται το query για την εκχώρηση του χρώματος στην κατάλληλη θέση στο board
            $sql = "update board set piece_color = '$color' where x=$x and y=" . $this->topOfEachColumn[$x].";";
            $mysqli->query($sql); //Εκτέλεση του query

            $sql = 'select * from board';   //Ετοιμάζεται το query για να αντιγραφεί το Board της βάσης στον πίνακα board
            $result = $mysqli->query($sql);

            foreach ($result as $value){
                $i = $value["x"];
                $y = $value["y"];
                $this->board[$y][$i] = $value["piece_color"]; //Δεν χρησιμοποιή την boardFillFromDB για να μου είναι πιο εύκολη η διαδικασία ελέγχου νικήτριας κίνησης (λόγω το 7-$y)
            }

            $flagWin = $this->isWinningMove($x, $color); //flagWin = true/false ώστε να δηλώσει αν η κίνηση ήταν νικητήρια ή όχι
            return $flagWin;
        }
        private function updateTop($x){ //Update στην βάση για για το top της κατάλληλης στήλης στο board
            global $mysqli;
            $this->topOfEachColumn[$x]++;
            $sql = "update topofx set top= ".$this->topOfEachColumn[$x] ." where x=$x";
            $mysqli->query($sql);
        }

        private function isWinningMove($x, $color){
            //Έλεγχος πιθανής νικήτριας κίνησης

            $flag = false;


            if($this->topOfEachColumn[$x]>=4) {     //Αν το top της στήλης που παίχτηκε πιόνι($x) είναι > 4, να γίνει έλεγχος αν υπάρχει νίκη κάθετα
                $flag = $this->vertical($x , $color);//Αν βρέθηκε νίκη true αλλιώς false
            }
            if (!$flag){                             //Αν δεν βρέθηκε νίκη πριν, ψάχνει για νίκη πίσω διαγώνια
                $flag = $this->backDia($x,$color);
            }
            if (!$flag){                             //Αν δεν βρέθηκε νίκη στα προηγούμενα, ψάχνει μπροστά διαγώνια
                $flag = $this->frontDia($x,$color);
            }
            if (!$flag){                            //Αν δεν βρέθηκε νίκη στα προηγούμενα, ψάχνει οριζόντια
                $flag = $this->horizontal($x,$color);
            }
            return $flag;
        }

        private function horizontal($x,$color){
            //Ψάχνει οριζόνια για νίκη

            $streak=0;
            $i=$x; //i= στήλη που έγινε η κίνηση
            $top = $this->topOfEachColumn[$x]; //Το πιόνι που βρήσκετε στην υψηλότερη θέση στην στήλη (δηλαδή το πιόνι της τελευταίας κίνησης)

            while ($i>0  and $this->board[$top][$i] == $color ){
                /*Όσο το η στήλη(i) είναι μεγαλύτερο από το 0 και η θέση χρώμα του πιονιού στο board
                στην θέση (top,i) είναι ίσο με το πιόνι που παίχτηκε ψαχνει προς τα αριστερά της σειράς

                (ο πίνακας board έχει θέσεις από [0-7][0-6] αλλά δεν χρησιμοποιείται το board[0][0-7] και το board[0][0-6] γι αυτό ξεκινάει από το i>0)*/
                $streak++;
                $i--;
            }

            $i= $x + 1; // x+1 ώστε να μετρήσει και δεξιά από την θέση που παίχτηκε και να μην μετρήσει την ίδια θέση ως επιπλέον πιόνι
            while ($i<=7 and isset($this->board[$top][$i]) and $this->board[$top][$i] == $color ){
                /*Όσο το η στήλη(i) είναι μικρότερο από το 7 και η θέση χρώμα του πιονιού στο
                board στην θέση (top,i) είναι ίσο με το πιόνι που παίχτηκε ψάχνει προς τα δεξιά της σειράς */

                $streak++;
                $i++;

            }

            return $this->streakFlag($streak); // Έλεγχος αν το streak είναι ίσο ή μεγαλύτερο από 4
        }

        private function vertical($x,$color){
            //Ψάχνει κάθετα για πιθανή νικητήρια κίνηση

            $streak=0;
            $i=$this->topOfEachColumn[$x]; //i = με την θέση του πιονού με την υψηλότερη θέση στην στήλη που παίχτηκε το τελευταίο πιόνι(x)
            while ($i>0 && $this->board[$i][$x] == $color ) { //Ψάχνει στην στήλη προς τα κάτω για να ελέγξει αν έχει 4 όμοια
                $streak++;
                $i--;
            }

            return $this->streakFlag($streak);
        }

        private function backDia($x,$color){
            //Ψάνει για νικητήρια κίνηση πίσω διαγώνια

            $streak=0;
            $i=$x; //i = στήλη κίνησης

            $y=$this->topOfEachColumn[$x]; //y = ύψος κίνησης στην στήλη

            while ($i<7  and $y<6 and $this->board[$y][$i] == $color ){
                /*Ψάχνει διαγώνια προς τα πάνω και δεξιά
                 Όσο το i δεν ξεπερνάει την τέρμα δεξιά στήλη και όσο το y δεν ξεπερνάει το ύψος της στήλης
                και όσο το χρώμα του πιονιού της τελευταίας κίνησης είναι ίδιο με το χρώμα του board[y][i]
                ανέβασε το streak+1 */

                $streak++;
                $i++;
                $y++;
                //Με τα i++ και y++ δίνω την επόμενη θέση που ψάχνει, δηλαδή μία θέση πάνω και μία θέση δεξιά (διαγώνια πάνω δεξιά )
            }
            $i=$x -1 ;
            $y=$this->topOfEachColumn[$x] - 1;
            //Ξεκινάει από μία θέση κάτω και μία θέση αριστερά από το πιόνι της κίνησης για να μην μετρήσει ξανά το ίδιο

            while ($i>0 and $y>0 and $this->board[$y][$i] == $color ){
                /*Ψάχνει διαγώνια προς τα κάτω και αριστερά
                 Όσο το i δεν ξεπερνάει την τέρμα αριστερή στήλη και όσο το y δεν ξεπερνάει τον πάτο της στήλης
                και όσο το χρώμα του πιονιού της τελευταίας κίνησης είναι ίδιο με το χρώμα του board[y][i] */

                $streak++;
                $i--;
                $y--;
                //Με τα i-- και y-- δίνω την επόμενη θέση που ψάχνει, δηλαδή μία θέση κάτω και μία θέση αριστερά (διαγώνια κάτω αριστερά)
            }
            return $this->streakFlag($streak);
        }

        private function frontDia($x,$color){
            // Ψάχνει νικητήρια κίνηση στην μπροστά διαγώνιο

            $streak=0;
            $i=$x;
            $y=$this->topOfEachColumn[$x];
            while ($i<=7 and $y>0 and $this->board[$y][$i] == $color){

                $streak++;
                $i++;
                $y--;
                // Με τις αλλαγές y,i πηγαίνει στην επόμενη θέση, δηλαδή μία θέση κάτω και μία θέση αριστερά
            }
            $i=$x -1;
            $y=$this->topOfEachColumn[$x] + 1;
            while ($i>0 and $y<6 and $this->board[$y][$i] == $color){
                $streak++;
                $i--;
                $y++;
                // Με τις αλλαγές y,i πηγαίνει στην επόμενη θέση, δηλαδή μία θέση πάνω και μία θέση δεξιά
            }

            return $this->streakFlag($streak);
        }

        function show_board(){

            $this->boardFillFromDB();
            //Επειδή δεν έχω UI, εμφανίζω το board είτε με αυτόν τον τρόπο, είτε με json
            //Αυτός ο τρόπος είναι περισσότερο για να δω πως μοιάζει ο πίνακας και να κάνω debugging
            echo "<center> <table style='width: 10%';>";
            for ($i = 1; $i <= 6; $i++) {
                echo "<tr style='width: 30%;'>";
                for ($y = 1; $y <=7; $y++) {
                    $timi = $this->board[$i][$y];
                    if($timi == null){
                        $timi = '.';
                    }
                    echo "<td>" . $timi . "</td>";
                }
                echo "</tr>";
            }
            echo "</table> </center>";
        }

        private function streakFlag($streak){
            //Το καλούν όλοι οι έλεγχοι(horizontal,vertical,backDia,frontDia) ώστε να επιστρέψει false/true
            if ($streak>=4){
                return true;
            }else{
                return false;
            }
        }

        function checkTopOfX($x){
            //Χρησιμοποιείται στο api ώστε να ελέγχει αν επιτρέπεται η κίνηση στην στήλη x γιατί μπορεί να είναι γεμάτη
            if ($this->topOfEachColumn[$x] <6){
                return true;
            }else{
                return false;
            }
        }

        function getBoard(){
            //Χρησιμοποιείται στο api ώστε να πάρει το τον πίνακα board και να το εμφανίσει σε μορφή json
            return $this->board;
        }
    }
?>