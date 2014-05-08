<?php

class certificate_zfs extends certificate {

    public $name = 'Zentrum für Schlüsselkompetenzen';
    public $sem_tree_id = '23bd2f0b9f437b60729290733961853d';
    public $beschreibung = "Das Zentrum für Schlüsselkompetenzen der Universität Passau ist eine zentrale wissenschaftliche Einrichtung, die als Ergänzung zum akademischen Fachstudium Veranstaltungen aus dem Bereich überfachlicher Kompetenzen anbietet. Studierende, die das größtenteils freiwillige Veranstaltungsangebot in Anspruch nehmen, beweisen damit Eigeninitiative und eine hohe Motivation zur persönlichen Weiterentwicklung.";
    
    public function export() {
// Create certificate.
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->setMargins(15, 10, 15);

        $pdf->SetY($pdf->GetY() + 48);


        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(180, 18, $this->fullname, 0, 1, "C");

        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(180, 5, "hat erfolgreich an " . $this->getCount() . " Veranstaltungen aus folgenden Bereichen", 0, 1, "C");
        $pdf->Cell(180, 5, "am Zentrum für Schlüsselkompetenzen der Universität Passau teilgenommen:", 0, 1, "C");

// Default font and margin sizes.
        $textsize = 11;
        $headersize = 11;
        $infosize = 8;
        $cellHeight = 5;
        $topMargin = 4;
        $bottomMargin = 4;
// If there are more than x courses, start to adapt font size.
        if ($this->getCount() > 17) {
            $textsize = max(6, $textsize - round($this->getCount() / 15));
            $headersize = max(6, $headersize - round($this->getCount() / 15));
            $infosize = max(6, $infosize - round($this->getCount() / 15));
            $cellHeight = max(2, $cellHeight - round($this->getCount() / 15));
            $topMargin = max(0, $topMargin - round($this->getCount() / 14));
            $bottomMargin = max(0, $topMargin - round($this->getCount() / 14));
        }

        $pdf->SetY($pdf->GetY() + $topMargin);
//new dBug($this->tree->children);
//die;
// Studiengaenge ausgeben
        foreach ($this->header as $header => $item) {
                $pdf->SetFont('Arial', 'B', $headersize);
                $pdf->Cell(180, $cellHeight, $header, 0, 1, "C");

// Veranstaltungen ausgeben
                foreach ($item as $ver) {
                    //new dBug($ver);
                    $pdf->SetFont('Arial', '', $textsize);
                    $tmp = $ver['Name'];

// Dozenten und Stunden ausgeben
                    if ($ver['dauer']) {
                        $tmp .= ' (' . $ver['dauer'].')';
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
        $pdf->SetY(225);
        $pdf->SetFont('Arial', '', 11);
        $pdf->SetY($pdf->GetY() + $bottomMargin);
        $pdf->MultiCell(0, 5, $this->beschreibung);


// Signature
        $pdf->SetY(270);
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(60, 4, "Passau, den " . date("d.m.Y"), 0, 1, "");

// Send PDF.
        $pdf->Output("zfs_cert_" . $this->user . ".pdf", "D");
    }

}

?>
