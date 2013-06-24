<?php

require('fpdf/fpdf.php');
require('fpdi/fpdi.php');

// Descriptional text underneath the course list.
$beschreibung = "Das Zentrum für Schlüsselqualifikationen der Universität Passau ist eine zentrale wissenschaftliche Einrichtung, die als Ergänzung zum akademischen Fachstudium Kurse aus dem Bereich überfachlicher Kompetenzen anbietet. Studierende, die das größtenteils freiwillige Kursangebot in Anspruch nehmen, beweisen damit Eigeninitiative und eine hohe Motivation zur persönlichen Weiterentwicklung.";

$db = new DB_Seminar();

$fullname = get_fullname_from_uname($_POST['user']);

$selectedCourses = array();
$count = 0;

// Check which sem_tree items are relevant
$semTree = new StudipSemTree(array('visible_only' => true));
$semTreeIds = array();
foreach ($_POST['sem_tree_ids'] as $semTreeItem) {
    $semTreeIds = array_merge($semTreeIds, 
        $semTree->getKidsKids($semTreeItem), array($semTreeItem));
}

// Get courses.
$query = "SELECT s.Name, s.Seminar_id, st.sem_tree_id AS sem_tree_id, 
        st.name AS sem_tree_name, s.start_time
    FROM seminare s
        JOIN seminar_sem_tree sst ON (s.Seminar_id=sst.seminar_id)
        JOIN sem_tree st ON (sst.sem_tree_id=st.sem_tree_id)
    WHERE s.Seminar_id IN ('".implode("', '", $_POST['courses'])."')
        AND st.sem_tree_id IN ('".implode("', '", $semTreeIds)."')
    ORDER BY st.priority ASC, s.start_time ASC, s.VeranstaltungsNummer ASC, s.Name ASC;";
$db->query($query);
while ($db->next_record()) {
    if (!stristr($db->f("name"), "Nachrangige Berücksichtigung")) {
        $starttime = veranstaltung_beginn($db->f("Seminar_id"), true);
        // Set correct minimal time.
        if (!$mintime || $starttime < $mintime) {
            $mintime = $starttime;
        }
        if (!$maxtime || $starttime > $maxtime) {
            $maxtime = $starttime;
        }
        $db2 = new DB_Seminar();
        // Get lecturers.
        $lecturers = array();
        $query2 = "SELECT auth_user_md5.user_id, ".
            $GLOBALS['_fullname_sql']['full']." AS fullname 
            FROM auth_user_md5 
                JOIN user_info USING (user_id)
            WHERE auth_user_md5.user_id IN ('".
                implode("', '", array_keys(get_seminar_dozent($db->f("Seminar_id")))).
                "')
            ORDER BY Nachname ASC, Vorname ASC";
        $db2->query($query2);
        while ($db2->next_record()) {
            $name = $db2->f("fullname");
            $db3 = new DB_Seminar();
            $query3 = "SELECT e.content 
                FROM datafields_entries e 
                    JOIN datafields d ON (e.datafield_id=d.datafield_id) 
                WHERE e.range_id='".$db2->f("user_id")."' 
                    AND d.name='Berufsbezeichnung' LIMIT 1";
            $db3->query($query3);
            if ($db3->next_record()) {
                $name .= " (".$db3->f("content").")";
            }
            $lecturers[] = $name;
        }
        // Get duration.
        $duration = 0;
        $query3 = "SELECT content
            FROM datafields_entries
            WHERE datafield_id='8554741ae3a5cfcc38c6741ab0c9ce5e' 
                AND range_id = '".$db->f("Seminar_id")."'";
        $db2->query($query3);
        if ($db2->next_record()) {
            $duration = intval($db2->f("content"));
        }
        $selectedCourses[$db->f('sem_tree_id')]['name'] = $db->f('sem_tree_name');
        $selectedCourses[$db->f('sem_tree_id')]['sem'][] = array(
                'id' => $db->f("Seminar_id"),
                'name' => $db->f("Name"),
                'dozenten' => $lecturers,
                'dauer' => $duration
            );
        $count++;
    }
}

    // Get first and last date of all courses.
    $query = "SELECT MIN(`date`) AS minimum, MAX(`end_time`) AS maximum 
        FROM `termine` WHERE `range_id` IN ('".
        implode("', '", $_POST['courses'])."')";
    $db->query($query);
    $db->next_record();

    // Create certificate.
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->setMargins(15, 10, 15);

    $pdf->SetY($pdf->GetY() + 75);


    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(180, 18, $fullname, 0, 1, "C");

    // Get current locale time setting.
    $currentLocale = setlocale(LC_TIME, "0");
    // Set to German locale.
    setlocale(LC_TIME, "de_DE");
    $pdf->SetFont('Arial', '', 11);
    $pdf->MultiCell(180, 5, "hat am Zentrum für Schlüsselqualifikationen der ".
        "Universität Passau im Zeitraum von ".
        strftime('%B %Y', $db->f("minimum")).
        " bis ".strftime('%B %Y', $db->f("maximum"))." an ".$count.
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
    if ($count > 14) {
        $textsize = max(6, $textsize - round($count / 15));
        $headersize = max(6, $headersize - round($count / 15));
        $infosize = max(6, $infosize - round($count / 15));
        $cellHeight = max(2, $cellHeight - round($count / 15));
        $topMargin = max(0, $topMargin - round($count / 14));
        $bottomMargin = max(0, $topMargin - round($count / 14));
    }

    $pdf->SetY($pdf->GetY() + $topMargin);

    // Studiengaenge ausgeben
    foreach ($selectedCourses as $item) {
        if (!empty($item['sem'])) {
            $pdf->SetFont('Arial', 'B', $headersize);
            $pdf->Cell(180, $cellHeight, $item['name'], 0, 1, "C");

            // Veranstaltungen ausgeben
            foreach ($item['sem'] as $ver) {
                $pdf->SetFont('Arial', '', $textsize);
                $tmp = $ver['name'];

                // Dozenten und Stunden ausgeben
                if ($ver['dauer'] != 0) {
                    $hours = ($ver['dauer'] == 1 ? ' Stunde' : ' Stunden');
                    $tmp .= ' ('.$ver['dauer'].$hours.')';
                }
                if (!empty($ver['dozenten'])) {
                    $tmp .= ' - ';
                    $tmp .= implode(", ", $ver['dozenten']);
                }
                $pdf->MultiCell(180, $cellHeight, $tmp, 0, "C");
            }
            $pdf->Ln(3);
        }
    }
    
    // Descriptional text.
    $pdf->SetY(215);
    $pdf->SetFont('Arial', '', 11);
    $pdf->SetY($pdf->GetY() + $bottomMargin);
    $pdf->MultiCell(0, 5, $beschreibung);
    $pdf->Ln();

    // Signature
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(60, 4, "Passau, den " . date("d.m.Y"), 0, 1, "");

    // Send PDF.
    $pdf->Output("zfs_cert_" . $_POST['user'] . ".pdf", "D");
?>
