<?php

/**
* Anchura de la celda(w): Si esta en 0 significa que la celda se extiende hasta el margen derecho
* Altura de la celda(h): Valor por defecto 0
* Texto(txt): Cadena a imprimir. Valor por defecto: cadena vacía.
* Borde(border): Indica si los bordes deben ser dibujados alrededor de la célula. El valor puede ser un número:
*   0: Sin bordes
*   1: cuadro
*        o una cadena que contiene algunos o todos de los siguientes caracteres (en cualquier orden):
*   L: izquierda
*   T: parte superior
*   R: derecho
*   B: fondo
*   Valor por defecto: 0.
*   ln: Indica donde la posición actual debe ir después de la llamada. Los valores posibles son:
*       0: a la derecha
*       1: Al comienzo de la siguiente línea
*       2: por debajo
*       Poniendo 1esto equivaldrá a escribir 0y llamar a Ln () justo después. Valor por defecto: 0.
*   align: Permite centrar o alinear el texto. Los valores posibles son:
*       L o cadena vacía: alinear a la izquierda (valor por defecto)
*       C: centro
*       R: Alinear a la derecha
*   fill: Indica si el fondo de la celda debe ser pintado ( true) o transparente ( false). Valor por defecto: false.
*   link: URL o identificador devuelto por AddLink ().
*  Cell(float w [, float h [, string txt [, mixed border [, int ln [, string align [, boolean fill [, mixed link]]]]]]])
*  Cell(Anchura(w), Altura(h), txt [, mixed border [, int ln [, string align [, boolean fill [, mixed link]]]]]]])
*/

