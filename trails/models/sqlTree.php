<?php
/**
 * Extends a tree by some sql functions to be automaticly loaded and stored
 * into database
 */
class sqlTree extends leafTree {

    public $id = 0;
    protected $SQL = "SELECT * FROM `%s` WHERE `%s` = ?";
    
    public function loadChildrenFromSQL($db, $table, $idCol = "id", $parentidCol = "parent_id") {
        $sql = sprintf($this->SQL, $table, $parentidCol);
        $stmt = $db->prepare($sql);
        $stmt->execute(array($this->id));
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $child = $this->addNewChild();
            $child->setID($result[$idCol]);
            $child->setData($result);
            $child->setIdFromDataCol($idCol);
        }
    }

    public function loadChildrenRecursiveFromSQL($db, $table, $idCol = "id", $parentidCol = "parent_id") {
        $this->loadChildrenFromSQL($db, $table, $idCol, $parentidCol);
        if ($this->hasChildren()) {
            foreach ($this->children as &$child) {
                $child->loadChildrenRecursiveFromSQL($db, $table, $idCol, $parentidCol);
            }
        }
    }

    public function loadFromSQL($db, $table, $idCol = "id") {
        $sql = sprintf($this->SQL, $table, $idCol);
        $stmt = $db->prepare($sql);
        $stmt->execute(array($this->id));
        if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->setData($result);
        }
    }
    
    public function hasSQLChildren($db, $table, $idCol = "id", $parentidCol = "parent_id") {
        $sql = sprintf($this->SQL, $table, $parentidCol) . " LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($this->id));
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function loadAllFromSQL($db, $table, $idCol = "id", $parentidCol = "parent_id") {
        $this->loadFromSQL($db, $table, $idCol);
        $this->loadChildrenRecursiveFromSQL($db, $table, $idCol, $parentidCol);
    }

    public function setIdFromDataCol($idCol) {
        $data = $this->getData();
        $this->setID($data[$idCol]);
    }

    public function getID() {
        return $this->id;
    }

    public function setID($id) {
        $this->id = $id;
    }

}

?>
