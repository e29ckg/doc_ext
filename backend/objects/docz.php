<?php
class Docz
{
    private $conn;
    private $table_name = "docz";

    public $id;
    public $r_number;
    public $r_date;
    public $doc_speed;
    public $doc_form_number;
    public $doc_date;
    public $doc_form;
    public $doc_to;
    public $name;
    public $file;
    public $user_create;
    public $st;
    public $start;
    public $end;
    public $created;

    public function __construct($db)
    {
        $this->conn = $db;
    }


    public function countAll($search)
    {
        // กำหนดเงื่อนไขการค้นหา
        $searchQuery = "";
        if ($search) {
            $searchQuery = "WHERE name LIKE :search";
        }

        // นับจำนวนข้อมูลทั้งหมด
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " " . $searchQuery . ";";
        $stmt = $this->conn->prepare($query);

        if ($search) {
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    // ฟังก์ชันเพื่อดึงข้อมูลตามหน้าและการค้นหา
    public function readPaginated($offset, $per_page, $search)
    {
        // กำหนดเงื่อนไขการค้นหา
        $searchQuery = "";
        if ($search) {
            $searchQuery = "WHERE name LIKE :search ";
        }

        // ดึงข้อมูลตามหน้าและการค้นหา
        $query = "SELECT * FROM docz $searchQuery ORDER BY created DESC LIMIT :offset, :per_page";
        $stmt = $this->conn->prepare($query);

        if ($search) {
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam(":per_page", $per_page, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    // Count total number of transactions
    public function count()
    {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function read()
    {
        $query = "SELECT * FROM " . $this->table_name . "ORDER BY created DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }


    function readOne()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->r_number = $row['r_number'];
            $this->r_date = $row['r_date'];
            $this->doc_speed = $row['doc_speed'];
            $this->doc_form_number = $row['doc_form_number'];
            $this->doc_date = $row['doc_date'];
            $this->doc_form = $row['doc_form'];
            $this->doc_to = $row['doc_to'];
            $this->name = $row['name'];
            $this->file = $row['file'];
            $this->user_create = $row['user_create'];
            $this->st = $row['st'];
            $this->created = $row['created'];
        }
    }

    function create()
    {
        $query = "INSERT INTO " . $this->table_name . "
          SET
            r_number=:r_number, r_date=:r_date, doc_speed=:doc_speed, doc_form_number=:doc_form_number,
            doc_date=:doc_date, doc_form=:doc_form, doc_to=:doc_to,
            name=:name, file=:file,
            user_create=:user_create, st=:st";


        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->r_number = htmlspecialchars(strip_tags($this->r_number));
        $this->r_date = htmlspecialchars(strip_tags($this->r_date));
        $this->doc_speed = htmlspecialchars(strip_tags($this->doc_speed));
        $this->doc_form_number = htmlspecialchars(strip_tags($this->doc_form_number));
        $this->doc_date = htmlspecialchars(strip_tags($this->doc_date));
        $this->doc_form = htmlspecialchars(strip_tags($this->doc_form));
        $this->doc_to = htmlspecialchars(strip_tags($this->doc_to));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->file = htmlspecialchars(strip_tags($this->file));
        $this->user_create = htmlspecialchars(strip_tags($this->user_create));
        $this->st = htmlspecialchars(strip_tags($this->st));

        // Bind values
        $stmt->bindParam(":r_number", $this->r_number);
        $stmt->bindParam(":r_date", $this->r_date);
        $stmt->bindParam(":doc_speed", $this->doc_speed);
        $stmt->bindParam(":doc_form_number", $this->doc_form_number);
        $stmt->bindParam(":doc_date", $this->doc_date);
        $stmt->bindParam(":doc_form", $this->doc_form);
        $stmt->bindParam(":doc_to", $this->doc_to);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":file", $this->file);
        $stmt->bindParam(":user_create", $this->user_create);
        $stmt->bindParam(":st", $this->st);

        // Execute query
        if ($stmt->execute()) {
            // สร้างเรื่องเพื่อเก็บค่า last inserted ID
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    public function update()
    {
        $query = "UPDATE " . $this->table_name . "
                  SET
                    r_number=:r_number, r_date=:r_date, doc_speed=:doc_speed, doc_form_number=:doc_form_number,
                    doc_date=:doc_date, doc_form=:doc_form, doc_to=:doc_to,
                    name=:name, file=:file,
                    user_create=:user_create, st=:st, created=CURRENT_TIMESTAMP
                  WHERE
                    id = :id";

        $stmt = $this->conn->prepare($query);

        $this->r_number = htmlspecialchars(strip_tags($this->r_number));
        $this->r_date = htmlspecialchars(strip_tags($this->r_date));
        $this->doc_speed = htmlspecialchars(strip_tags($this->doc_speed));
        $this->doc_form_number = htmlspecialchars(strip_tags($this->doc_form_number));
        $this->doc_date = htmlspecialchars(strip_tags($this->doc_date));
        $this->doc_form = htmlspecialchars(strip_tags($this->doc_form));
        $this->doc_to = htmlspecialchars(strip_tags($this->doc_to));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->file = htmlspecialchars(strip_tags($this->file));
        $this->user_create = htmlspecialchars(strip_tags($this->user_create));
        $this->st = htmlspecialchars(strip_tags($this->st));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":r_number", $this->r_number);
        $stmt->bindParam(":r_date", $this->r_date);
        $stmt->bindParam(":doc_speed", $this->doc_speed);
        $stmt->bindParam(":doc_form_number", $this->doc_form_number);
        $stmt->bindParam(":doc_date", $this->doc_date);
        $stmt->bindParam(":doc_form", $this->doc_form);
        $stmt->bindParam(":doc_to", $this->doc_to);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":file", $this->file);
        $stmt->bindParam(":user_create", $this->user_create);
        $stmt->bindParam(":st", $this->st);
        $stmt->bindParam(":id", $this->id);

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