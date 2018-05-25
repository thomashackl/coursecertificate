<?php

class certificate {

    public $user;
    public $tree;
    public $semester = array();
    public $fullname;
    public $whitelist;
    // exclude sem tree ids
    public $exclude_sem_tree_ids = array();

    public function __construct($user = null, $whitelist = null) {
        if ($whitelist != null) {
            $this->whitelist = $whitelist;
        }
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

    public function loadDuration(&$data, $lang = 'de_DE') {
        $db = DBManager::get();

        // Check if we got a swsentry
        $sws = $db->prepare("SELECT content FROM datafields_entries WHERE range_id = ? AND datafield_id = '8554741ae3a5cfcc38c6741ab0c9ce5e'");
        $sws->execute(array($data['seminar_id']));
        if ($entry = $sws->fetchColumn(0)) {
            if ($lang == 'en_GB') {
                $entry = str_replace(['Stunden', 'Stunde'], ['hours', 'hour'], $entry);
            }
            $data['dauer'] = $entry;
        } else {
            // Try to guess it
            $stmt = $db->prepare("SELECT ROUND((SUM(end_time - date) / 3600), 0) FROM termine WHERE range_id = ?");
            $stmt->execute(array($data['seminar_id']));
            $duration = $stmt->fetch(PDO::FETCH_COLUMN, 0);
            if ($duration && $duration != 0) {
                $data['dauer'] = $duration . ' '.($duration == 1 ?
                        ($lang == 'de_DE' ? ' Stunde' : ' hour') :
                        ($lang == 'de_DE' ? ' Stunden' : ' hours'));
            }
        }
    }

    public function loadSeminars() {
        $semtree = TreeAbstract::getInstance('StudipSemTree', array('visible_only' => 1));
        $sql = "SELECT DISTINCT s.Seminar_id, s.VeranstaltungsNummer, s.Name, sd.description as semester,
            sst.sem_tree_id, s.seminar_id, MIN(t.date) start, MAX(t.end_time) end,
            s.ects, s.Beschreibung, su.`status`
            FROM seminare s
            JOIN seminar_sem_tree sst USING (seminar_id)
            JOIN seminar_user su USING (Seminar_id)
            JOIN auth_user_md5 md5 USING (user_id)
            LEFT JOIN termine t ON (s.seminar_id = t.range_id)
            JOIN semester_data sd ON (sd.beginn <= s.start_time AND sd.ende >= s.start_time)
            WHERE md5.username = ?
            AND s.visible = 1
            AND s.Name NOT LIKE 'Nachrangige Ber%'
            AND s.Name NOT LIKE 'Unentschuldigt%'
            AND su.`status` IN ('user', 'autor')
            AND sst.sem_tree_id IN (?)
            ".($this->exclude_sem_tree_ids ? "AND sst.sem_tree_id NOT IN (?)" : "")."
            GROUP BY s.seminar_id, sst.sem_tree_id
            ORDER BY s.start_time, s.VeranstaltungsNummer, s.Name";
        $db = DBManager::get();
        $stmt = $db->prepare($sql);
        $parameters = array($this->user, $semtree->getKidsKids($this->sem_tree_id));
        if ($this->exclude_sem_tree_ids) {
            $parameters[] = $this->exclude_sem_tree_ids;
        }
        $stmt->execute($parameters);
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ((!$this->whitelist || in_array($result['seminar_id'], $this->whitelist)) && $obj = $this->tree->search($result['sem_tree_id'])) {
                if ($this->start == 0 || $this->start > $result['start']) {
                    $this->start = $result['start'];
                }

                $this->end = $result['end'];
                $this->loadLecturers($result);
                $this->loadDuration($result);
                $obj->seminare[] = $result;
                $this->semester[$result['semester']][] = $result;
                $this->allCourses[] = $result['seminar_id'];
                $this->header[$obj->name][] = $result;
            }
        }
    }

