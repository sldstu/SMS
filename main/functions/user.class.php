<?php
require_once '../database/database.class.php';

class User {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function signup($username, $password, $email) {
        try {
            $sql = 'INSERT INTO users (username, password, role, email) VALUES (:username, :password, :role, :email)';
            $query = $this->conn->prepare($sql);
            $query->bindParam(':email', $email);
            $query->bindParam(':username', $username);
            $query->bindParam(':password', password_hash($password, PASSWORD_BCRYPT));
            $default_role = 'student'; // Default role is student
            $query->bindParam(':role', $default_role);
            return $query->execute();
        } catch (PDOException $e) {
            echo "Signup Error: " . $e->getMessage();
            return false;
        }
    }

    public function login($username, $password) {
        try {
            $sql = 'SELECT * FROM users WHERE username = :username';
            $query = $this->conn->prepare($sql);
            $query->bindParam(':username', $username);
            $query->execute();
            $user = $query->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            echo "Login Error: " . $e->getMessage();
            return false;
        }
    }

    public function fetch($username) {
        try {
            $sql = 'SELECT * FROM users WHERE username = :username';
            $query = $this->conn->prepare($sql);
            $query->bindParam(':username', $username);
            $query->execute();
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Fetch Error: " . $e->getMessage();
            return false;
        }
    }

    public function fetchAll() {
        try {
            $sql = 'SELECT * FROM users';
            $query = $this->conn->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Fetch All Error: " . $e->getMessage();
            return false;
        }
    }

    public function delete($user_id) {
        try {
            $sql = 'DELETE FROM users WHERE user_id = :user_id';
            $query = $this->conn->prepare($sql);
            $query->bindParam(':user_id', $user_id);
            return $query->execute();
        } catch (PDOException $e) {
            echo "Delete Error: " . $e->getMessage();
            return false;
        }
    }

    public function update($user_id, $username, $password, $role, $first_name, $last_name) {
        try {
            $sql = 'UPDATE users SET username = :username, password = :password, role = :role, first_name = :first_name, last_name = :last_name WHERE user_id = :user_id';
            $query = $this->conn->prepare($sql);
            $query->bindParam(':user_id', $user_id);
            $query->bindParam(':username', $username);
            $query->bindParam(':password', password_hash($password, PASSWORD_BCRYPT));
            $query->bindParam(':role', $role);
            $query->bindParam(':first_name', $first_name);
            $query->bindParam(':last_name', $last_name);
            return $query->execute();
        } catch (PDOException $e) {
            echo "Update Error: " . $e->getMessage();
            return false;
        }
    }

    public function changeRole($user_id, $role) {
        try {
            $sql = 'UPDATE users SET role = :role WHERE user_id = :user_id';
            $query = $this->conn->prepare($sql);
            $query->bindParam(':user_id', $user_id);
            $query->bindParam(':role', $role);
            return $query->execute();
        } catch (PDOException $e) {
            echo "Change Role Error: " . $e->getMessage();
            return false;
        }
    }
}
?>
