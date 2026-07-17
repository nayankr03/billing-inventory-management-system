<?php

require_once "../fpdf/fpdf.php";

$pdf = new FPDF();

$pdf->AddPage();

$pdf->SetFont('Arial','B',16);

$pdf->Cell(0,10,'FPDF Working Successfully!',0,1,'C');

$pdf->Output();