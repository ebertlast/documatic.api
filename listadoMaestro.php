<?php
require('rotation.php');
class PDF extends PDF_Rotate
{
	public $codigo = 'PGD-01-F2';
	public $version = '001';
	function Header()
	{
    //Put the watermark
		// $this->SetFont('Arial','B',50);
		// $this->SetTextColor(255,192,203);
		// $this->RotatedText(35,190,'C o p i a   C o n t r o l a d a',45);
    

    $this->SetFont('Arial','B',12);
    $this->Cell(45,7,utf8_decode('Código: ').$this->codigo/*.' '.$this->GetY()*/,1,0,'C');


    $this->Cell(150,21,'LISTADO MAESTRO DE DOCUMENTOS DE',1,0,'C');
    $this->SetY(21);
    $this->SetX(50);
    $this->Cell(150,7,'DESARROLLOS PHARMACEUTICOS DE COLOMBIA SAS',0,0,'C');
    $this->SetY(10);
    $this->SetX(55);
    $this->Cell(150,7,'',0,0,'C');
    $this->Cell(70,21,'',1,0,'C');

    $this->Image('logo.png',210,10,63);
    $this->Ln();
    $this->SetY(17);
    $this->SetX(10);
    $this->Cell(45,7,utf8_decode('Versión: ').$this->version,1,0,'C');
    $this->Ln();
    $this->Cell(45,7,utf8_decode('Página ').$this->PageNo().' de {nb}',1,0,'C');

    $this->Ln(10);
		$this->SetFillColor(192, 192, 192);
    $this->Cell(105,7,utf8_decode('PROCESO'),1,0,'C',1);
    $this->Cell(35,7,utf8_decode('CÓDIGO'),1,0,'C',1);
    $this->Cell(20,7,utf8_decode('VERSIÓN'),1,0,'C',1);
    $this->Cell(30,7,utf8_decode('APLICACIÓN'),1,0,'C',1);
    $this->Cell(30,7,utf8_decode('VIGENCIA'),1,0,'C',1);
    $this->Cell(45,7,utf8_decode('DISTRIBUCIÓN'),1,0,'C',1);
    $this->SetFillColor(255,255,255);
    $this->Ln();

		 // Logo
    // $this->Image('logo.png',10,8,33);
    // Arial bold 15
    // $this->SetFont('Arial','B',12);
    // Movernos a la derecha
    // $this->Cell(80);
    // Título
    // $this->Cell(10,10,'Version: '.$this->version,1,0,'C');
    // Salto de línea
    // $this->Ln(20);
	}

	function RotatedText($x, $y, $txt, $angle)
	{
    //Text rotated around its origin
		$this->Rotate($angle,$x,$y);
		$this->Text($x,$y,$txt);
		$this->Rotate(0);
	}

	// Pie de página
	function Footer()
	{
    // Posición: a 1,5 cm del final
		$this->SetY(-15);
    // Arial italic 8
		$this->SetFont('Arial','I',8);
    // Número de página
		// $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
	}
}
function generarListado($pdfDestino,$codigo,$version,$gestiones,$documentos){
	// $pdf = new FPDF();
	// $pdf->AddPage('L', 'Letter');
	
	// $pdf->SetFont('Arial','',12);
	// $txt="FPDF is a PHP class which allows to generate PDF files with pure PHP, that is to say ".
	// "without using the PDFlib library. F from FPDF stands for Free: you may use it for any ".
	// "kind of usage and modify it to suit your needs.\n\n";
	// for($i=0;$i<25;$i++) 
	// 	$pdf->MultiCell(0,5,$txt,0,'J');
	// $pdf->Output($pdfDestino, 'F');


	$pdf=new PDF();
	$pdf->version=$version;
	$pdf->AliasNbPages();
	$pdf->AddPage('L', 'Letter');
	// $pdf->AddPage();
	$pdf->SetFont('Arial','',12);

	// $pdf->Cell(95,7,count($gestiones),1,0,'C');
	for($i = 0; $i < count($gestiones); $i++){
		$pdf->SetFont('Arial','B',12);
		$pdf->SetFillColor(55, 96, 145);
		// $pdf->SetTextColor(255,255,255);
		$pdf->Cell(265,7,($i+1).'- '.($gestiones[$i]['denominacion']).(' ('.$gestiones[$i]['gestionid'].')'),1,0,'C',1);
		// $pdf->SetTextColor(0,0,0);
		$pdf->SetFillColor(255, 255, 255);
    $pdf->Ln();

		$pdf->SetFont('Arial','',8);

		for($j = 0; $j < count($documentos); $j++){
			if($documentos[$j]['convencionid']==='P'){
				$pdf->SetFillColor(197, 217, 241);
			}

			if($documentos[$j]['gestionid']===$gestiones[$i]['gestionid']&&false){
				$pdf->Cell(95,7,$documentos[$j]['denominacion'],1,0,'C');
				$pdf->Cell(45,7,$documentos[$j]['archivoid'],1,0,'C');
				$pdf->Cell(20,7,substr('000'.$documentos[$j]['version'],-3,3),1,0,'C');
				$date = new DateTime($documentos[$j]['fecha']);
				$pdf->Cell(30,7,$date->format('d/m/Y'),1,0,'C');
				$date = new DateTime($documentos[$j]['fechaexp']);
				$pdf->Cell(30,7,$date->format('d/m/Y'),1,0,'C');
				// $pdf->Cell(45,7,utf8_decode('Centro de Documentación e Información (CDI)'),1,0,'C');
				$pdf->Cell(45,7,utf8_decode('Centro de Doc. e Inf. (CDI)'),1,0,'C');
				// $pdf->MultiCell(45, 7, utf8_decode('Centro de Documentación e Información (CDI)'), 1, 'L', false);
    		$pdf->Ln();
			}

			if($documentos[$j]['gestionid']===$gestiones[$i]['gestionid']){
				// $pdf->SetTopMargin(5);
				$alto = (strlen($documentos[$j]['denominacion'])>58)?14:7;
				$pdf->MultiCell(105, 7, $documentos[$j]['denominacion'], 1, 'L', true);
				$pdf->SetY($pdf->GetY()-7);
				$pdf->SetX(115);
				// $pdf->MultiCell(45, 7, $documentos[$j]['archivoid'], 1, 'L', false);
				$pdf->Cell(35,7,$documentos[$j]['archivoid'],1,0,'C',1);
				$pdf->Cell(20,7,substr('000'.$documentos[$j]['version'],-3,3),1,0,'C',1);
				$date = new DateTime($documentos[$j]['fecha']);
				$pdf->Cell(30,7,$date->format('d/m/Y'),1,0,'C',1);
				$date = new DateTime($documentos[$j]['fechaexp']);
				$pdf->Cell(30,7,$date->format('d/m/Y'),1,0,'C',1);
				$pdf->Cell(45,7,utf8_decode('Centro de Doc. e Inf. (CDI)'),1,0,'C',1);

    		$pdf->Ln();
    		
			}
			$pdf->SetFillColor(255, 255, 255);
  	}	
		$pdf->SetFont('Arial','',12);

	}

	// $txt="FPDF is a PHP class which allows to generate PDF files with pure PHP, that is to say ".
	// "without using the PDFlib library. F from FPDF stands for Free: you may use it for any ".
	// "kind of usage and modify it to suit your needs.\n\n";
	// for($i=0;$i<20;$i++) 
	// 	$pdf->MultiCell(0,5,$txt,0,'J');
	$pdf->Output($pdfDestino, 'F');
}

?>