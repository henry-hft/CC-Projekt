<?php
class Database{
  
    // specify database credentials
    private $host = getenv("SQL_HOST");
    private $db_name = getenv("SQL_DATABASE");
    private $username = getenv("SQL_USER");
    private $password = getenv("SQL_PASSWORD");
    public $conn;
  
    // get the database connection
    public function getConnection(){
  
        $this->conn = null;
  
        try{
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        }catch(PDOException $exception){
            echo "Connection error: " . $exception->getMessage();
        }
  
        return $this->conn;
    }
}
?>
