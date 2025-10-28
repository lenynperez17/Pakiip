<?php
require('fpdf.php');
define('EURO', chr(128) );
define('EURO_VAL', 6.55957 );
 
 
//////////////////////////////////////
// Public functions                 //
//////////////////addCadreTVAs////////////////////
//  function sizeOfText( $texte, $larg )
//  function addSociete( $nom, $adresse )
//  function fact_dev( $libelle, $num )
//  function addDevis( $numdev )
//  function addFacture( $numfact )
//  function addDate( $date )
//  function addClient( $ref )
//  function addPageNumber( $page )
//  function addClientAdresse( $adresse )
//  function addReglement( $mode )
//  function addEcheance( $date )
//  function addNumTVA($tva)
//  function addReference($ref)
//  function addCols( $tab )
//  function addLineFormat( $tab )
//  function lineVert( $tab )
//  function addLine( $ligne, $tab )
//  function addRemarque($remarque)
//  function addCadreTVAs()
//  function addCadreEurosFrancs()
//  function addTVAs( $params, $tab_tva, $invoice )
//  function temporaire( $texte )
 
class PDF_Invoice extends FPDF
{
// private variables
var $colonnes;
var $format;
var $angle=0;




protected $javascript;
    protected $n_js;

    function IncludeJS($script, $isUTF8=false) {
        if(!$isUTF8)
            $script=utf8_encode($script);
        $this->javascript=$script;
    }

    function _putjavascript() {
        $this->_newobj();
        $this->n_js=$this->n;
        $this->_put('<<');
        $this->_put('/Names [(EmbeddedJS) '.($this->n+1).' 0 R]');
        $this->_put('>>');
        $this->_put('endobj');
        $this->_newobj();
        $this->_put('<<');
        $this->_put('/S /JavaScript');
        $this->_put('/JS '.$this->_textstring($this->javascript));
        $this->_put('>>');
        $this->_put('endobj');
    }

    function _putresources() {
        parent::_putresources();
        if (!empty($this->javascript)) {
            $this->_putjavascript();
        }
    }

    function _putcatalog() {
        parent::_putcatalog();
        if (!empty($this->javascript)) {
            $this->_put('/Names <</JavaScript '.($this->n_js).' 0 R>>');
        }
    }


    






    function AutoPrint($printer='')
    {
        // Open the print dialog
        if($printer)
        {
            $printer = str_replace('\\', '\\\\', $printer);
            $script = "var pp = getPrintParams();";
            $script .= "pp.interactive = pp.constants.interactionLevel.full;";
            $script .= "pp.printerName = '$printer'";
            $script .= "print(pp);";
        }
        else
            $script = 'print(true);';
        $this->IncludeJS($script);
    }
 



 
// private functions
function RoundedRect($x, $y, $w, $h, $r, $style = '')
{
    $k = $this->k;
    $hp = $this->h;
    if($style=='F')
        $op='f';
    elseif($style=='FD' || $style=='DF')
        $op='B';
    else
        $op='S';
    $MyArc = 4/3 * (sqrt(2) - 1);
    $this->_out(sprintf('%.2F %.2F m',($x+$r)*$k,($hp-$y)*$k ));
    $xc = $x+$w-$r ;
    $yc = $y+$r;
    $this->_out(sprintf('%.2F %.2F l', $xc*$k,($hp-$y)*$k ));
 
    $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);
    $xc = $x+$w-$r ;
    $yc = $y+$h-$r;
    $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-$yc)*$k));
    $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);
    $xc = $x+$r ;
    $yc = $y+$h-$r;
    $this->_out(sprintf('%.2F %.2F l',$xc*$k,($hp-($y+$h))*$k));
    $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);
    $xc = $x+$r ;
    $yc = $y+$r;
    $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$yc)*$k ));
    $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
    $this->_out($op);
}
 
function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
{
    $h = $this->h;
    $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $x1*$this->k, ($h-$y1)*$this->k,
                        $x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
}
 
function Rotate($angle, $x=-1, $y=-1)
{
    if($x==-1)
        $x=$this->x;
    if($y==-1)
        $y=$this->y;
    if($this->angle!=0)
        $this->_out('Q');
    $this->angle=$angle;
    if($angle!=0)
    {
        $angle*=M_PI/180;
        $c=cos($angle);
        $s=sin($angle);
        $cx=$x*$this->k;
        $cy=($this->h-$y)*$this->k;
        $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
    }
}
 
function _endpage()
{
    if($this->angle!=0)
    {
        $this->angle=0;
        $this->_out('Q');
    }
    parent::_endpage();
}
 
// public functions
function sizeOfText( $texte, $largeur )
{
    $index    = 0;
    $nb_lines = 0;
    $loop     = TRUE;
    while ( $loop )
    {
        $pos = strpos($texte, "\n");
        if (!$pos)
        {
            $loop  = FALSE;
            $ligne = $texte;
        }
        else
        {
            $ligne  = substr( $texte, $index, $pos);
            $texte = substr( $texte, $pos+1 );
        }
        $length = floor( $this->GetStringWidth( $ligne ) );
        $res = 1 + floor( $length / $largeur) ;
        $nb_lines += $res;
    }
    return $nb_lines;
}