function firmar($firmas,$pdfOrigen,$pdfDestino,$demo=false){
    if(count($firmas)>0||true)
    {
        $pdf = new FPDI();
        
        //Set the source PDF file
        $pagecount = $pdf->setSourceFile($pdfOrigen);

        for($i=1;$i<=$pagecount;$i++){
        $pdf->AddPage();    
        $pdf->SetTextColor(0,0,0);
        $tpl = $pdf->importPage($i);
        //Use this page as template
        $pdf->useTemplate($tpl);
        
        //Go to 1.5 cm from bottom
        $pdf->SetY(-10);

        if(count($firmas)>0)
        {
            $firma='uploads/firmas/jvalera.png';        
            $pdf->Image($firmas[0]['firma'], 5, $pdf->GetY()-30, 33.78);
            if (array_key_exists(1, $firmas)) 
                $pdf->Image($firmas[1]['firma'], 45, $pdf->GetY()-30, 33.78);
            if (array_key_exists(2, $firmas)) 
                $pdf->Image($firmas[2]['firma'], 85, $pdf->GetY()-30, 33.78);
            if (array_key_exists(3, $firmas)) 
                $pdf->Image($firmas[3]['firma'], 125, $pdf->GetY()-30, 33.78);
            if (array_key_exists(4, $firmas)) 
                $pdf->Image($firmas[4]['firma'], 165, $pdf->GetY()-30, 33.78);
            $pdf->SetY(-10);
            $pdf->SetX(-370);
            
            //Select Arial italic 8
            $pdf->SetFont('Arial','B',8);
            $pdf->SetY(-5);
            
            $nombre=str_pad($firmas[0]['razonsocial'], 10, " ", STR_PAD_BOTH); 
            $pdf->Cell(30, -30, $nombre, 0, 0, 'C');
            //  $pdf->SetY(-5);$pdf->SetX(-370);
            if (array_key_exists(1, $firmas)) {
                $pdf->Cell(10, -30, "", 0, 0, 'C');
                $nombre=str_pad($firmas[1]['razonsocial'], 30, " ", STR_PAD_BOTH); 
                $pdf->Cell(30, -30, substr($nombre,0,30), 0, 0, 'C');
                $pdf->Cell(10, -30, "", 0, 0, 'C');
            }
            if (array_key_exists(2, $firmas)) {
                $nombre=str_pad($firmas[2]['razonsocial'], 30, " ", STR_PAD_BOTH); 
                $pdf->Cell(30, -30, substr($nombre,0,30), 0, 0, 'C');
                $pdf->Cell(10, -30, "", 0, 0, 'C');
            }
            if (array_key_exists(3, $firmas)) {
                $nombre=str_pad($firmas[3]['razonsocial'], 30, " ", STR_PAD_BOTH); 
                $pdf->Cell(30, -30, substr($nombre,0,30), 0, 0, 'C');
                $pdf->Cell(10, -30, "", 0, 0, 'C');
            }
            if (array_key_exists(4, $firmas)) {
                $nombre=str_pad($firmas[4]['razonsocial'], 30, " ", STR_PAD_BOTH); 
                $pdf->Cell(30, -30, substr($nombre,0,30), 0, 0, 'C');
            }


            $pdf->SetFont('Arial','',8);
            $pdf->SetY(-1);
            //$pdf->SetX(-370);   
            $cargo=str_pad($firmas[0]['cargo'], 10, " ", STR_PAD_BOTH); 
            $pdf->Cell(30, -30, $cargo, 0, 0, 'C');
            $pdf->Cell(10, -30, "", 0, 0, 'C');
            if (array_key_exists(1, $firmas)) {
                $cargo=str_pad($firmas[1]['cargo'], 30, " ", STR_PAD_BOTH); 
                $pdf->Cell(30, -30, substr($cargo,0,30), 0, 0, 'C');
                $pdf->Cell(10, -30, "", 0, 0, 'C');
            }
            if (array_key_exists(2, $firmas)) {
                $cargo=str_pad($firmas[2]['cargo'], 30, " ", STR_PAD_BOTH); 
                $pdf->Cell(30, -30, substr($cargo,0,30), 0, 0, 'C');
                $pdf->Cell(10, -30, "", 0, 0, 'C');
            }
            if (array_key_exists(3, $firmas)) {
                $cargo=str_pad($firmas[3]['cargo'], 30, " ", STR_PAD_BOTH); 
                $pdf->Cell(30, -30, substr($cargo,0,30), 0, 0, 'C');
                $pdf->Cell(10, -30, "", 0, 0, 'C');
            }
            if (array_key_exists(4, $firmas)) {
                $cargo=str_pad($firmas[4]['cargo'], 30, " ", STR_PAD_BOTH); 
                $pdf->Cell(30, -30, substr($cargo,0,30), 0, 0, 'C');
            }
        }
        
        //$pdf->Cell(0, -30, "Administrador", 0, 0, 'C');
        //$pdf->Cell(0, -30, "Administrador", 0, 0, 'C');


        // F I R M A   2
        // $pdf->SetY(0);
        // $firma='uploads/firmas/jvalera.png';
        // $pdf->Image($firma, 45, $pdf->GetY()-30, 33.78);
        // $pdf->SetY(-5); $pdf->Cell(100, -30, "Ebert Zerpa", 0, 0, 'C');
        // $pdf->SetY(-1);$pdf->SetX(-290);$pdf->Cell(0, -30, "Administrador", 0, 0, 'C');


        
        // F I R M A   3
        // $pdf->SetY(-15);
        // $firma='uploads/firmas/jvalera.png';
        // $pdf->Image($firma, 45, $pdf->GetY()-30, 33.78);
        // $pdf->SetY(-5); $pdf->Cell(100, -30, "Ebert Zerpa", 0, 0, 'C');
        // $pdf->SetY(-1);$pdf->SetX(-210);$pdf->Cell(0, -30, "Administrador", 0, 0, 'C');
        if($demo)
        {
            $pdf->SetY(-50);
            $pdf->SetX(100);
            $pdf->SetTextColor(240,240,240);
            // $pdf->SetTextColor(130,130,130);
            $pdf->SetFont('Arial','B',50);
            $pdf->Cell(30, -170, "NO DEFINITIVO", 0, 0, 'C');
        }
        }
        $pdf->Output($pdfDestino, "F");
        // $pdf->Output($pdfDestino, "D");
	
    }
}

function marcarAgua($pdfOrigen,$pdfDestino){
        $pdf = new FPDI();
        $pagecount = $pdf->setSourceFile($pdfOrigen);

        for($i=1;$i<=$pagecount;$i++){
            $pdf->AddPage();    
            $pdf->SetTextColor(0,0,0);
            $tpl = $pdf->importPage($i);
            $pdf->useTemplate($tpl);
            
            
            $pdf->SetY(-50);
            $pdf->SetX(100);
            $pdf->SetTextColor(240,240,240);
            // $pdf->SetTextColor(130,130,130);
            $pdf->SetFont('Arial','B',50);
            $pdf->Cell(30, -170, "NO DEFINITIVO", 0, 0, 'C');
        }
        $pdf->Output($pdfDestino, "F");
	
    }