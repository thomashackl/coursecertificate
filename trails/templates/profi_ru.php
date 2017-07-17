<?php

class certificate_profi_ru extends certificate {

    public $order = 3;

    public $name = 'Profi.RU';
    public $sem_tree_id = 'dfceefe635ecec2c1d81d90e9dc772cb';

    public function oldDataFetchIsNowObsolete() {
        $db = new DB_Seminar();

        $fullname = get_fullname_from_uname($_POST['user']);

        $selectedCourses = array();
        $count = 0;

// Check which sem_tree items are relevant
        $semTree = new StudipSemTree(array('visible_only' => true));
        $semTreeIds = array();
        foreach ($_POST['sem_tree_ids'] as $semTreeItem) {
            $semTreeIds = array_merge($semTreeIds, $semTree->getKidsKids($semTreeItem), array($semTreeItem));
        }

// Get course data.
        $query = "SELECT s.Name, s.Seminar_id, s.ects, s.Beschreibung, 
        st.sem_tree_id AS sem_tree_id, st.name AS sem_tree_name
    FROM seminare s
        JOIN seminar_sem_tree sst ON (s.Seminar_id=sst.seminar_id)
        JOIN sem_tree st ON (sst.sem_tree_id=st.sem_tree_id)
    WHERE s.Seminar_id IN ('" . implode("', '", $_POST['courses']) . "')
        AND st.sem_tree_id IN ('" . implode("', '", $semTreeIds) . "')
    ORDER BY st.priority ASC, s.start_time ASC, s.VeranstaltungsNummer ASC, s.Name ASC;";
        $db->query($query);
        while ($db->next_record()) {
            $selectedCourses[$db->f('sem_tree_id')]['name'] = $db->f('sem_tree_name');
            $selectedCourses[$db->f('sem_tree_id')]['sem'][] = array(
                'id' => $db->f("Seminar_id"),
                'name' => $db->f("Name"),
                'ects' => $db->f('ects'),
                'description' => $db->f('Beschreibung')
            );
            $count++;
        }
    }

    public function export() {


// Create certificate.
        $pdf = new FPDF();
        $pdf->AddPage();

// Header
//$pdf->Image('img/uni_passau_logo.png', 100, 5);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Write(5, 'Zentrum für Schlüsselkompetenzen');
        $pdf->Ln();
        $pdf->Write(5, 'Department für Katholische Theologie');
        $pdf->SetX(0);
        $pdf->SetY($pdf->GetY() + 40);

// Start
        $pdf->SetFont('Arial', 'B', 32);
        $pdf->Cell(0, 8, "Zertifikat", 0, 1, "C");
        $pdf->SetY($pdf->GetY() + 18);

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, "Hiermit bestätigen wir", 0, 1, "C");
        $pdf->SetY($pdf->GetY() + 8);

        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 8, $this->fullname, 0, 1, "C");
        $pdf->SetY($pdf->GetY() + 8);

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, "die erfolgreiche Teilnahme am studienbegleitenden Qualifikationsprogramm", 0, 1, "C");
        $pdf->SetY($pdf->GetY() + 8);

        $pdf->SetFont('Arial', '', 20);
        $pdf->Cell(0, 12, $parent_name, 0, 1, "C");
        $pdf->SetFont('Arial', '', 14);
        $pdf->Cell(0, 12, $parent_info, 0, 1, "C");
        $pdf->SetY($pdf->GetY() + 16);


// Ausbildungsinhalte
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 12, "Ausbildungsinhalte", 0, 1);
        $pdf->SetY($pdf->GetY() + 4);

        foreach ($this->tree->getChildrenWithSeminars() as $item) {
            $pdf->SetFont('Arial', '', 14);
            $pdf->Cell(0, 6, $item->name, 0, 1, "");
//$pdf->SetFont('Arial', '', 8);
            //$pdf->Cell(0, 6, $item['info'], 0, 1, "");
        }
        $pdf->SetFont('Arial', '', 10);


// Signatur
        $pdf->SetY(260);
        $pdf->Cell(60, 4, "Passau, den " . date("d.m.Y"), 0, 1, "");
        $pdf->Line(130, $pdf->GetY(), 190, $pdf->GetY());
        $pdf->SetX(130);
        $pdf->Cell(60, 4, $_REQUEST['sig1'], 0, 1, "C");
        $pdf->SetX(130);
        $pdf->Cell(60, 4, $_REQUEST['sig2'], 0, 1, "C");
        $pdf->SetY($pdf->GetY() - 10);

// Seitenumbruch
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 20);
        $pdf->Cell(0, 12, $parent_name, 0, 1, "C");
        $pdf->SetFont('Arial', '', 14);
        $pdf->Cell(0, 12, $parent_info, 0, 1, "C");
        $pdf->SetY($pdf->GetY() + 10);

        $pdf->Cell(0, 12, "Nachweis der erfolgreich absolvierten Profilelemente", 0, 1, "");

        foreach ($this->tree->getChildrenWithSeminars() as $item) {
            $j++;
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 8, $j . ". " . $item->name, 0, 1, "");
            //foreach ($item['sem'] as $course) {
            foreach ($item->seminare as $course) {
                $pdf->SetFont('Arial', 'U', 10);
                $pdf->Cell(0, 8, $course["Name"] . " (" . $course["ects"] . " ECTS)", 0, 1, "");
                $pdf->SetFont('Arial', '', 8);
                $pdf->MultiCell(0, 4, $course["Beschreibung"], 0, 1, "");
                $pdf->SetY($pdf->GetY() + 10);
            }
        }

// PDF an Benutzer senden
        $pdf->Output("Profi.RU_" . $_POST['user'] . ".pdf", "D");
    }

}