function addSocietenombre($nom, $tlibre)
{
    $x1 = 10;
    $y1 = 6;
    $this->SetXY( $x1, $y1 + 4 );
    $this->SetFont('Arial','B',16);
    $length = $this->GetStringWidth($nom);
    $this->Cell( $length, 2, $nom, 'C');
    $this->SetXY( $x1 + 25, $y1 + 8 );
    $this->SetFont('Arial','B',7);
    $length = $this->GetStringWidth($tlibre);
    $this->Cell( $length, 2, $tlibre, 'C');
}
 
// Company
function addSociete($telefono, $email, $direccion, $logo,$ext_logo )
{
    $x1 = 35;
    $y1 = 14;
    //Positionnement en bas
    $this->Image($logo , 10 , 15, 25 , 13 , $ext_logo); // (x,y, ancho, alto)
    $this->SetXY( $x1, $y1 );
    //$this->SetFont('Arial','B',12);
    
    $this->SetXY( $x1, $y1 + 4 );
    $this->SetFont('Arial','',6);
    $this->MultiCell(100, 2, $telefono);

    $this->SetXY( $x1, $y1 + 6 );
    $this->MultiCell(100, 2, $email);

    $this->SetXY( $x1, $y1 + 8 );
    $this->MultiCell(40, 2, $direccion,'','L');
}


function addSocietedireccion($adresse)
{
    $x1 = 26;
    $y1 = 15;
  
    $this->SetXY( $x1, $y1 + 4 );
    $this->SetFont('helvetica','B',6);
    
    $this->MultiCell(50, 2, $adresse);
}


function addSociete2( $nom, $adresse,$logo,$ext_logo )
{
    $x1 = 30;
    $y1 =155;
    //Positionnement en bas
    $this->Image($logo , 5 ,150, 25 , 25 , $ext_logo);
    $this->SetXY( $x1, $y1 );
    $this->SetFont('Arial','B',12);
    $length = $this->GetStringWidth( $nom );
    $this->Cell( $length, 2, $nom);
    $this->SetXY( $x1, $y1 + 4 );
    $this->SetFont('Arial','',10);
    $length = $this->GetStringWidth( $adresse );
    //Coordonnées de la société
    $lignes = $this->sizeOfText( $adresse, $length) ;
    $this->MultiCell($length, 4, $adresse);
}
 
// Label and number of invoice/estimate
function fact_dev($num )
{
    $r1  = $this->w - 63;
    $r2  = $r1 + 50;
    $y1  = 6;
    $y2  = $y1 + 20;
    $mid = ($r1 + $r2 ) / 2;
     
    //$texte  = $ruc.$num;    
    $texte  = $num;    
    $szfont = 12;
    $loop   = 0;
     
    while ( $loop == 0 )
    {
       $this->SetFont( "Arial", "B", $szfont );
       $sz = $this->GetStringWidth( $texte );
       if ( ($r1+$sz) > $r2 )
          $szfont --;
       else
          $loop ++;
    }
 
    $this->SetLineWidth(0.1);
    //$this->SetFillColor(72,209,20);
    $this->SetFillColor(255,255,255);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 2.5, 'DF');
    $this->SetXY( $r1+1, $y1+2);
    $this->Cell($r2-$r1 -1,5, $texte, 0, 0, "C" );
}

// Label and number of invoice/estimate
function fact_dev2( $libelle, $num )
{
    $r1  = $this->w - 63;
    $r2  = $r1 + 50;
    $y1  = 145;
    $y2  = $y1 -137;
    $mid = ($r1 + $r2 ) / 2;
     
    //$texte  = $libelle  . $num;    
    $texte  = $num;    
    $szfont = 12;
    $loop   = 0;
     
    while ( $loop == 0 )
    {
       $this->SetFont( "Arial", "B", $szfont );
       $sz = $this->GetStringWidth( $texte );
       if ( ($r1+$sz) > $r2 )
          $szfont --;
       else
          $loop ++;
    }
 
    $this->SetLineWidth(0.1);
    //$this->SetFillColor(72,209,20);
    $this->SetFillColor(255,255,255);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 2.5, 'DF');
    $this->SetXY( $r1+1, $y1+2);
    $this->Cell($r2-$r1 -1,5, $texte, 0, 0, "C" );
}
 
// Estimate
function addDevis( $numdev )
{
    $string = sprintf("DEV%04d",$numdev);
    $this->fact_dev( "Devis", $string );
}
 
// Invoice
function addFacture( $numfact )
{
    $string = sprintf("FA%04d",$numfact);
    $this->fact_dev( "Facture", $string );
}
 
