<?php

require_once "../includes/config.php";
require_once "../includes/auth.php";
require_once "../fpdf/fpdf.php";

staffOnly();

/* ==========================
   GET SALE ID
========================== */

if (!isset($_GET['id'])) {
    die("Invalid Invoice.");
}

$sale_id = intval($_GET['id']);

/* ==========================
   COMPANY SETTINGS
========================== */

$companyResult = mysqli_query(
    $conn,
    "SELECT * FROM company_settings LIMIT 1"
);

$company = mysqli_fetch_assoc($companyResult);

/* ==========================
   SALE DETAILS
========================== */

$sql = "SELECT
            sales.*,
            customers.customer_name,
            customers.mobile,
            customers.email,
            customers.city,
            customers.address
        FROM sales
        INNER JOIN customers
            ON sales.customer_id = customers.id
        WHERE sales.id=?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $sale_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    die("Invoice not found.");
}

$sale = mysqli_fetch_assoc($result);

/* ==========================
   SALE ITEMS
========================== */

$sql = "SELECT
            sale_items.*,
            products.product_name,
            products.product_code
        FROM sale_items
        INNER JOIN products
            ON sale_items.product_id = products.id
        WHERE sale_items.sale_id=?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $sale_id);

mysqli_stmt_execute($stmt);

$items = mysqli_stmt_get_result($stmt);

/* ==========================
   CREATE PDF
========================== */

$pdf = new FPDF('P', 'mm', 'A4');

$pdf->SetTitle("Invoice - " . $sale['invoice_no']);
$pdf->SetAuthor($company['company_name']);
$pdf->SetCreator("Billing Inventory Management System");
$pdf->SetSubject("Tax Invoice");

$pdf->AddPage();

$pdf->SetMargins(10,10,10);

$pdf->SetAutoPageBreak(true,20);

/* ==========================================
   COMPANY HEADER
========================================== */

// Company Logo
if (!empty($company['logo'])) {

    $logoPath = "../uploads/logo/" . $company['logo'];

    if (file_exists($logoPath)) {

        $pdf->Image($logoPath, 10, 10, 28);
    }
}

// Company Name
$pdf->SetXY(42, 10);

$pdf->SetFont('Arial','B',18);

$pdf->SetTextColor(18,82,180);

$pdf->Cell(
    115,
    9,
    strtoupper($company['company_name']),
    0,
    1
);

// Address
$pdf->SetX(42);

$pdf->SetTextColor(0, 0, 0);

$pdf->SetFont('Arial', '', 10);

$pdf->MultiCell(
    100,
    5,
    $company['address']
);

// Phone
$pdf->SetX(42);

$pdf->Cell(
    100,
    5,
    "Phone : " . $company['phone'],
    0,
    1
);

// Email
$pdf->SetX(42);

$pdf->Cell(
    100,
    5,
    "Email : " . $company['email'],
    0,
    1
);

// GST
if (!empty($company['gst_number'])) {

    $pdf->SetX(42);

    $pdf->Cell(
        100,
        5,
        "GSTIN : " . $company['gst_number'],
        0,
        1
    );
}

// Invoice Title
$pdf->SetXY(150, 15);

$pdf->SetFont('Arial','B',18);

$pdf->SetTextColor(0,0,0);

$pdf->Cell(
    48,
    9,
    "TAX INVOICE",
    0,
    1,
    'R'
);
$pdf->Ln(8);

// Divider
$pdf->SetDrawColor(210,210,210);

$pdf->SetLineWidth(0.4);

$pdf->Line(10,48,200,48);

/* ==========================================
   INVOICE DETAILS
========================================== */

$pdf->Ln(8);

/* ==========================================
   BILL TO & INVOICE DETAILS
========================================== */

$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8,'Bill To',0,1);

$pdf->SetFont('Arial','',10);

// Row 1
$pdf->Cell(20,7,'Name',0,0);
$pdf->Cell(75,7,': '.$sale['customer_name'],0,0);

$pdf->Cell(30,7,'Invoice No',0,0);
$pdf->Cell(65,7,': '.$sale['invoice_no'],0,1);

// Row 2
$pdf->Cell(20,7,'Mobile',0,0);
$pdf->Cell(75,7,': '.$sale['mobile'],0,0);

$pdf->Cell(30,7,'Date',0,0);
$pdf->Cell(65,7,': '.date('d-m-Y',strtotime($sale['invoice_date'])),0,1);

// Row 3
$pdf->Cell(20,7,'Address',0,0);
$pdf->Cell(75,7,': '.($sale['address'] ?: '-'),0,0);

$pdf->Cell(30,7,'Payment',0,0);
$pdf->Cell(65,7,': '.$sale['payment_method'],0,1);

$pdf->Ln(3);

$pdf->SetDrawColor(210,210,210);
$pdf->Line(10,$pdf->GetY(),200,$pdf->GetY());

/* Divider */

$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());

$pdf->Ln(5);

/* ==========================================
   PRODUCT TABLE
========================================== */

/* ==========================================
   PRODUCT TABLE HEADER
========================================== */

$pdf->SetFillColor(240,240,240);
$pdf->SetDrawColor(180,180,180);
$pdf->SetLineWidth(0.2);

$pdf->SetFont('Arial','B',10);

$pdf->Cell(10,8,'#',1,0,'C',true);
$pdf->Cell(28,8,'Code',1,0,'C',true);
$pdf->Cell(72,8,'Product',1,0,'C',true);
$pdf->Cell(18,8,'Qty',1,0,'C',true);
$pdf->Cell(31,8,'Price',1,0,'C',true);
$pdf->Cell(31,8,'Total',1,1,'C',true);

$pdf->SetFont('Arial','',10);

$sr = 1;

while($item = mysqli_fetch_assoc($items))
{
    $pdf->Cell(10,8,$sr++,1,0,'C');

    $pdf->Cell(28,8,$item['product_code'],1,0);

    $pdf->Cell(72,8,$item['product_name'],1,0);

    $pdf->Cell(18,8,$item['quantity'],1,0,'C');

    $pdf->Cell(
        31,
        8,
        'Rs. '.number_format($item['price'],2),
        1,
        0,
        'R'
    );

    $pdf->Cell(
        31,
        8,
        'Rs. '.number_format($item['total'],2),
        1,
        1,
        'R'
    );
}

/* ==========================================
   GRAND TOTAL
========================================== */

$pdf->Ln(3);

$pdf->SetFont('Arial','B',11);

// Empty space on left
$pdf->Cell(128,8,'');

// Label
$pdf->Cell(
    32,
    8,
    'Grand Total',
    1,
    0,
    'C',
    true
);

// Amount
$pdf->Cell(
    30,
    8,
    'Rs. '.number_format($sale['grand_total'],2),
    1,
    1,
    'R'
);

$pdf->Ln(8);

$pdf->Ln(10);

/* ==========================================
   FOOTER
========================================== */

$pdf->SetFont('Arial', 'B', 13);

$pdf->Cell(
    0,
    8,
    'Thank You For Shopping!',
    0,
    1,
    'C'
);

$pdf->SetFont('Arial', '', 10);

$pdf->Cell(
    0,
    6,
    'This is a computer generated invoice.',
    0,
    1,
    'C'
);

$pdf->Ln(15);


$pdf->Output(
    'D',
    $sale['invoice_no'] . '.pdf'
);
