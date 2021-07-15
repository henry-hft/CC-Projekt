<?php
class Database
{
    // specify database credentials
    private $host = "localhost";
    private $db_name = "cloud";
    private $username = "user";
    private $password = "test";
    public $conn;

    // get the database connection
    public function getConnection()
    {
        $this->conn = null;
    
        if (getenv("SQL_HOST") && getenv("SQL_DATABASE") && getenv("SQL_USER") && getenv("SQL_PASSWORD")) {
             $this->host = getenv("SQL_HOST");
             $this->db_name = getenv("SQL_DATABASE");
             $this->username = getenv("SQL_USER");
             $this->password = getenv("SQL_PASSWORD");
        }
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->exec("set names utf8");
        } catch (PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>