function addDate( $date )
{
    $r1  = $this->w - 61;
    $r2  = $r1 + 49;
    $y1  = 17;
    $y2  = $y1 ;
    $mid = $y1 + ($y2 / 2);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 3.5, 'D');
    $this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+3 );
    $this->SetFont( "Arial", "B", 11);
    $this->Cell(10,5, "Fecha", 0, 0, "C");
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+9 );
    $this->SetFont( "Arial", "", 10);
    $this->Cell(10,5,$date, 0,0, "C");
}

function numGuia( $num, $ruc )
{
    $r1  = $this->w - 60;
    $r2  = $r1 + 45;
    $y1  = 8;
    $y2  = 17 ;
    $mid = $y1 + ($y2 / 2);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 1, 'D');
    //$this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+1 );
    $this->SetFont( "Arial", "B", 11);
    $this->Cell(10,5, utf8_decode("RUC N° ".$ruc), 0, 0, "C");

    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+6 );
    $this->SetFont( "Arial", "B", 11);
    $this->Cell(10,5, utf8_decode("GUIA DE REMISIÓN"), 0, 0, "C");

    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+11 );
    $this->SetFont( "Arial", "B", 11);
    $this->Cell(10,5,$num, 0,0, "C");
}


function fechaemision($fechaemi)
{
    $r1  = $this->w - 126; //moviemiento izuierd derecha 
    $r2  = $r1 + 30;
    $y1  = 15; //movimiento arriba abajp
    $y2  = 10 ;
    $mid = $y1 + ($y2 / 2);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 1, 'D');
    //$this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+1 );
    $this->SetFont( "Arial", "B", 8);
    $this->Cell(10,5, utf8_decode("Fecha de emisión"), 0, 0, "C");

    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+5 );
    $this->SetFont( "Arial", "B", 8);
    $this->Cell(10,5,$fechaemi, 0,0, "C");
}

function fechatraslado($fechatraslado)
{
    $r1  = $this->w - 92; //moviemiento izuierd derecha 
    $r2  = $r1 + 30;
    $y1  = 15; //movimiento arriba abajp
    $y2  = 10 ;
    $mid = $y1 + ($y2 / 2);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 1, 'D');
    //$this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+1 );
    $this->SetFont( "Arial", "B", 8);
    $this->Cell(10,5, utf8_decode("Fecha inicio traslado"), 0, 0, "C");

    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+5 );
    $this->SetFont( "Arial", "B", 8);
    $this->Cell(10,5,$fechatraslado, 0,0, "C");
}

function numGuia2( $num )
{
    $r1  = $this->w - 80;
    $r2  = $r1 + 70;
    $y1  = 152;
    $y2  = 17 ;
    $mid = $y1 + ($y2 / 2);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 3.5, 'D');
    //$this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+1 );
    $this->SetFont( "Arial", "B", 11);
    $this->Cell(10,5, utf8_decode("RUC N° 20100088917"), 0, 0, "C");

    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+6 );
    $this->SetFont( "Arial", "B", 11);
    $this->Cell(10,5, utf8_decode("GUIA DE REMISIÓN"), 0, 0, "C");

    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+11 );
    $this->SetFont( "Arial", "B", 11);
    $this->Cell(10,5,$num, 0,0, "C");
}


function datosEmpresa2( $num )
{
    $r1  = $this->w - 80;
    $r2  = $r1 + 70;
    $y1  = 6;
    $y2  = 20 ;
    $mid = $y1 + ($y2 / 2);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 3.5, 'D');
    //$this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+1 );
    $this->SetFont( "Arial", "B", 11);
    $this->Cell(10,5, "RUC 20100088917", 0, 0, "C");

    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+6 );
    $this->SetFont( "Arial", "B", 11);
    $this->Cell(10,5, "FACTURA ELECTRONICA", 0, 0, "C");

    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+11 );
    $this->SetFont( "Arial", "B", 11);
    $this->Cell(10,5,$num, 0,0, "C");
}

function addDate2( $date )
{
    $r1  = $this->w - 61;
    $r2  = $r1 + 49;
    $y1  = 156;
    $y2  = 18 ;
    $mid = $y1 + ($y2 / 2);

    $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 3.5, 'D');
    $this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+3 );
    $this->SetFont( "Arial", "B", 11);
    $this->Cell(10,5, "Fecha", 0, 0, "C");
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+9 );
    $this->SetFont( "Arial", "", 10);
    $this->Cell(10,5,$date, 0,0, "C");
}


 
 
 
 
function addClient( $ref )
{
    $r1  = $this->w - 31;
    $r2  = $r1 + 19;
    $y1  = 17;
    $y2  = $y1;
    $mid = $y1 + ($y2 / 2);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 3.5, 'D');
    $this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+3 );
    $this->SetFont( "Arial", "B", 10);
    $this->Cell(10,5, "CLIENT", 0, 0, "C");
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1 + 9 );
    $this->SetFont( "Arial", "", 10);
    $this->Cell(10,5,$ref, 0,0, "C");
}
 
