<?php
include 'db.php';

function buscar_jogos() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM jogos WHERE ativo = 1");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function gerar_comprovante($aposta_id, $nome, $jogo, $numeros) {
    require_once('tcpdf/tcpdf.php');
    $pdf = new TCPDF();
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'Comprovante de Aposta #' . $aposta_id, 1, 1, 'C');
    $pdf->Cell(0, 10, 'Nome: ' . $nome, 0, 1);
    $pdf->Cell(0, 10, 'Jogo: ' . $jogo, 0, 1);
    $pdf->Cell(0, 10, 'Números: ' . implode(', ', $numeros), 0, 1);
    $pdf->Output("../uploads/comprovante_$aposta_id.pdf", 'F');
}