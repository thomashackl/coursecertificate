<?php

class certificate_jura extends certificate {

    public $name = 'Schlüsselkompetenzen Jura';
    public $beschreibung = "Das Zentrum für Schlüsselkompetenzen der Universität Passau ist eine zentrale wissenschaftliche Einrichtung, die als Ergänzung zum akademischen Fachstudium Kurse aus dem Bereich überfachlicher Kompetenzen anbietet. Studierende, die das größtenteils freiwillige Kursangebot in Anspruch nehmen, beweisen damit Eigeninitiative und eine hohe Motivation zur persönlichen Weiterentwicklung.";
    public $sem_tree_id = '23bd2f0b9f437b60729290733961853d';

    public function oldDataFetchIsNowObsolete() {
        // Descriptional text underneath the course list.

        $db = DBManager::get();

        $fullname = get_fullname_from_uname(Request::get('username'));

        $selectedCourses = array();
        $count = 0;

// Check which sem_tree items are relevant
        $semTree = new StudipSemTree(array('visible_only' => true));
        $semTreeIds = array();
        foreach ($seminars as $semTreeItem) {
            $semTreeIds = array_merge($semTreeIds, $semTree->getKidsKids($semTreeItem), array($semTreeItem));
        }
        new dBug($semTree->getKidsKids($semTreeItem));
        new dBug($seminars);
        new dBug($semTreeIds);
        die;

// Get courses.
        $query = "SELECT s.Name, s.Seminar_id, st.sem_tree_id AS sem_tree_id, 
        st.name AS sem_tree_name, s.start_time
    FROM seminare s
        JOIN seminar_sem_tree sst ON (s.Seminar_id=sst.seminar_id)
        JOIN sem_tree st ON (sst.sem_tree_id=st.sem_tree_id)
    WHERE s.Seminar_id IN ('" . implode("', '", $seminars) . "')
        AND st.sem_tree_id IN ('" . implode("', '", $semTreeIds) . "')
    ORDER BY st.priority ASC, s.start_time ASC, s.VeranstaltungsNummer ASC, s.Name ASC;";
        $stmt = $db->query($query);
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!stristr($result["name"], "Nachrangige Berücksichtigung")) {
                $starttime = veranstaltung_beginn($result["Seminar_id"], true);
                // Set correct minimal time.
                if (!$mintime || $starttime < $mintime) {
                    $mintime = $starttime;
                }
                if (!$maxtime || $starttime > $maxtime) {
                    $maxtime = $starttime;
                }


                // Get lecturers.
                $lecturers = array();
                $query2 = "SELECT auth_user_md5.user_id, " .
                        $GLOBALS['_fullname_sql']['full'] . " AS fullname 
            FROM auth_user_md5 
                JOIN user_info USING (user_id)
            WHERE auth_user_md5.user_id IN ('" .
                        implode("', '", array_keys(get_seminar_dozent($result["Seminar_id"]))) .
                        "')
            ORDER BY Nachname ASC, Vorname ASC";
                $stmt2 = $db->query($query2);
                while ($result2 = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                    $name = $result2["fullname"];
                    $db3 = new DB_Seminar();
                    $query3 = "SELECT e.content 
                FROM datafields_entries e 
                    JOIN datafields d ON (e.datafield_id=d.datafield_id) 
                WHERE e.range_id='" . $result2["user_id"] . "' 
                    AND d.name='Berufsbezeichnung' LIMIT 1";
                    $stmt3 = $db->query($query3);
                    if ($result3 = $stmt3->fetch(PDO::FETCH_ASSOC)) {
                        $name .= " (" . $result3["content"] . ")";
                    }
                    $lecturers[] = $name;
                }
                // Get duration.
                $duration = 0;
                $query3 = "SELECT content
            FROM datafields_entries
            WHERE datafield_id='8554741ae3a5cfcc38c6741ab0c9ce5e' 
                AND range_id = '" . $result["Seminar_id"] . "'";
                $stmt4 = $db->query($query3);
                if ($result4 = $stmt4->fetch(PDO::FETCH_ASSOC)) {
                    $duration = intval($result4["content"]);
                }
                $selectedCourses[$result['sem_tree_id']]['name'] = $result['sem_tree_name'];
                $selectedCourses[$result['sem_tree_id']]['sem'][] = array(
                    'id' => $result["Seminar_id"],
                    'name' => $result["Name"],
                    'dozenten' => $lecturers,
                    'dauer' => $duration
                );
                $count++;
            }
        }