function addPageNumber( $page )
{
    $r1  = $this->w - 80;
    $r2  = $r1 + 19;
    $y1  = 17;
    $y2  = $y1;
    $mid = $y1 + ($y2 / 2);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 3.5, 'D');
    $this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+3 );
    $this->SetFont( "Arial", "B", 10);
    $this->Cell(10,5, "PAGE", 0, 0, "C");
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1 + 9 );
    $this->SetFont( "Arial", "", 10);
    $this->Cell(10,5,$page, 0,0, "C");
}
 
// Client address
function addClientAdresse(
    $pllegada,
    $destinatario,
    $nruc,
    $ppartida,
    $fecha,
    $numcomprobante, 
    $motivo,
            $fechatraslado,
            $rsocialtransportista,
            $ructran,
            $placa,
            $marca,
            $cinc,
            $container,
            $nlicencia,
            $ncoductor,
            $npedido,
            $vendedor,
            $costmt,
            $fechacomprobante,
            $ocompra,
            $comprobante,
            $observaciones,
            $abre,
            $pesobruto
    )
{
    $r1     = $this->w - 200;
    $r2     = $r1 + 100;
    $y1     = 23;


    $this->SetXY( $r1, $y1+5);
    $this->SetFont( "helvetica", "",8);
    $this->MultiCell( 150, 4, utf8_decode("Motivo de traslado: ").$motivo);



    $this->SetXY( $r1, $y1+10);
    $this->SetFont( "helvetica", "",7);
    $this->MultiCell( 80, 4, utf8_decode("Apellidos y Nombres o Razón solcial de destinatario:")."\n".$destinatario);

    $this->SetXY( $r2, $y1+10);
    $this->MultiCell( 80, 4, utf8_decode("R.U.C. / DNI:")."\n".$nruc);



    $this->SetXY( $r1, $y1+20);
    $this->MultiCell( 80, 3,utf8_decode("Dirección del punto de partida:")."\n".$ppartida);

    $this->SetXY( $r2, $y1+18);
    $this->MultiCell( 80, 4, utf8_decode("Dirección punto de llegada:")."\n".$pllegada);



    $this->SetXY( $r1, $y1+30);
    $this->MultiCell( 80, 5, utf8_decode("Apellidos y Nombres o razón social transportista:")."\n".$rsocialtransportista);

    $this->SetXY( $r2, $y1+30);
    $this->MultiCell( 80, 5, utf8_decode("R.U.C. transportista:")."\n".$ructran);



    $this->SetFont( "helvetica", "B",8);
    $this->SetXY( $r1, $y1+40);
    $this->Cell( 100, 4, utf8_decode("IDENTIFICACION DE LA UNIDAD DE TRANSPORTE"),1,1,'C');

    $this->SetFont( "helvetica", "",7);
    $this->SetXY( $r1, $y1+44);
    $this->multiCell( 20, 5, utf8_decode("Marca:")."\n".$marca,1,1);

    $this->SetXY( 30, $y1+44);
    $this->multiCell(20, 5, utf8_decode("Placa")."\n".$placa,1,1);

    $this->SetXY( 50, $y1+44);
    $this->multiCell(40, 5, utf8_decode("Contancia de inscripción")."\n".$cinc,1,1);

    $this->SetXY( 90, $y1+44);
    $this->multiCell(20, 5, utf8_decode("Contaniner")."\n".$container,1,1);




    $this->SetFont( "helvetica", "B",8);
    $this->SetXY( 114, $y1+40);
    $this->Cell( 85, 4, utf8_decode("IDENTIFICACION DEL CONDUCTOR"),1,1,'C');

    $this->SetFont( "helvetica", "",7);
    $this->SetXY( 114, $y1+44);
    $this->multiCell( 30, 5, utf8_decode("Nro de licencia:")."\n".$nlicencia,1,1);

    $this->SetXY( 144, $y1+44);
    $this->multiCell(55, 5, utf8_decode("Nombre")."\n".$ncoductor,1,1);

    $this->SetXY( $r1, $y1+55);
    $this->multiCell(30.83, 5, utf8_decode("Orden de compra")."\n".$ocompra,1,1);

    $this->SetXY( 42, $y1+55);
    $this->multiCell(30.83, 5, utf8_decode("N° de pedido")."\n".$npedido,1,1);

    $this->SetXY( 74, $y1+55);
    $this->multiCell(30.83, 5, utf8_decode("Código de vendedor")."\n".$vendedor,1,1);

    $this->SetXY( 106, $y1+55);
    $this->multiCell(30.83, 5, utf8_decode("Costo mínimo traslado")."\n".$costmt,1,1);

    $this->SetXY( 138, $y1+55);
    $this->multiCell(30.83, 5, utf8_decode("N° comp. de pago")."\n".$numcomprobante,1,1);

    $this->SetXY( 170, $y1+55);
    $this->multiCell(30.83, 5, utf8_decode("Fecha de comp. de pago")."\n".$fechacomprobante,1,1);



    $this->SetFont( "helvetica", "B",8);
    $this->SetXY( $r1, 247);
    $this->Cell( 25, 6, "",1,1,'C');

    $this->SetFont( "helvetica", "B",8);
    $this->SetXY( 35, 247);
    $this->Cell( 50, 6, "TOTAL PESO BRUTO: ".$pesobruto." ".$abre,1,1,'C');


    $this->SetFont( "helvetica", "",6);
    $this->SetXY( $r1, 253);
    $this->Cell( 190, 8, "Observaciones: ".$observaciones,1,1,'L');
    //$this->multiCell(190, 8, "Observaciones"."\n".$observaciones,1,1);

    $this->SetXY( $r1, 261);
    $this->Cell( 31.66, 10, utf8_decode("Preparó:"),1,1,'L');

    $this->SetXY( 42, 261);
    $this->Cell( 31.66, 10, utf8_decode("Almacén:"),1,1,'L');

    $this->SetXY( 74, 261);
    $this->Cell( 31.66, 10, utf8_decode("Balanza:"),1,1,'L');

    $this->SetXY( 106, 261);
    $this->Cell( 31.66, 10, utf8_decode("V° B°:"),1,1,'L');

    $this->SetXY( 138, 261);
    $this->Cell( 31.66, 10, utf8_decode("Transportista:"),1,1,'L');

    $this->SetXY( 170, 261);
    $this->Cell( 30, 10, utf8_decode("Recibi conforme:")."\n"."Fecha:",1,1);

    $this->SetXY($r1, 271);
    $this->Cell(31.66, 5, utf8_decode("DESPÚES DE FIRMADA LA PRESENTE GUIA NO HAY LUGAR A RECLAMO"));

    

}
 

 // Client address
