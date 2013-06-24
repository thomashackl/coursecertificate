<?php
/**
 * basic tree class
 */
class leafTree {

    public $data;
    public $children;
    private $parent;

    public function __construct($data = null) {
        if ($data != null) {
            $this->setData($data);
        }
    }

    public function addNewChild() {
        $class = get_class($this);
        $child = new $class;
        $this->addChild($child);
        return $child;
    }

    public function addChild($child) {
        if (!is_array($this->children)) {
            $this->children = array();
        }
        $child->setParent($this);
        array_push($this->children, $child);
    }
    
    public function hasChildren() {
        return count($this->children);
    }

    public function getData() {
        return $this->data;
    }

    public function setData($data) {
        $this->data = $data;
    }

    public function getParent() {
        return $this->parent;
    }

    public function setParent($parent) {
        $this->parent = $parent;
    }

}

?>
