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
    public $process_only_ids = [
        '814e28f539e0952dfe1f35bca8800bee',
        'sbZfS05'
    ];
    public $visible_sem_tree_ids = array(
        '303c7d8ba8ea844b29849deff2efbf0a',
        'cfe64d3e5142bcbe212daacc9aaec327',
        'c4f61f4304c79ea9f97ba285d0fb8c4b',
        'sbZfS05'
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
                    $tmp .= ' (' . date('Y/m/d', $ver['start']);

                    if (date('Y/m/d', $ver['start']) != date('Y/m/d', $ver['end'])) {
                        $tmp .= ' - ' . date('Y/m/d', $ver['end']);
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
        $pdf->Cell(60, 4, "Passau, " . date("Y/m/d"), 0, 1, "");

// Send PDF.
        $pdf->Output("cert_" . $this->user . ".pdf", "D");
    }

}