function addClientAdresse2($fecha,$cliente,$domicilio,$num_documento,$estado,$usuario)
{
    $r1     = $this->w - 180;
    $r2     = $r1 + 68;
    $y1     = 172;


    $this->SetXY( $r1, $y1+2);
    $this->SetFont( "Arial", "", 9);
    $this->MultiCell( 60, 4, utf8_decode("Fecha Emisión: ").$fecha);

    $this->SetXY( $r1, $y1+6);
    $this->SetFont( "Arial", "", 9);
    $this->MultiCell( 150, 3,utf8_decode("Señor(es):  ").$cliente);

    $this->SetXY( $r1, $y1+10);
    $this->MultiCell( 150, 3, utf8_decode("Dirección:  ").$domicilio);

    $this->SetXY( $r1, $y1+14);
    $this->MultiCell( 150, 3, utf8_decode("RUC/DNI:  ").$num_documento);

    $this->SetXY( $r1, $y1+22);
    $this->MultiCell( 150, 3, utf8_decode("Atención:  ").$usuario);
    
    $st="";
    if($estado==0){
        $st="Anulado";
        }else{
        $st="Cancelado";
    }

    $this->SetXY( $r1, $y1+18);
    $this->MultiCell( 150, 3, utf8_decode("Estado :  ").$st);

    //$this->SetXY( $r1, $y1+22);
    //$this->MultiCell( 150, 3, $esatdo);
}


// Mode of payment
function addReglement( $mode )
{
    $r1  = 10;
    $r2  = $r1 + 60;
    $y1  = 80;
    $y2  = $y1+10;
    $mid = $y1 + (($y2-$y1) / 2);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D');
    $this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + ($r2-$r1)/2 -5 , $y1+1 );
    $this->SetFont( "Arial", "B", 10);
    $this->Cell(10,4, "CLIENTE", 0, 0, "C");
    $this->SetXY( $r1 + ($r2-$r1)/2 -5 , $y1 + 5 );
    $this->SetFont( "Arial", "", 10);
    $this->Cell(10,5,$mode, 0,0, "C");
}
 
// Expiry date
function addEcheance( $documento,$numero )
{
    $r1  = 80;
    $r2  = $r1 + 40;
    $y1  = 80;
    $y2  = $y1+10;
    $mid = $y1 + (($y2-$y1) / 2);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D');
    $this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + ($r2 - $r1)/2 - 5 , $y1+1 );
    $this->SetFont( "Arial", "B", 10);
    $this->Cell(10,4, $numero, 0, 0, "C");
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5 , $y1 + 5 );
    $this->SetFont( "Arial", "", 10);
    $this->Cell(10,5,$numero, 0,0, "C");
}
 
// VAT number
function addNumTVA($tva)
{
    $this->SetFont( "Arial", "B", 10);
    $r1  = $this->w - 80;
    $r2  = $r1 + 70;
    $y1  = 80;
    $y2  = $y1+10;
    $mid = $y1 + (($y2-$y1) / 2);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D');
    $this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + 16 , $y1+1 );
    $this->Cell(40, 4, "DIRECCIÓN", '', '', "C");
    $this->SetFont( "Arial", "", 10);
    $this->SetXY( $r1 + 16 , $y1+5 );
    $this->Cell(40, 5, $tva, '', '', "C");
}
 
