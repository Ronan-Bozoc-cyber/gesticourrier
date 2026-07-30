<?php
if (session_status() === PHP_SESSION_NONE) @session_start();

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    die('Non authentifié.');
}

require_once __DIR__ . '/partials/connexion.php';
require_once __DIR__ . '/fpdf/fpdf.php';
require_once __DIR__ . '/partials/parametres.php';

$logId = intval($_GET['id'] ?? 0);
if ($logId <= 0) {
    http_response_code(400);
    die('ID de log invalide.');
}

// Récupérer le log
$stmt = $conn->prepare("SELECT * FROM destruction_logs WHERE id = ?");
$stmt->bind_param('i', $logId);
$stmt->execute();
$log = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$log) {
    http_response_code(404);
    die('Certificat introuvable.');
}

$courriers = json_decode($log['courriers_json'] ?? '[]', true) ?: [];
$dateDestruction = date('d/m/Y à H:i:s', strtotime($log['date_destruction']));
$orgName = $org_settings['raison_sociale'] ?? 'Organisme';
$numCertificat = 'CERT-' . str_pad($logId, 6, '0', STR_PAD_LEFT) . '-' . date('Y', strtotime($log['date_destruction']));

// ─── Création PDF ────────────────────────────────────────────────────────────
class CertificatPDF extends FPDF {
    public string $orgName = '';
    public string $numCertificat = '';

    function Header() {
        // Bande de titre
        $this->SetFillColor(37, 99, 235);
        $this->Rect(0, 0, 210, 28, 'F');
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Helvetica', 'B', 16);
        $this->SetY(6);
        $this->Cell(0, 8, iconv('UTF-8', 'ISO-8859-1', '🗑️ Certificat de Destruction de Courriers'), 0, 1, 'C');
        $this->SetFont('Helvetica', '', 9);
        $this->Cell(0, 6, iconv('UTF-8', 'ISO-8859-1', $this->orgName), 0, 1, 'C');
        $this->SetTextColor(0, 0, 0);
        $this->Ln(4);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Helvetica', 'I', 7);
        $this->SetTextColor(120, 120, 120);
        $this->Cell(0, 5, iconv('UTF-8', 'ISO-8859-1', 'Réf. : ' . $this->numCertificat . '   —   Page ' . $this->PageNo() . '/{nb}'), 0, 0, 'C');
    }
}

$pdf = new CertificatPDF('P', 'mm', 'A4');
$pdf->orgName = $orgName;
$pdf->numCertificat = $numCertificat;
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 20);

// ─── Bloc d'information principale ───────────────────────────────────────────
$pdf->SetFillColor(239, 246, 255);
$pdf->SetDrawColor(37, 99, 235);
$pdf->RoundedRect(14, 32, 182, 48, 4, 'DF');

$pdf->SetFont('Helvetica', 'B', 10);
$pdf->SetTextColor(37, 99, 235);
$pdf->SetXY(20, 35);
$pdf->Cell(0, 7, iconv('UTF-8', 'ISO-8859-1', 'INFORMATIONS DE DESTRUCTION'), 0, 1);

$pdf->SetFont('Helvetica', '', 9);
$pdf->SetTextColor(30, 30, 30);

$infoLines = [
    ['Numéro de certificat', $numCertificat],
    ['Date et heure de destruction', $dateDestruction],
    ['Opération réalisée par', $log['username']],
    ['Durée de conservation appliquée', $log['duree_conservation'] . ' ans'],
    ['Nombre de courriers détruits',
        $log['nb_total'] . ' (' . $log['nb_arrive'] . ' entrant(s) + ' . $log['nb_depart'] . ' sortant(s))'],
];

foreach ($infoLines as [$label, $value]) {
    $pdf->SetX(20);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->Cell(72, 6, iconv('UTF-8', 'ISO-8859-1', $label . ' :'), 0, 0);
    $pdf->SetFont('Helvetica', '', 9);
    $pdf->Cell(0, 6, iconv('UTF-8', 'ISO-8859-1', $value), 0, 1);
}