        // Get first and last date of all courses.
        $query = "SELECT MIN(`date`) AS minimum, MAX(`end_time`) AS maximum 
        FROM `termine` WHERE `range_id` IN ('" .
                implode("', '", $seminars) . "')";
        $stmt = $db->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    }

// Descriptional text underneath the course list.

    public function export($seminars) {

        // Create certificate.
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->setMargins(15, 10, 15);

        $pdf->SetY($pdf->GetY() + 75);


        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(180, 18, $this->fullname, 0, 1, "C");

        // Get current locale time setting.
        $currentLocale = setlocale(LC_TIME, "0");
        // Set to German locale.
        setlocale(LC_TIME, "de_DE");
        $pdf->SetFont('Arial', '', 11);
        $pdf->MultiCell(180, 5, "hat am Zentrum für Schlüsselkompetenzen der " .
                "Universität Passau im Zeitraum von " .
                strftime('%B %Y', $this->start) .
                " bis " . strftime('%B %Y', $this->end) . " an " . $this->getCount() .
                " Kursen aus folgenden Bereichen erfolgreich teilgenommen:", 0, "C");
        // Re-set locale to original value.
        setlocale(LC_TIME, $currentLocale);

        // Default font and margin sizes.
        $textsize = 11;
        $headersize = 11;
        $infosize = 8;
        $cellHeight = 5;
        $topMargin = 4;
        $bottomMargin = 4;
        // If there are more than x courses, start to adapt font size.
        if ($this->getCount() > 14) {
            $textsize = max(6, $textsize - round($this->getCount() / 15));
            $headersize = max(6, $headersize - round($this->getCount() / 15));
            $infosize = max(6, $infosize - round($this->getCount() / 15));
            $cellHeight = max(2, $cellHeight - round($this->getCount() / 15));
            $topMargin = max(0, $topMargin - round($this->getCount() / 14));
            $bottomMargin = max(0, $topMargin - round($this->getCount() / 14));
        }

        $pdf->SetY($pdf->GetY() + $topMargin);

        // Studiengaenge ausgeben
        foreach ($this->header as $header => $item ){
                $pdf->SetFont('Arial', 'B', $headersize);
                $pdf->Cell(180, $cellHeight, $header, 0, 1, "C");

                // Veranstaltungen ausgeben
                foreach ($item as $ver) {
                    $pdf->SetFont('Arial', '', $textsize);
                    $tmp = $ver['Name'];

                    // Dozenten und Stunden ausgeben
                    if ($ver['dauer'] != 0) {
                        $hours = ($ver['dauer'] == 1 ? ' Stunde' : ' Stunden');
                        $tmp .= ' (' . $ver['dauer'] . $hours . ')';
                    }
                    if (!empty($ver['dozenten'])) {
                        $tmp .= ' - ';
                        $tmp .= implode(", ", $ver['dozenten']);
                    }
                    $pdf->MultiCell(180, $cellHeight, $tmp, 0, "C");
                }
                $pdf->Ln(3);
        }

        // Descriptional text.
        $pdf->SetY(215);
        $pdf->SetFont('Arial', '', 11);
        $pdf->SetY($pdf->GetY() + $bottomMargin);
        $pdf->MultiCell(0, 5, $this->beschreibung);
        $pdf->Ln();

        // Signature
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(60, 4, "Passau, den " . date("d.m.Y"), 0, 1, "");

        // Send PDF.
        $pdf->Output("zfs_cert_" . $this->user . ".pdf", "D");
    }

}

?>