function addReference($ref)
{
    $this->SetFont( "Arial", "", 10);
    $length = $this->GetStringWidth( "Références : " . $ref );
    $r1  = 10;
    $r2  = $r1 + $length;
    $y1  = 92;
    $y2  = $y1+5;
    $this->SetXY( $r1 , $y1 );
    $this->Cell($length,4, "Références : " . $ref);
}
 
function addCols( $tab )
{
    global $colonnes;
     
    $r1  = 10;
    $r2  = $this->w - ($r1 * 2) ;
    $y1  = 90;
    $y2  = $this->h - 50 - $y1;

    $this->SetXY( $r1, $y1 );
    $this->Rect( $r1, $y1, $r2, $y2, "L");
    $this->Line( $r1, $y1+6, $r1+$r2, $y1+6);
    $colX = $r1;
    $colonnes = $tab;
    while ( list( $lib, $pos ) = each ($tab) )
    {
        $this->SetXY( $colX, $y1+2 );
        $this->Cell( $pos, 1, $lib, 0, 0, "L");
        $colX += $pos;
        $this->Line( $colX, $y1, $colX, $y1+$y2);
    }
}

function addCols2( $tab )
{
    global $colonnes;
     
    $r1  = 10;
    $r2  = $this->w - ($r1 * 2) ;
    $y1  = 200;
    $y2  = $this->h - 40 - $y1;
    $this->SetXY( $r1, $y1 );
    $this->Rect( $r1, $y1, $r2, $y2, "D");
    $this->Line( $r1, $y1+6, $r1+$r2, $y1+6);
    $colX = $r1;
    $colonnes = $tab;
    while ( list( $lib, $pos ) = each ($tab) )
    {
        $this->SetXY( $colX, $y1+2 );
        $this->Cell( $pos, 1, $lib, 0, 0, "C");
        $colX += $pos;
        $this->Line( $colX, $y1, $colX, $y1+$y2);
    }
} 
 
function addLineFormat( $tab )
{
    global $format, $colonnes;
     
    while ( list( $lib, $pos ) = each ($colonnes) )
    {
        if ( isset( $tab["$lib"] ) )
            $format[ $lib ] = $tab["$lib"];
    }
}

function addLineFormat2( $tab )
{
    global $format, $colonnes;
     
    while ( list( $lib, $pos ) = each ($colonnes) )
    {
        if ( isset( $tab["$lib"] ) )
            $format[ $lib ] = $tab["$lib"];
    }
}
 
function lineVert( $tab )
{
    global $colonnes;
 
    reset( $colonnes );
    $maxSize=0;
    while ( list( $lib, $pos ) = each ($colonnes) )
    {
        $texte = $tab[ $lib ];
        $longCell  = $pos -2;
        $size = $this->sizeOfText( $texte, $longCell );
        if ($size > $maxSize)
            $maxSize = $size;
    }
    return $maxSize;
}
 
// add a line to the invoice/estimate
/*    $ligne = array( "REFERENCE"    => $prod["ref"],
                      "DESIGNATION"  => $libelle,
                      "QUANTITE"     => sprintf( "%.2F", $prod["qte"]) ,
                      "P.U. HT"      => sprintf( "%.2F", $prod["px_unit"]),
                      "MONTANT H.T." => sprintf ( "%.2F", $prod["qte"] * $prod["px_unit"]) ,
                      "TVA"          => $prod["tva"] );
*/
function addLine( $ligne, $tab )
{
    global $colonnes, $format;
 
    $ordonnee     = 10;
    $maxSize      = $ligne;
 
    reset( $colonnes );
    while ( list( $lib, $pos ) = each ($colonnes) )
    {
        $longCell  = $pos - 2;
        $texte     = $tab[ $lib ];
        $length    = $this->GetStringWidth( $texte );
        $tailleTexte = $this->sizeOfText( $texte, $length );
        $formText  = $format[ $lib ];
        $this->SetXY( $ordonnee, $ligne-1);
        $this->MultiCell( $longCell, 3 , $texte, 0, $formText);
        if ( $maxSize < ($this->GetY()  ) )
            $maxSize = $this->GetY() ;
        $ordonnee += $pos;
    }
    return ( $maxSize - $ligne );
}





