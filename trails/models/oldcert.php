<?php

class certificate {

    public $user;
    public $tree;
    public $semester = array();
    public $fullname;
    public $whitelist;

    public function __construct($user = null, $whitelist = null) {
        $this->whitelist = $whitelist;
        if ($user) {
            $this->user = $user;
            $this->loadTree();
            $this->loadSeminars();
            $this->fullname = get_fullname_from_uname($user);
        }
    }

    public function getCount() {
        return $this->whitelist ? count($this->whitelist) : count($this->allCourses);
    }

    public function getCourses() {
        return array();
    }

    public function loadTree() {
        $this->tree = new certificate_tree();
        $this->tree->setID($this->sem_tree_id);
        $this->tree->loadFromSQL(DBManager::get(), 'sem_tree', 'sem_tree_id');
        $this->tree->loadChildrenRecursiveFromSQL(DBManager::get(), 'sem_tree', 'sem_tree_id');
    }

    public function loadLecturers(&$data) {
        $sql = "SELECT " . $GLOBALS['_fullname_sql']['full'] . " as fullname FROM seminar_user su
            JOIN auth_user_md5 md5 USING(user_id)
            JOIN user_info ui USING(user_id)
WHERE su.seminar_id = ? AND status = 'dozent'";
        $db = DBManager::get();
        $stmt = $db->prepare($sql);
        $stmt->execute(array($data['seminar_id']));
        $data['dozenten'] = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    public function loadDuration(&$data) {
        $sql = "SELECT ROUND((SUM(end_time - date) / 3600), 0) FROM termine
WHERE range_id = ?";
        $db = DBManager::get();
        $stmt = $db->prepare($sql);
        $stmt->execute(array($data['seminar_id']));
        $data['dauer'] = $stmt->fetch(PDO::FETCH_COLUMN, 0);
    }

    public function loadSeminars() {
        $sql = "SELECT VeranstaltungsNummer, s.Name, sd.description as semester, st.sem_tree_id, s.seminar_id, st.parent_id as parent, MIN(t.date) start, MAX(t.end_time) end
            FROM seminare s
            JOIN seminar_sem_tree sst USING (seminar_id)
            JOIN sem_tree st USING (sem_tree_id)
            JOIN seminar_user su USING (Seminar_id)
            JOIN auth_user_md5 md5 USING (user_id)
            LEFT JOIN termine t ON (s.seminar_id = t.range_id)
            JOIN semester_data sd ON (sd.beginn <= s.start_time AND sd.ende >= s.start_time)
            WHERE md5.username = ?
            AND s.visible = 1
            AND s.Name NOT LIKE 'Nachrangige Berücksichtigung %'
            AND s.Name NOT LIKE 'Unentschuldigt%'
            GROUP BY s.seminar_id, st.sem_tree_id
            ORDER BY s.start_time";
        $db = DBManager::get();
        $stmt = $db->prepare($sql);
        $stmt->execute(array($this->user));
        $test = $stmt->fetchAll(PDO::FETCH_ASSOC);
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ((!$this->whitelist == null || in_array($result['seminar_id'], $this->whitelist)) && $obj = $this->tree->search($result['parent'])) {
                if ($this->start == 0 || $this->start > $result['start']) {
                    $this->start = $result['start'];
                }

                $this->end = $result['end'];
                $this->loadLecturers($result);
                $this->loadDuration($result);
                $obj->seminare[] = $result;
                $this->semester[$result['semester']][] = $result;
                $this->allCourses[] = $result['seminar_id'];
            }
        }
    }

}
