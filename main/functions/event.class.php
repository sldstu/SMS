<?php
require_once 'database.class.php';

class Event {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function addEvent($event_name, $event_date, $teacher_id) {
        try {
            $sql = 'INSERT INTO events (event_name, event_date, teacher_id) VALUES (:event_name, :event_date, :teacher_id)';
            $query = $this->conn->prepare($sql);
            $query->bindParam(':event_name', $event_name);
            $query->bindParam(':event_date', $event_date);
            $query->bindParam(':teacher_id', $teacher_id);
            return $query->execute();
        } catch (PDOException $e) {
            echo "Add Event Error: " . $e->getMessage();
            return false;
        }
    }

    public function getEventsByTeacher($teacher_id) {
        try {
            $sql = 'SELECT * FROM events WHERE teacher_id = :teacher_id';
            $query = $this->conn->prepare($sql);
            $query->bindParam(':teacher_id', $teacher_id);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Get Events Error: " . $e->getMessage();
            return false;
        }
    }

    public function getAllEvents() {
        try {
            $sql = 'SELECT * FROM events';
            $query = $this->conn->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Get All Events Error: " . $e->getMessage();
            return false;
        }
    }

    public function deleteEvent($event_id) {
        try {
            $sql = 'DELETE FROM events WHERE event_id = :event_id';
            $query = $this->conn->prepare($sql);
            $query->bindParam(':event_id', $event_id);
            return $query->execute();
        } catch (PDOException $e) {
            echo "Delete Event Error: " . $e->getMessage();
            return false;
        }
    }

    public function updateEvent($event_id, $event_name, $event_date) {
        try {
            $sql = 'UPDATE events SET event_name = :event_name, event_date = :event_date WHERE event_id = :event_id';
            $query = $this->conn->prepare($sql);
            $query->bindParam(':event_name', $event_name);
            $query->bindParam(':event_date', $event_date);
            $query->bindParam(':event_id', $event_id);
            return $query->execute();
        } catch (PDOException $e) {
            echo "Update Event Error: " . $e->getMessage();
            return false;
        }
    }
}
?>