function addLine2( $ligne, $tab )
{
    global $colonnes, $format;
 
    $ordonnee     = 10;
    $maxSize      = $ligne;
 
    reset( $colonnes );
    while ( list( $lib, $pos ) = each ($colonnes) )
    {
        $longCell  = $pos -2;
        $texte     = $tab[ $lib ];
        $length    = $this->GetStringWidth( $texte );
        $tailleTexte = $this->sizeOfText( $texte, $length );
        $formText  = $format[ $lib ];
        $this->SetXY( $ordonnee, $ligne-1);
        $this->MultiCell( $longCell, 4 , $texte, 0, $formText);
        if ( $maxSize < ($this->GetY()  ) )
            $maxSize = $this->GetY() ;
        $ordonnee += $pos;
    }
    return ( $maxSize - $ligne );
}

 
function addRemarque($remarque)
{
    $this->SetFont( "Arial", "", 10);
    $length = $this->GetStringWidth( "Remarque : " . $remarque );
    $r1  = 10;
    $r2  = $r1 + $length;
    $y1  = $this->h - 45.5;
    $y2  = $y1+5;
    $this->SetXY( $r1 , $y1 );
    $this->Cell($length,4, "Remarque : " . $remarque);
}
 
function addCadreTVAs($monto)
{
    $this->SetFont( "Arial", "B", 8);
    $r1  = 10;
    $r2  = $r1 + 120;
    $y1  = $this->h - 179;
    $y2  = $y1+5;
    $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D');

    $this->SetXY( $r1+9, $y1+1);
    $this->Cell(10,3, "IMPORTE TOTAL: ");
    
    $this->SetFont( "Arial", "", 8);
    $this->SetXY( $r1+35, $y1+1);
    $this->MultiCell(100,3, $monto);

    

}

function observSunat($nro, $std)
{
    $this->SetFont( "Arial", "B", 7);
    $r1  = 10;
    $r2  = $r1 + 120;
    $y1  = $this->h - 173;
    $y2  = $y1+16;
    $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D');

    $this->SetXY( $r1+9, $y1+1);
    $this->Cell(10,3, utf8_decode("Observación SUNAT: "));
    
    $this->SetFont( "Arial", "", 8);
    $this->SetXY( $r1+9, $y1+3);
    $this->MultiCell(100,4, utf8_decode("La Factura Electrónica número ").$nro." a sido".$std);

    $this->SetFont( "Arial", "", 6);
    $this->SetXY( $r1+9, $y1+7);
    $this->MultiCell(100,4, utf8_decode("Autorizado a ser emisor electrónico mediante R.I. SUNAT N° ##########. Representación impresa." ));

    
    $this->SetFont( "Arial", "", 6);
    $this->SetXY( $r1+9, $y1+10);
    $this->MultiCell(100,4, utf8_decode("##################################################" ));


}

function observSunat2($nro, $std)
{
    $this->SetFont( "Arial", "B", 7);
    $r1  = 10;
    $r2  = $r1 + 120;
    $y1  = $this->h - 33;
    $y2  = $y1+16;
    $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D');

    $this->SetXY( $r1+9, $y1+1);
    $this->Cell(10,3, utf8_decode("Observación SUNAT: "));
    
    $this->SetFont( "Arial", "", 8);
    $this->SetXY( $r1+9, $y1+3);
    $this->MultiCell(100,4, utf8_decode("La Factura Electrónica número ").$nro." a sido".$std);

    $this->SetFont( "Arial", "", 6);
    $this->SetXY( $r1+9, $y1+6);
    $this->MultiCell(100,4, utf8_decode("Autorizado a ser emisor electrónico mediante R.I. SUNAT N° ##########. Representación impresa." ));
    
	$this->SetFont( "Arial", "", 6);
    $this->SetXY( $r1+9, $y1+8);
    $this->MultiCell(100,4, utf8_decode("##################################################" ));

}

function addCadreTVAs2($monto)
{
    $this->SetFont( "Arial", "B", 8);
    $r1  = 10;
    $r2  = $r1 + 120;
    $y1  = $this->h - 39;
    $y2  = $y1+5;
    $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D');

    $this->SetXY( $r1+9, $y1+1);
    $this->Cell(10,3, "IMPORTE TOTAL: ");
    
    $this->SetFont( "Arial", "", 8);
    $this->SetXY( $r1+35, $y1+1);
    $this->MultiCell(100,3, $monto);
 
}
 
function addCadreEurosFrancs($impuesto)
{
    $r1  = $this->w - 70;
    $r2  = $r1 + 60;
    $y1  = $this->h - 179;
    $y2  = $y1+22;
    $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D');
    $this->Line( $r1+20,  $y1, $r1+20, $y2); // avant EUROS
    //$this->Line( $r1+20, $y1+4, $r2, $y1+4); // Sous Euros & Francs
    //$this->Line( $r1+38,  $y1, $r1+38, $y2); // Entre Euros & Francs
    $this->SetFont( "Arial", "B", 8);
    $this->SetXY( $r1+22, $y1 );
    $this->Cell(30,4, "TOTALES", 0, 0, "C");
    $this->SetFont( "Arial", "", 8);
    
    $this->SetFont( "Arial", "B", 6);
    $this->SetXY( $r1, $y1+5 );
    $this->Cell(20,4, "Op. Gravada", 0, 0, "C");
    $this->SetXY( $r1, $y1+10 );
    $this->Cell(20,4, "I.G.V.", 0, 0, "C");
    $this->SetXY( $r1, $y1+15 );
    $this->Cell(20,4, "IMPORTE TOTAL", 0, 0, "C");
}

