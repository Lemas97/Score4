<?php
require_once "dbconnect.php";
class Player
{

    private $username;
    private $status=0;
    private $color;

    public function __construct($username, $password)
    {
        $this->login($username,$password);

    }


    function setColor($c)
    {
        $this->color = $c;
    }

    function getColor()
    {
        return $this->color;
    }

    function getUsername()
    {
        return $this->username;
    }

    private function login($username,$password)
    {
        global $mysqli;

        $sql = "select * from player where username = ? and password = ?";
        $st = $mysqli->prepare($sql);
        $st->bind_param('ss' ,$username, $password);
        $st->execute();
        $users = $st->get_result();
        $c = 'R';

        if ($users->num_rows > 0) {
            foreach ($users as $user) {
                if ($user['status'] == 0) {

                    $sql = 'select * from player where status = 1';
                    $colors = $mysqli->query($sql);

                    if ($colors->num_rows > 0) {
                        foreach ($colors as $color) {

                            if ($color['color'] == 'R') {
                                $c = 'Y';
                            }
                        }
                    }
                    print json_encode(["loginStatus"=>"Συνδέθηκες με επιτυχία!"]);

                    $sql = "update player set status = 1 , color = ? where username = ?";
                    $st = $mysqli->prepare($sql);
                    $st->bind_param('ss' ,$c,$username);
                    $st->execute();

                    $this->username = $username;
                    $this->color = $c;
                    $this->status=1;



                } else {
                    print json_encode(["errormesg" => "Είσαι ήδη συνδεδεμένος!"]);
                    exit;
                }
            }

        } else {
            print json_encode(["errormesg" => "Λάθος συνδιασμός στοιχείων!"]);

        }
    }

    function logout(){
        if($this->status==1){
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
        if($this->status==0){
            return false;
        }
        return true;
    }

}

?>