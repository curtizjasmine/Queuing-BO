<?php 

class database{
           private $host = "localhost";
           private $user ="root";
           private $pwd = "";
           private $dbname = "opd_db";
           protected function connect(){
                 
                   $pdo = new PDO("mysql:host=$this->host; dbname=".$this->dbname,$this->user,$this->pwd);
                   $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                   return $pdo;
           }
        

     
}


  



?>