function addCadreEurosFrancs2($impuesto)
{
    $r1  = $this->w - 70;
    $r2  = $r1 + 60;
    $y1  = $this->h - 39;
    $y2  = $y1+22;
    $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D');
    $this->Line( $r1+20,  $y1, $r1+20, $y2); // avant EUROS

    $this->SetFont( "Arial", "B", 8);
    $this->SetXY( $r1+22, $y1 );
    $this->Cell(30,4, "TOTALES", 0, 0, "C");

    $this->SetFont( "Arial", "", 8);
    $this->SetFont( "Arial", "B", 6);

    $this->SetXY( $r1, $y1+4 );
    $this->Cell(20,4, "Op. Gravada", 0, 0, "C");

    $this->SetXY( $r1, $y1+9 );
    $this->Cell(20,4, "I.G.V.", 0, 0, "C");

    $this->SetXY( $r1, $y1+14 );
    $this->Cell(20,4, "IMPORTE TOTAL", 0, 0, "C");
}
 
// remplit les cadres TVA / Totaux et la remarque
// params  = array( "RemiseGlobale" => [0|1],
//                      "remise_tva"     => [1|2...],  // {la remise s'applique sur ce code TVA}
//                      "remise"         => value,     // {montant de la remise}
//                      "remise_percent" => percent,   // {pourcentage de remise sur ce montant de TVA}
//                  "FraisPort"     => [0|1],
//                      "portTTC"        => value,     // montant des frais de ports TTC
//                                                     // par defaut la TVA = 19.6 %
//                      "portHT"         => value,     // montant des frais de ports HT
//                      "portTVA"        => tva_value, // valeur de la TVA a appliquer sur le montant HT
//                  "AccompteExige" => [0|1],
//                      "accompte"         => value    // montant de l'acompte (TTC)
//                      "accompte_percent" => percent  // pourcentage d'acompte (TTC)
//                  "Remarque" => "texte"              // texte
// tab_tva = array( "1"       => 19.6,
//                  "2"       => 5.5, ... );
// invoice = array( "px_unit" => value,
//                  "qte"     => qte,
//                  "tva"     => code_tva );
function addTVAs( $igv, $total, $moneda )
{
    $this->SetFont('Arial','',8);
 
    $re  = $this->w - 30;
    $rf  = $this->w - 29;
    $y1  = $this->h - 178;
    $this->SetFont( "Arial", "", 8);
    $this->SetXY( $re, $y1+5 );
    //$this->Cell( 17,4, $moneda.sprintf("%0.2F", $total-($total*$igv/($igv+100))), '', '', 'R');
    $this->Cell( 17,4, $moneda.sprintf("%0.2F", $total - $igv), '', '', 'R');

    //$this->Cell( 17,4, $moneda.sprintf("%0.2F", $total-($total*$igv/($igv+100))), '', '', 'R');

    $this->SetXY( $re, $y1+10 );
    $this->Cell( 17,4, $moneda.sprintf("%0.2F", $igv), '', '', 'R');

    // $this->Cell( 17,4, $moneda.sprintf("%0.2F", ($total*$igv/($igv+100))), '', '', 'R');

    $this->SetXY( $re, $y1+14.8 );
    $this->Cell( 17,4, $moneda.sprintf("%0.2F", $total), '', '', 'R');
     
}

function addTVAs2( $igv, $total, $moneda )
{
    $this->SetFont('Arial','',8);
 
    $re  = $this->w - 30;
    $rf  = $this->w - 29;
    $y1  = $this->h - 39;
    $this->SetFont( "Arial", "", 8);
    $this->SetXY( $re, $y1+5 );
    $this->Cell( 17,4, $moneda.sprintf("%0.2F", $total - $igv), '', '', 'R');

    //$this->Cell( 17,4, $moneda.sprintf("%0.2F", $total-($total*$igv/($igv+100))), '', '', 'R');

    $this->SetXY( $re, $y1+10 );
    $this->Cell( 17,4, $moneda.sprintf("%0.2F", $igv), '', '', 'R');

    // $this->Cell( 17,4, $moneda.sprintf("%0.2F", ($total*$igv/($igv+100))), '', '', 'R');

    $this->SetXY( $re, $y1+14.8 );
    $this->Cell( 17,4, $moneda.sprintf("%0.2F", $total), '', '', 'R');
     
}
 
// add a watermark (temporary estimate, DUPLICATA...)
// call this method first
function temporaire( $texte )
{
    $this->SetFont('Arial','B',50);
    $this->SetTextColor(203,203,203);
    $this->Rotate(45,55,190);
    $this->Text(55,190,$texte);
    $this->Rotate(0);
    $this->SetTextColor(0,0,0);
}
 
}
?>