<?php
class Doc_file
{
    private $conn;
    private $table_name = "doc_file";

    public $id;
    public $docz_id;
    public $doc_form;
    public $name;
    public $file;
    public $ext;
    public $user_id_create;
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
        $query = "SELECT * FROM $this->table_name $searchQuery ORDER BY created DESC LIMIT :offset, :per_page";
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
        $query = "SELECT * FROM $this->table_name ORDER BY created DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    public function readDoczId($docz_id)
    {
        $query = "SELECT * FROM $this->table_name WHERE docz_id = :docz_id ORDER BY created DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":docz_id", $docz_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    function readOne()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['id'];
            $this->docz_id = $row['docz_id'];
            $this->doc_form = $row['doc_form'];
            $this->name = $row['name'];
            $this->file = $row['file'];
            $this->ext = $row['ext'];
            $this->user_id_create = $row['user_id_create'];
            $this->created = $row['created'];
        }
    }

    function create()
    {
        $query = "INSERT INTO " . $this->table_name . "
          SET
            docz_id=:docz_id, doc_form=:doc_form, name=:name, file=:file,
            ext=:ext, user_id_create=:user_id_create";

        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->docz_id = htmlspecialchars(strip_tags($this->docz_id));
        $this->doc_form = htmlspecialchars(strip_tags($this->doc_form));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->file = htmlspecialchars(strip_tags($this->file));
        $this->ext = htmlspecialchars(strip_tags($this->ext));
        $this->user_id_create = htmlspecialchars(strip_tags($this->user_id_create));

        // Bind values
        $stmt->bindParam(":docz_id", $this->docz_id);
        $stmt->bindParam(":doc_form", $this->doc_form);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":file", $this->file);
        $stmt->bindParam(":ext", $this->ext);
        $stmt->bindParam(":user_id_create", $this->user_id_create);

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
                    docz_id=:docz_id, doc_form=:doc_form, name=:name, file=:file,
                    ext=:ext, user_id_create=:user_id_create, created=CURRENT_TIMESTAMP
                  WHERE
                    id = :id";

        $stmt = $this->conn->prepare($query);

        $this->docz_id = htmlspecialchars(strip_tags($this->docz_id));
        $this->doc_form = htmlspecialchars(strip_tags($this->doc_form));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->file = htmlspecialchars(strip_tags($this->file));
        $this->ext = htmlspecialchars(strip_tags($this->ext));
        $this->user_id_create = htmlspecialchars(strip_tags($this->user_id_create));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":docz_id", $this->docz_id);
        $stmt->bindParam(":doc_form", $this->doc_form);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":file", $this->file);
        $stmt->bindParam(":ext", $this->ext);
        $stmt->bindParam(":user_id_create", $this->user_id_create);
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