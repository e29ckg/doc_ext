<?php

class Users
{
    private $conn;
    private $table_name = "users";

    public $id;
    public $uid;
    public $card_id;
    public $role;
    public $active;
    public $created;
    public $updated;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readOne()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['id'];
            $this->uid = $row['uid'];
            $this->card_id = $row['card_id'];
            $this->role = $row['role'];
            $this->active = $row['active'];
            $this->created = $row['created'];
            $this->updated = $row['updated'];
        }
    }
    public function readOneCardId()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE card_id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->card_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['id'];
            $this->uid = $row['uid'];
            $this->card_id = $row['card_id'];
            $this->role = $row['role'];
            $this->active = $row['active'];
            $this->created = $row['created'];
            $this->updated = $row['updated'];
        }
    }
    public function read_uid()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE uid = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->uid);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['id'];
            $this->uid = $row['uid'];
            $this->card_id = $row['card_id'];
            $this->role = $row['role'];
            $this->active = $row['active'];
            $this->created = $row['created'];
            $this->updated = $row['updated'];
        }
    }
    public function ck_uid()
    {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE uid = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->uid);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // ถ้าจำนวนที่ได้จากการค้นหามีค่า > 0 หมายถึงมี uid อยู่ในฐานข้อมูล
        return $row['count'] > 0;
    }

    public function readAll()
    {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }
    function create()
    {
        $query = "INSERT INTO " . $this->table_name . "
          SET
            uid=:uid, card_id=:card_id, role=:role, active=:active";

        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->uid = htmlspecialchars(strip_tags($this->uid));
        $this->card_id = htmlspecialchars(strip_tags($this->card_id));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->active = htmlspecialchars(strip_tags($this->active));

        // Bind values
        $stmt->bindParam(":uid", $this->uid);
        $stmt->bindParam(":card_id", $this->card_id);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":active", $this->active);

        // Execute query
        if ($stmt->execute()) {
            // สร้างเรื่องเพื่อเก็บค่า last inserted ID
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }
    function create_uid()
    {
        if (!$this->ck_uid()) {
            $query = "INSERT INTO " . $this->table_name . "
              SET
                uid=:uid";

            $stmt = $this->conn->prepare($query);

            // Clean data
            $this->uid = htmlspecialchars(strip_tags($this->uid));

            // Bind values
            $stmt->bindParam(":uid", $this->uid);

            // Execute query
            if ($stmt->execute()) {
                // สร้างเรื่องเพื่อเก็บค่า last inserted ID
                $this->id = $this->conn->lastInsertId();
                return true;
            }
        }

        return false;
    }

    function update()
    {
        $query = "UPDATE " . $this->table_name . "
                  SET
                    card_id=:card_id, updated=:updated
                  WHERE
                    uid = :uid";

        $stmt = $this->conn->prepare($query);

        $this->uid = htmlspecialchars(strip_tags($this->uid));
        $this->card_id = htmlspecialchars(strip_tags($this->card_id));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":card_id", $this->card_id);
        $stmt->bindParam(":uid", $this->uid);
        $currentTime = date('Y-m-d H:i:s');
        $stmt->bindParam(":updated", $currentTime);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    function delete()
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind id
        $stmt->bindParam(1, $this->id);

        // Execute query
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

}

?>