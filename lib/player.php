<?php
require_once "dbconnect.php";
class Player
{
    private $username;
    private $status=0; //Αν είναι συνδεδεμένος γίνεται 1 διαφορετικά είναι 0
    private $color; //Παίρνει χρώμα πιονιού όταν συνδεθεί ανάλογα ποιο είναι διαθέσιμο

    public function __construct($username, $password)
    {
        $this->login($username,$password);
    }

    function getColor()
    {
        //Χρησιμοποιείται στο api
        return $this->color;
    }

    function getUsername()
    {
        return $this->username;
    }

    private function login($username,$password)
    {
        global $mysqli;

        $sql = "select * from player where username = ? and password = ?"; //Βρίσκει τις στήλες με τα στοιχεία που έδωσε ο χρήστης
        $st = $mysqli->prepare($sql);
        $st->bind_param('ss' ,$username, $password);
        $st->execute();
        $users = $st->get_result();
        $c = 'R';

        if ($users->num_rows > 0) {
            //Αν βρέθηκε έστω και μία σειρά σημαίνει ότι τα στοιχεία είναι σωστά
            foreach ($users as $user) {
                if ($user['status'] == 0) {
                    //Αν δεν είναι συνδεδεμένος

                    $sql = 'select * from player where status = 1'; //Βρίσκει τους συνδεδεμένους παίκτες
                    /*Το παιχνίδι έχει σχεδιαστεί ώστε να πραγματοποιείται μόνο ένα παιχνίδι την φορά
                    οπότε επιτρέπεται μέχρι 2 άτομα να συνδεθούν*/
                    $colors = $mysqli->query($sql);

                    if ($colors->num_rows > 0) {
                        //Αν υπάρχει κάποιος άλλος συνδεδεμένος
                        foreach ($colors as $color) {

                            if ($color['color'] == 'R') { //Ελέγχει το χρώμα του και δίνει αναλόγως
                                $c = 'Y';
                            }
                        }
                    }
                    header("Content-type: application/json; charset=utf-8");
                    print json_encode(["loginStatus"=>"Συνδέθηκες με επιτυχία!"]);

                    $sql = "update player set status = 1 , color = ? where username = ?"; //Πέρασμα στην βάση ως συνδεδεμένος χρήστης και το χρώμα του
                    $st = $mysqli->prepare($sql);
                    $st->bind_param('ss' ,$c,$username);
                    $st->execute();

                    $this->username = $username; //Συμπλήρωση των πεδίων του αντικειμένου
                    $this->color = $c;
                    $this->status=1;
                } else {
                    header("HTTP/1.1 400 Bad Request");
                    header("Content-type: application/json; charset=utf-8");
                    print json_encode(["errormesg" => "Είσαι ήδη συνδεδεμένος!"]);
                    exit;
                }
            }
        } else {
            header("HTTP/1.1 400 Bad Request");
            print json_encode(["errormesg" => "Λάθος συνδιασμός στοιχείων!"]);

        }
    }

    function logout(){
        //Χρησιμοποιείται στο api
        if($this->status==1){
            //Αν είναι συνδεδεμένος
            print json_encode(["logoutStatus" => "Αποσυνδέθηκες!"]);
            global $mysqli;

            $sql ="update player set status = 0, color = 'null' where username = ?";
            $st = $mysqli->prepare($sql);
            $st->bind_param('s',$this->username);
            $st->execute();
            $this->status=0;
        }
    }

    function checkStatus(){
        //Χρησιμοποιείται στο api
        if($this->status==0){
            return false;
        }
        return true;
    }

}

?>