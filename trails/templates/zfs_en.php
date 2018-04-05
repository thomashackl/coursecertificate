<?php

class certificate_zfs_en extends certificate {

    public $order = 1;

    public $name = 'KompetenzPAss englisch';
    public $sem_tree_id = '23bd2f0b9f437b60729290733961853d';
    public $beschreibung = "The seminars and computer literacy courses offered by the Centre for Careers and " .
        "Competences (ZKK) give students the opportunity to develop transversal skills to complement their " .
        "degree-related expertise. This strengthens their ability for self-reflection and promotes a sense of " .
        "resposonsibility while developing and training their self-management and autonomous learning skills. " .
        "As most ZKK courses are optional, students who make use of this offering demonstrate a high degree of ".
        "initiative and motivation for personal growth.";
    public $exclude_sem_tree_ids = array(
        'c0b4af8e91ef5022141ec58f17e69b21',
        '814e28f539e0952dfe1f35bca8800bee'
    );
    public $invisible_sem_tree_ids = array(
        'c0b4af8e91ef5022141ec58f17e69b21',
        '814e28f539e0952dfe1f35bca8800bee',
        '30906bf846b53c8a9e2fe21d2180f818',
        '6a29bdcc362a3c2f5ea09efda19c5e49',
        '42e7d7a3481c706ed1d54dc072d552e0',
        '701cff7eda6876be28da2fcd62ded48a',
        '0b30d769e0dcf80fda308272b87814ca',
        '7df63ae06d54e6c6ed7dcd48b80259a0',
        '9d88ab9caba20e819765c4b5a96b63b2',
        '855037ff174597580d4ba3f1267a9328',
        '304aa7e5e378dc260ffa596d7df50559',
        '04f08c9ca84f098d236d2befd6da7940',
        '2b86bdce5d33e98ac4f205e841c2354b',
        '0db0a0bb3aab3cd62dcd8c17fa20b312'
    );

    public function export() {
        $this->loadSeminarsForPDF('en_GB');
// Create certificate.
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->setMargins(15, 10, 15);

        $pdf->SetY($pdf->GetY() + 48);


        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(180, 18, legacy_studip_utf8decode($this->fullname), 0, 1, "C");

        // Get current locale time setting.
        $currentLocale = setlocale(LC_TIME, "0");
        // Set to German locale.
        setlocale(LC_TIME, "en_GB");
        $pdf->SetFont('Arial', '', 11);
        $pdf->MultiCell(180, 5,
			legacy_studip_utf8decode("has completed " . $this->getCount() .
                " courses at the Centre for Careers and Competences (ZKK) at the University of Passau " .
                "from the subject areas below in the period " . strftime('%B %Y', $this->start) .
                " to " . strftime('%B %Y', $this->end) . ":"), 0, "C");
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
        if ($this->getCount() > 23) {
            $textsize = max(6, $textsize - round($this->getCount() / 15));
            $headersize = max(6, $headersize - round($this->getCount() / 15));
            $infosize = max(6, $infosize - round($this->getCount() / 15));
            $cellHeight = max(2, $cellHeight - round($this->getCount() / 15));
            $topMargin = max(0, $topMargin - round($this->getCount() / 14));
            $bottomMargin = max(0, $topMargin - round($this->getCount() / 14));
        }

        $pdf->SetY($pdf->GetY() + $topMargin);
        $log = fopen($GLOBALS['TMP_PATH'] . '/cert.log', 'w');
        fwrite($log, print_r($this->header, 1));
        fclose($log);
// Studiengaenge ausgeben
        foreach ($this->header as $header => $item) {
                $pdf->SetFont('Arial', 'B', $headersize);
                $pdf->Cell(180, $cellHeight, legacy_studip_utf8decode($header), 0, 1, "C");

// Veranstaltungen ausgeben
                foreach ($item as $ver) {

                    $course = Course::find($ver['Seminar_id']);

                    $pdf->SetFont('Arial', '', $textsize);
                    $tmp = is_a($course->name, 'I18NString') ?
                        ($course->name->translation('en_GB') ?: $ver['Name']) :
                        $ver['Name'];

// Dozenten und Stunden ausgeben
                    $tmp .= ' (' . date('d/m/Y', $ver['start']);

                    if (date('d/m/Y', $ver['start']) != date('d/m/Y', $ver['end'])) {
                        $tmp .= ' - ' . date('d/m/Y', $ver['end']);
                    }

                    if ($ver['dauer']) {
                        $tmp .= ', ' . $ver['dauer'];
                    }
                    $tmp .= ')';
                    if (!empty($ver['dozenten'])) {
                        $tmp .= ' - ';
                        $tmp .= implode(", ", $ver['dozenten']);
                    }
                    $pdf->MultiCell(180, $cellHeight, legacy_studip_utf8decode($tmp), 0, "C");
                }
                $pdf->Ln(3);
        }

// Descriptional text.
        $pdf->SetY(225);
        $pdf->SetFont('Arial', '', 11);
        $pdf->SetY($pdf->GetY() + $bottomMargin);
        $pdf->MultiCell(0, 5, legacy_studip_utf8decode($this->beschreibung));


// Signature
        $pdf->SetY(270);
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(60, 4, "Passau, " . date("d/m/Y"), 0, 1, "");

// Send PDF.
        $pdf->Output("cert_" . $this->user . ".pdf", "D");
    }

}
