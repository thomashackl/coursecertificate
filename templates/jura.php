<?php

class certificate_jura extends certificate {

    public $order = 2;

    public $name = 'Schlüsselkompetenzen Jura';
    public $beschreibung = "Das Zentrum für Karriere und Kompetenzen (ZKK) bietet als Ergänzung zum akademischen Fachstudium Seminare und IT-Kurse zum Erwerb überfachlicher Kompetenzen an. Durch diese Kompetenzen werden Reflexivität und Verantwortungsbewusstsein gestärkt sowie selbstorganisiertes und eigenständiges Lernen vermittelt und gefördert. Studierende, die in allen drei Kompetenzbereichen Seminare besucht haben, beweisen damit Eigeninitiative und eine hohe Motivation zur persönlichen Weiterentwicklung.";
    public $sem_tree_id = '814e28f539e0952dfe1f35bca8800bee';

    public $process_only_semtree_ids = [
        '814e28f539e0952dfe1f35bca8800bee',
        'sbZfS05'
    ];
    public $visible_semtree_ids = [
        '303c7d8ba8ea844b29849deff2efbf0a',
        'cfe64d3e5142bcbe212daacc9aaec327',
        'c4f61f4304c79ea9f97ba285d0fb8c4b',
        'sbZfS05'
    ];

    public function export($seminars) {
        $this->loadSeminarsForPDF();
        // Create certificate.
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->setMargins(15, 10, 15);

        $pdf->SetY($pdf->GetY() + 75);


        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(180, 18, legacy_studip_utf8decode($this->fullname), 0, 1, "C");

        // Get current locale time setting.
        $currentLocale = setlocale(LC_TIME, "0");
        // Set to German locale.
        setlocale(LC_TIME, "de_DE");
        $pdf->SetFont('Arial', '', 11);
        $pdf->MultiCell(180, 5, legacy_studip_utf8decode("hat am Zentrum für Karriere und Kompetenzen der " .
                "Universität Passau im Zeitraum von " .
                strftime('%B %Y', $this->start) .
                " bis " . strftime('%B %Y', $this->end) . " an " . $this->getCount() .
                " Veranstaltungen aus folgenden Bereichen erfolgreich teilgenommen:"), 0, "C");
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
        if ($this->getCount() > 21) {
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
                $pdf->Cell(180, $cellHeight, legacy_studip_utf8decode($header), 0, 1, "C");

                // Veranstaltungen ausgeben
                foreach ($item as $ver) {
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
                    $pdf->MultiCell(180, $cellHeight, legacy_studip_utf8decode($tmp), 0, "C");
                }
                $pdf->Ln(3);
        }

        // Descriptional text.
        $pdf->SetY(215);
        $pdf->SetFont('Arial', '', 11);
        $pdf->SetY($pdf->GetY() + $bottomMargin);
        $pdf->MultiCell(0, 5, legacy_studip_utf8decode($this->beschreibung));
        $pdf->Ln();

        // Signature
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(60, 4, "Passau, den " . date("d.m.Y"), 0, 1, "");

        // Send PDF.
        $pdf->Output("zfs_cert_" . $this->user . ".pdf", "D");
    }

    public function loadLecturers(&$data) {
        $sql = "SELECT md5.`user_id` FROM seminar_user su
                JOIN auth_user_md5 md5 USING(user_id)
            WHERE su.seminar_id = ? AND status = 'dozent'
            ORDER BY su.`position`, md5.`Nachname`, md5.`Vorname`, md5.`username`";
        $db = DBManager::get();
        $stmt = $db->prepare($sql);
        $stmt->execute([$data['seminar_id']]);
        $lecturers = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        $data['dozenten'] = [];
        foreach ($lecturers as $l) {
            $u = User::find($l);
            $name = $u->getFullname();
            $datafield = $db->fetchFirst(
                "SELECT `content`
                FROM `datafields_entries`
                WHERE `range_id`=?
                    AND `datafield_id`='e96d12f041e6a432e8f9c77a026b3731'",
                [$l]);
            if ($datafield[0]) {
                $name .= ' ('.$datafield[0].')';
            }
            $data['dozenten'][] = $name;
        }
    }

}
