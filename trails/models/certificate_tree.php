<?php

/**
 * resourcestree for Raumbelegung
 */
class certificate_tree extends sqlTree {

    public $seminars = [];

    public function search($id) {
        if ($id == $this->id) {
            return $this;
        }
        if ($this->hasChildren()) {
            foreach ($this->children as $child) {
                if ($result = $child->search($id)) {
                    return $result;
                }
            }
        }
        return false;
    }

    public function setData($data) {
        parent::setData($data);
        $this->name = $data['name'];
    }

    public function getChildrenWithSeminars() {
        $result = [];
        foreach ($this->children as $child) {
            if (count($child->seminare))
                $result[] = $child;
        }
        return $result;
    }

}