// ─── Tableau des courriers détruits ──────────────────────────────────────────
$pdf->Ln(4);
$pdf->SetFont('Helvetica', 'B', 10);
$pdf->SetTextColor(37, 99, 235);
$pdf->Cell(0, 7, iconv('UTF-8', 'ISO-8859-1', 'LISTE DES COURRIERS DÉTRUITS'), 0, 1);

// En-têtes du tableau
$cols = [
    ['label' => 'Flux',      'w' => 18],
    ['label' => 'N° Ordre',  'w' => 20],
    ['label' => 'Année',     'w' => 16],
    ['label' => 'Date',      'w' => 24],
    ['label' => 'Contact',   'w' => 38],
    ['label' => 'Catégorie', 'w' => 32],
    ['label' => 'Sujet',     'w' => 48],
];

$pdf->SetFillColor(37, 99, 235);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Helvetica', 'B', 8);
$pdf->SetX(14);
foreach ($cols as $col) {
    $pdf->Cell($col['w'], 7, iconv('UTF-8', 'ISO-8859-1', $col['label']), 1, 0, 'C', true);
}
$pdf->Ln();

// Lignes
$pdf->SetFont('Helvetica', '', 7.5);
$fill = false;
foreach ($courriers as $c) {
    $pdf->SetTextColor(30, 30, 30);
    $bg = $fill ? [248, 250, 252] : [255, 255, 255];
    $pdf->SetFillColor(...$bg);

    $flux = $c['flux'] === 'ARRIVE' ? 'ARRIVEE' : 'DEPART';
    $date = !empty($c['date']) ? date('d/m/Y', strtotime($c['date'])) : '-';
    $sujet = mb_strimwidth($c['sujet_courrier'] ?? '-', 0, 55, '...', 'UTF-8');
    $contact = mb_strimwidth($c['expediteur_nom'] ?? ($c['expediteur'] ?? '-'), 0, 35, '...', 'UTF-8');
    $cat = mb_strimwidth($c['categorie_courrier'] ?? '-', 0, 30, '...', 'UTF-8');

    $rowData = [
        iconv('UTF-8', 'ISO-8859-1', $flux),
        (string)($c['num_ordre'] ?? '-'),
        (string)($c['annee'] ?? '-'),
        $date,
        iconv('UTF-8', 'ISO-8859-1', $contact),
        iconv('UTF-8', 'ISO-8859-1', $cat),
        iconv('UTF-8', 'ISO-8859-1', $sujet),
    ];

    $pdf->SetX(14);
    foreach ($cols as $i => $col) {
        $pdf->Cell($col['w'], 6, $rowData[$i], 1, 0, 'L', true);
    }
    $pdf->Ln();
    $fill = !$fill;
}

// ─── Mention légale ──────────────────────────────────────────────────────────
$pdf->Ln(6);
$pdf->SetFillColor(254, 243, 199);
$pdf->SetDrawColor(180, 83, 9);
$pdf->RoundedRect(14, $pdf->GetY(), 182, 18, 3, 'DF');
$pdf->SetX(18);
$pdf->SetTextColor(120, 53, 15);
$pdf->SetFont('Helvetica', 'B', 8);
$pdf->Cell(0, 5, iconv('UTF-8', 'ISO-8859-1', '⚠️  DOCUMENT OFFICIEL'), 0, 1);
$pdf->SetX(18);
$pdf->SetFont('Helvetica', '', 7.5);
$pdf->MultiCell(174, 5, iconv('UTF-8', 'ISO-8859-1',
    "Ce certificat atteste de la destruction définitive et irréversible des courriers listés ci-dessus, conformément à la durée de conservation légale de {$log['duree_conservation']} an(s). "
    . "Les documents originaux ont été supprimés du système de gestion électronique de documents le $dateDestruction par {$log['username']}."
), 0, 'L');

// ─── Signature ───────────────────────────────────────────────────────────────
$pdf->Ln(4);
$pdf->SetTextColor(60, 60, 60);
$pdf->SetFont('Helvetica', 'I', 8);
$pdf->Cell(0, 5, iconv('UTF-8', 'ISO-8859-1', "Généré automatiquement par OpenGestiCourrier — $numCertificat"), 0, 0, 'R');

// Output
$filename = 'certificat_destruction_' . $numCertificat . '.pdf';
$pdf->Output('D', $filename);
?>
