<?php 
    class DBConection{
    
        public $conn;

        public function dbConnect(){
            
            $conn = pg_connect("host='dblabs.it.teithe.gr' port='5432' dbname='it164630' user='it164630' password='123456789'");
            
            if($conn){
                echo 'Connection attempt succeeded.';
            }else{
                echo 'Connection attempt failed.';
            }

            return conn;
            
        }
    }

?>