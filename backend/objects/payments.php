<?php
class payments {
    private $conn;
    private $table_name = "financial_attorney_transactions";

    public $id;
    public $code_pf;
    public $version;
    public $codeH;
    public $code;
    public $account;
    public $amount;
    public $date_now;
    public $vendor_name;
    public $effective_date;
    public $bene_ref;
    public $personal_id;
    public $created;
    public $updated;

    public function __construct($db) {
        $this->conn = $db;
    }
    
    
   public function countAll($search) {
        // กำหนดเงื่อนไขการค้นหา
        $searchQuery = "";
        if ($search) {
            $searchQuery = "WHERE personal_id = :search";
        }

        // นับจำนวนข้อมูลทั้งหมด
        $query = "SELECT COUNT(*) as total FROM ".$this->table_name ." ". $searchQuery." ORDER BY effective_date DESC;";
        $stmt = $this->conn->prepare($query);

        if ($search) {
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    // ฟังก์ชันเพื่อดึงข้อมูลตามหน้าและการค้นหา
    public function readPaginated($offset, $per_page, $search) {
        // กำหนดเงื่อนไขการค้นหา
        $searchQuery = "";
        if ($search) {
            $searchQuery = "WHERE personal_id =:search";
        }

        // ดึงข้อมูลตามหน้าและการค้นหา
        $query = "SELECT * FROM financial_attorney_transactions $searchQuery ORDER BY effective_date DESC LIMIT :offset, :per_page";
        $stmt = $this->conn->prepare($query);

        if ($search) {
            $stmt->bindValue(':search', $search, PDO::PARAM_STR);
        }
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam(":per_page", $per_page, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    // Count total number of transactions
    public function count() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name ."ORDER BY created DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    

    function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->code_pf = $row['code_pf'];
            $this->version = $row['version'];
            $this->codeH = $row['codeH'];
            $this->code = $row['code'];
            $this->account = $row['account'];
            $this->amount = $row['amount'];
            $this->date_now = $row['date_now'];
            $this->vendor_name = $row['vendor_name'];
            $this->effective_date = $row['effective_date'];
            $this->bene_ref = $row['bene_ref'];
            $this->personal_id = $row['personal_id'];
            $this->created = $row['created'];
        }
    }

    
}
?>