    public function loadSeminarsForPDF($lang = 'de_DE') {
        $this->header = array();
        $semtree = TreeAbstract::getInstance('StudipSemTree', array('visible_only' => 1));
        $allSubjects = $this->getSortedKidsKids($semtree, $this->sem_tree_id);
        $mainSubjects = array();
        foreach ($allSubjects as $s) {
            if (!in_array($s, $this->exclude_sem_tree_ids)) {

                if (!in_array($s, $this->invisible_sem_tree_ids)) {
                    $mainSubjects[] = $s;
                }
            }
        }

        $sql = "SELECT DISTINCT s.Seminar_id, s.VeranstaltungsNummer, s.Name, sd.description as semester,
                sst.sem_tree_id, s.seminar_id, MIN(t.date) start, MAX(t.end_time) end,
                s.ects, s.Beschreibung, su.`status`
            FROM seminare s
                JOIN seminar_sem_tree sst USING (seminar_id)
                JOIN seminar_user su USING (Seminar_id)
                JOIN auth_user_md5 md5 USING (user_id)
                LEFT JOIN termine t ON (s.seminar_id = t.range_id)
                JOIN semester_data sd ON (sd.beginn <= s.start_time AND sd.ende >= s.start_time)
            WHERE md5.username = ?
                AND s.visible = 1
                AND s.Name NOT LIKE 'Nachrangige Ber%'
                AND s.Name NOT LIKE 'Unentschuldigt%'
                AND su.`status` IN ('user', 'autor')
                AND sst.sem_tree_id IN (?)";
        if ($this->whitelist) {
            $sql .= " AND s.`Seminar_id` IN (?)";
        }
        $sql .=  " GROUP BY s.seminar_id, sst.sem_tree_id
            ORDER BY s.start_time, s.VeranstaltungsNummer, s.Name";
        $db = DBManager::get();
        $stmt = $db->prepare($sql);
        foreach ($mainSubjects as $subject) {
            if ($this->whitelist) {
                $parameters = array($this->user, array_merge(array($subject), $semtree->getKidsKids($subject)), $this->whitelist);
            } else {
                $parameters = array($this->user, array_merge(array($subject), $semtree->getKidsKids($subject)));
            }
            $stmt->execute($parameters);
            $i=0;
            $obj = $this->tree->search($subject);

            // Fetch translated names if necessary.
            if ($lang != 'de_DE') {
                $translation = DBManager::get()->fetchOne(
                    "SELECT `value` FROM `i18n`
                        WHERE `object_id` = :id
                            AND `table` = 'sem_tree'
                            AND `field` = 'name'
                            AND `lang` = :lang
                        LIMIT 1",
                    ['id' => $subject, 'lang' => $lang]
                );
                if ($translation) {
                    $obj->name = $translation['value'];
                }
            }

            while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $i++;
                if ($this->start == 0 || $this->start > $result['start']) {
                    $this->start = $result['start'];
                }
                if ($this->end == 0 || $this->end < $result['end']) {
                    $this->end = $result['end'];
                }
                $this->loadLecturers($result);
                $this->loadDuration($result, $lang);
                $obj->seminare[] = $result;

                $this->header[$obj->name][] = $result;
            }
        }
    }

    /**
     * returns all direct kids and kids of kids and so on...
     *
     * @access   public
     * @param    string  $item_id
     * @param    bool    $in_recursion   only used in recursion
     * @return   array
     */
    protected function getSortedKidsKids(&$semtree, $item_id, $in_recursion = false){
        static $kidskids;
        if (!$kidskids || !$in_recursion){
            $kidskids = array();
        }
        $num_kids = $semtree->getNumKids($item_id);
        if ($num_kids){
            $kids = $semtree->getKids($item_id);
            for ($i = 0; $i < $num_kids; ++$i){
                array_push($kidskids, $kids[$i]);
                $this->getSortedKidsKids($semtree, $kids[$i], true);
            }
        }
        return (!$in_recursion) ? $kidskids : null;
    }


}
