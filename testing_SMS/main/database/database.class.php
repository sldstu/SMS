<?php
class Database {
    private $host = 'localhost';
    private $username = 'root';
    private $password = '';
    // private $db = 'sports_management';
    private $db = 'sms_db';
    protected $connection;

    public function connect() {
        try {
            $this->connection = new PDO("mysql:host=$this->host;dbname=$this->db", $this->username, $this->password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo 'Connection error: ' . $e->getMessage();
        }
        return $this->connection;
    }
}
?>
