<?php
require "../config/Conexion.php";
Class Doccobranza
{

    //Implementamos nuestro constructor
    public function __construct()
    {
 

    }


public function insertarTc($fechatc, $compra, $venta)
    {
        $sql="insert into tcambio (fecha, compra, venta)
        values ('$fechatc', '$compra', '$venta')";
        return ejecutarConsulta($sql);
    }

  //Implementamos un método para editar registros
  public function editarTc($id, $fechatc, $compra, $venta)
  {
        $sql="update tcambio  set fecha='$fechatc', compra='$compra', venta='$venta' where idtipocambio='$id' ";
        return ejecutarConsulta($sql);
  }

 
 
    //Implementamos un método para insertar registros para factura
public function insertar($idempresa, $idusuario, $idcliente, $condicion, $fecha_emision, $serienota, $tarifas, $subtotal_doccobranza_servicio , $total_igv_servicio, $otros, $deduccion, $total_final_servicio, $observacion, $tcambio, $tipo_moneda, $tipodoccobranza, $idserviciobien, $codigo, $cantidad, $precio_unitario, $descdet, $norden, $numero_doccobranza, $idserie, $email, $domicilio_fiscal2, $RazonSocial, $igvvalorventa, $igvitem, $vuniitem, $valorventa, $hora)
    {

      $sw=true;
        
        $sql="insert into 
        doccobranza
         (  
            idempresa,
            idusuario, 
            idcliente, 
            condicion, 
            fechaemision, 
            serienumero, 
            tarifa,
            neta,
            igv,
            otros,
            deduccion,
            total,
            observacion,
            tcambio,
            tipo_moneda,
            tipodoccobranza
          )
          values
          (
  '$idempresa', '$idusuario', '$idcliente', '$condicion', '$fecha_emision $hora', '$serienota', '$tarifas', '$subtotal_doccobranza_servicio' , '$total_igv_servicio', '$otros', '$deduccion', '$total_final_servicio', '$observacion', '$tcambio', '$tipo_moneda', '$tipodoccobranza')";
        //return ejecutarConsulta($sql);
        $iddoccobranzanew=ejecutarConsulta_retornarID($sql);
 
        $num_elementos=0;
      while ($num_elementos < count($idserviciobien))
        {
            //Guardar en Detalle
        $sql_detalle = "insert into 
        detalle_doccobranza
        (

        iddoccobranza, 
        iditem, 
        codigo, 
        cantidad, 
        precio,
        descdet,
        norden, 
        igvvalorventa,
        igvitem,
        vuniitem,
        valorventa
          ) 
          values 
          (
          '$iddoccobranzanew', 
          '$idserviciobien[$num_elementos]',
          '$codigo[$num_elementos]',
          '$cantidad[$num_elementos]',
          '$precio_unitario[$num_elementos]',
          '$descdet[$num_elementos]',
          '$norden[$num_elementos]',
          '$igvvalorventa[$num_elementos]',
          '$igvitem[$num_elementos]',
          '$vuniitem[$num_elementos]',
          '$valorventa[$num_elementos]'
        )";

         //Para actualizar numeracion de las series de la factura
         $sql_update_numeracion="update 
         numeracion 
         set 
         numero='$numero_doccobranza' where idnumeracion='$idserie'";
        ejecutarConsulta($sql_update_numeracion) or $sw = false;
         //Fin 

        $sqlupdatecorreocliente="update persona set email='$email', domicilio_fiscal='$domicilio_fiscal2', razon_social='$RazonSocial', nombre_comercial='$RazonSocial'   where idpersona='$idcliente'";

            //return ejecutarConsulta($sql);
        ejecutarConsulta($sql_detalle) or $sw = false;
           ejecutarConsulta($sqlupdatecorreocliente) or $sw = false;

      $num_elementos=$num_elementos + 1;
          }   

return $sw; //FIN DE LA FUNCION

}
 



public function mostrarultimocomprobante($idempresa)
  {
    $sql="select numeracion_08 from factura f inner join empresa e on f.idempresa=e.idempresa  where e.idempresa='$idempresa'  order by idfactura desc limit 1";
    return ejecutarConsultaSimpleFila($sql);    
  }


public function crearPDF($idfactura, $idempresa)
{
require('Factura.php');
//Obtenemos los datos de la cabecera de la venta actuall
require_once "../modelos/Factura.php";
$factura = new Factura();
$rsptav = $factura->ventacabecera($idfactura, $idempresa);
$datos = $factura->datosemp($idempresa);
//Recorremos todos los valores obtenidos
$regv = $rsptav->fetch_object();
$datose = $datos->fetch_object();
$logo = "../files/logo/".$datose->logo;
$ext_logo = substr($datose->logo, strpos($datose->logo,'.'),-4);
//Establecemos la configuración de la factura
$pdf = new PDF_Invoice( 'P', 'mm',  'A4' );
$pdf->AddPage();
#Establecemos los márgenes izquierda, arriba y derecha: 
$pdf->SetMargins(10, 10 , 10); 
#Establecemos el margen inferior: 
$pdf->SetAutoPageBreak(true,10); 
//Enviamos los datos de la empresa al método addSociete de la clase Factura
$pdf->addSociete(utf8_decode(htmlspecialchars_decode($datose->nombre_comercial)),utf8_decode("Dirección    : ").utf8_decode($datose->domicilio_fiscal)."\n".utf8_decode("Teléfono     : ").$datose->telefono1." - ".$datose->telefono2."\n" ."Email          : ".$datose->correo, $logo, $ext_logo);
$pdf->numFactura("$regv->numeracion_08" , "$datose->numero_ruc");
$pdf->RotatedText($regv->estado, 35,190,'ANULADO - DADO DE BAJA',45);
$pdf->temporaire( "" );
//Enviamos los datos del cliente al método addClientAdresse de la clase Factura
$pdf->addClientAdresse( $regv->fecha."   /  Hora: ".$regv->hora,    utf8_decode(htmlspecialchars_decode($regv->cliente)), $regv->numero_documento, utf8_decode($regv->direccion), $regv->estado, utf8_decode($regv->vendedorsitio), utf8_decode($regv->guia) );
if ($regv->nombretrib=="IGV") {
        $nombret="V.U.";
    }else{
        $nombret="PRECIO";
    }
//Establecemos las columnas que va a tener la sección donde mostramos los detalles de la venta
$cols=array( "CODIGO"=>23,
             "DESCRIPCION"=>78,
             "CANTIDAD"=>22,
             $nombret=>25,
             "DSCTO"=>20,
             "SUBTOTAL"=>22);
$pdf->addCols( $cols);
$cols=array( "CODIGO"=>"L",
             "DESCRIPCION"=>"L",
             "CANTIDAD"=>"C",
             $nombret=>"R",
             "DSCTO" =>"R",
             "SUBTOTAL"=>"C");
$pdf->addLineFormat( $cols);
$pdf->addLineFormat($cols); 
//Actualizamos el valor de la coordenada "y", que será la ubicación desde donde empezaremos a mostrar los datos
$y= 62;
//Obtenemos todos los detalles de la venta actual
$rsptad = $factura->ventadetalle($idfactura);
 
while ($regd = $rsptad->fetch_object()) {
    if ($regd->nombretribu=="IGV") {
        $pv=$regd->valor_uni_item_14;
        $subt=$regd->subtotal;
    }else{
        $pv=$regd->precio;
        $subt=$regd->subtotal2;
    }
  $line = array( "CODIGO"=> "$regd->codigo",
                "DESCRIPCION"=> utf8_decode("$regd->articulo"),
                "CANTIDAD"=> "$regd->cantidad_item_12"." "."$regd->unidad_medida",
                $nombret=> $pv,
                "DSCTO" => "$regd->descuento",
                "SUBTOTAL"=> $subt);
            $size = $pdf->addLine( $y, $line );
            $y   += $size + 2;
}
//======= PARA EXTRAER EL HASH DEL DOCUMENTO FIRMADO ========================================
    require_once "../modelos/Rutas.php";
    $rutas = new Rutas();
    $Rrutas = $rutas->mostrar2($_SESSION['idempresa']);
    $Prutas = $Rrutas->fetch_object();
    $rutafirma=$Prutas->rutafirma; // ruta de la carpeta FIRMA
    $data[0] = "";

//===========PARA EXTRAER EL CODIGO HASH =============================
if ($regv->estado=='5') {
$facturaFirm=$regv->numero_ruc."-".$regv->tipo_documento_07."-".$regv->numeracion_08;
$sxe = new SimpleXMLElement($rutafirma.$facturaFirm.'.xml', null, true);
$urn = $sxe->getNamespaces(true);
$sxe->registerXPathNamespace('ds', $urn['ds']);
$data = $sxe->xpath('//ds:DigestValue');
}
else
{
     $data[0] = "";
}
//==================== PARA IMAGEN DEL CODIGO HASH ================================================
//set it to writable location, a place for temp generated PNG files
    $PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'/generador-qr/temp'.DIRECTORY_SEPARATOR;
    //html PNG location prefix
    $PNG_WEB_DIR = 'temp/';
    include 'generador-qr/phpqrcode.php';    
    
    //ofcourse we need rights to create temp dir
    if (!file_exists($PNG_TEMP_DIR)){
        mkdir($PNG_TEMP_DIR);
    }
    $filename = $PNG_TEMP_DIR.'test.png';
    //processing form input
    //remember to sanitize user input in real-life solution !!!
$dataTxt=$regv->numero_ruc."|".$regv->tipo_documento_07."|".$regv->serie."|".$regv->numerofac."|".$regv->sumatoria_igv_22_1."|".$regv->importe_total_venta_27."|".$regv->fecha2."|".$regv->tipo_documento."|".$regv->numero_documento."|";
$errorCorrectionLevel = 'H';    
$matrixPointSize = '2';
    // user data
        $filename = $PNG_TEMP_DIR.'test'.md5($dataTxt.'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
        QRcode::png($dataTxt, $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
        //default data
        //QRcode::png('PHP QR Code :)', $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
       //display generated file
        $PNG_WEB_DIR.basename($filename);
// //==================== PARA IMAGEN  ================================================
$logoQr = $filename;
//$logoQr = "../files/logo/".$datose->logo;
$ext_logoQr = substr($filename, strpos($filename,'.'),-4);
$pdf->ImgQr($logoQr, $ext_logoQr);
//======= PARA EXTRAER EL HASH DEL DOCUMENTO FIRMADO ========================================
//Convertimos el total en letras
require_once "Letras.php";
$V=new EnLetras(); 
$con_letra=strtoupper($V->ValorEnLetras($regv->importe_total_venta_27,"CON"));
$pdf->addCadreTVAs("".$con_letra);
$pdf->observSunat($regv->numeracion_08,$regv->estado, $data[0], $datose->webconsul,  $datose->nresolucion);
//Mostramos el impuesto
$pdf->addTVAs( $regv->sumatoria_igv_22_1 , $regv->importe_total_venta_27,"S/ ", $regv->tdescuento);
$pdf->addCadreEurosFrancs($regv->sumatoria_igv_22_1, $regv->nombretrib);
//===============SEGUNDA COPIA DE FACTURA=========================
//Enviamos los datos de la empresa al método addSociete de la clase Factura
$pdf->addSociete2(utf8_decode(htmlspecialchars_decode($datose->nombre_comercial)),utf8_decode("Dirección: ").utf8_decode($datose->domicilio_fiscal)."\n".utf8_decode("Teléfono: ").$datose->telefono1." - ".$datose->telefono2."\n" ."Email : ".$datose->correo, $logo, $ext_logo);
//Datos de la empresa
$pdf->numFactura2("$regv->numeracion_08" , "$datose->numero_ruc" );
$pdf->temporaire( "" );
////Enviamos los datos del cliente al método addClientAdresse de la clase Factura
$pdf->addClientAdresse2( $regv->fecha."   /  Hora: ".$regv->hora, utf8_decode($regv->cliente), $regv->numero_documento, utf8_decode($regv->direccion), $regv->estado, utf8_decode($regv->vendedorsitio), utf8_decode($regv->guia));
if ($regv->nombretrib=="IGV") {

        $nombret="V.U.";
    }else{
        $nombret="PRECIO";
    }

//Establecemos las columnas que va a tener la sección donde mostramos los detalles de la venta
$cols=array( "CODIGO"=>23,
             "DESCRIPCION"=>78,
             "CANTIDAD"=>22,
             $nombret=>25,
             "DSCTO"=>20,
             "SUBTOTAL"=>22);
$pdf->addCols2( $cols);
$cols=array( "CODIGO"=>"L",
             "DESCRIPCION"=>"L",
             "CANTIDAD"=>"C",
             $nombret=>"R",
             "DSCTO" =>"R",
             "SUBTOTAL"=>"C");
$pdf->addLineFormat2( $cols);
$pdf->addLineFormat2($cols);
//Actualizamos el valor de la coordenada "y", que será la ubicación desde donde empezaremos a mostrar los datos
$y2= 208; // para el tamaño del cuadro del segundo detalle
//Obtenemos todos los detalles de la venta actual
$rsptad = $factura->ventadetalle($idfactura);
while ($regd = $rsptad->fetch_object()) {
  if ($regd->nombretribu=="IGV") {
        $pv=$regd->valor_uni_item_14;
        $subt=$regd->subtotal;
    }else{
        $pv=$regd->precio;
        $subt=$regd->subtotal2;
    }
  $line = array( "CODIGO"=> "$regd->codigo",
                "DESCRIPCION"=> utf8_decode("$regd->articulo"." - "."$regd->descdet"),
                "CANTIDAD"=> "$regd->cantidad_item_12"." "."$regd->unidad_medida",
                $nombret=> $pv,
                "DSCTO" => "$regd->descuento",
                "SUBTOTAL"=> $subt);
            $size2 = $pdf->addLine2( $y2, $line );
            $y2   += $size2 + 2;
}

$V=new EnLetras(); 
$con_letra=strtoupper($V->ValorEnLetras($regv->importe_total_venta_27,"CON"));
$pdf->addCadreTVAs2("".$con_letra);
$pdf->observSunat2($regv->numeracion_08,$regv->estado,$data[0], $datose->webconsul, $datose->nresolucion);
//Mostramos el impuesto
$pdf->addTVAs2( $regv->sumatoria_igv_22_1, $regv->importe_total_venta_27,"S/ ", $regv->tdescuento);
$pdf->addCadreEurosFrancs2($regv->sumatoria_igv_22_1, $regv->nombretrib);
//Linea para guardar la factura en la carpeta facturas PDF
//$Factura=$pdf->Output($regv->numeracion_08.'.pdf','I');
$Factura=$pdf->Output('../facturasPDF/'.$regv->numeracion_08.'.pdf','F');
}






//Implementamos un método para dar de baja a factura
public function baja($idfactura,$fecha_baja, $com, $hora)
{
$sw=true;
$connect = new mysqli(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);
mysqli_query( $connect, 'SET NAMES "'.DB_ENCODE.'"');
      //Si tenemos un posible error en la conexión lo mostramos
      if (mysqli_connect_errno())
      {
            printf("Falló conexión a la base de datos: %s\n",mysqli_connect_error());
            exit();
      }

    $query="select dt.idfactura, a.idarticulo, dt.cantidad_item_12,  dt.valor_uni_item_14, a.codigo, a.unidad_medida  from detalle_fac_art dt inner join articulo a on dt.idarticulo=a.idarticulo where idfactura = '$idfactura'";
    $resultado = mysqli_query($connect,$query);

    $Idf=array();
    $Ida=array();
    $Ct=array();
    $Cod=array();
    $Vu=array();
    $Um=array();
    $sw=true;

    while ($fila = mysqli_fetch_assoc($resultado)) {
    for($i=0; $i < count($resultado) ; $i++){
        $Idf[$i] = $fila["idfactura"];  
        $Ida[$i] = $fila["idarticulo"];  
        $Ct[$i] = $fila["cantidad_item_12"];  
        $Cod[$i] = $fila["codigo"];  
        $Vu[$i] = $fila["valor_uni_item_14"];  
        $Um[$i] = $fila["unidad_medida"];  

    $sql_update_articulo="update detalle_fac_art de inner join 
    articulo a on de.idarticulo=a.idarticulo 
    set 
     a.saldo_finu=a.saldo_finu + '$Ct[$i]', a.stock=a.stock + '$Ct[$i]', a.ventast=a.ventast - '$Ct[$i]', a.valor_finu=(a.saldo_finu + a.comprast - a.ventast) * a.costo_compra
    where 
    de.idfactura='$Idf[$i]' and de.idarticulo='$Ida[$i]'";
        
        
    //ACTUALIZAR TIPO TRANSACCIon KARDEX
    //Guardar en Kardex
    $sql_kardex="insert into kardex (idcomprobante, idarticulo, transaccion, codigo, fecha, tipo_documento, numero_doc, cantidad, costo_1, unidad_medida, saldo_final, costo_2,valor_final) 

            values 

            ('$idfactura', '$Ida[$i]', 

            'ANULADO', 

            '$Cod[$i]',

             '$fecha_baja $hora', 
             '01',
             (select numeracion_08 from factura where idfactura='$Idf[$i]'), 

             '$Ct[$i]', 

             '$Vu[$i]',

             '$Um[$i]',

             0, 0, 0)";
        }
        //Fin de FOR
         ejecutarConsulta($sql_update_articulo) or $sw=false;
         ejecutarConsulta($sql_kardex) or $sw=false; 
        }
        //Fin de WHILE


          $sqlestado="update factura set estado='3', fecha_baja='$fecha_baja $hora', comentario_baja='$com' where idfactura='$idfactura'";
         ejecutarConsulta($sqlestado) or $sw=false;

   


    return $sw;    

}

//Implementamos un método para dar de baja a factura
public function ActualizarEstado($idfactura,$st)
{
        $sw=true;
        $sqlestado="update factura set estado='$st' where idfactura='$idfactura'";
        ejecutarConsulta($sqlestado) or $sw=false; 
    return $sw;    
}



//Implementamos un método para anular la factura
public function anular($iddoccobranza)
{
       

    $query="delete from doccobranza where iddoccobranza='$iddoccobranza'";
    ejecutarConsulta($query) or $sw=false; 

        //Fin de WHILE
    return $sw;    
}





 
    //Implementar un método para mostrar los datos de un registro a modificar
    public function mostrar($idfactura)
    {
        $sql="select 
        f.idfactura,
        date(f.fecha_emision_01) as fecha,
        f.idcliente,
        p.razon_social as cliente,
        p.numero_documento,
        p.domicilio_fiscal,
        u.idusuario,
        u.nombre as usuario,
        f.tipo_documento_07,
        f.numeracion_08, 
        f.total_operaciones_gravadas_monto_18_2, 
        f.sumatoria_igv_22_1, 
        f.importe_total_venta_27, 
        f.estado 
        from 
        factura f inner join persona p on f.idcliente=p.idpersona inner join usuario u on f.idusuario=u.idusuario where f.idfactura='$idfactura'";
        return ejecutarConsultaSimpleFila($sql);
    }

   
   
    //Implementar un método para mostrar los datos de un registro a modificar
    public function mostrarCabFac()
    {
        $sql="select
        f.idfactura,
     e.numero_ruc as ruc,
     f.tipo_documento_07 as tipodoc,
     f.numeracion_08 as numerodoc
     from 
     factura f inner join persona p on f.idcliente=p.idpersona
     inner join empresa e on f.idempresa=f.idempresa
     ";
        return ejecutarConsulta($sql);
    }
 
    public function listarDetalle($idfactura)
    {
        $sql="select df.idfactura,df.idarticulo,a.nombre,df.cantidad_item_12, df.valor_uni_item_14, df.valor_venta_item_21, df.igv_item from detalle_fac_art df inner join articulo a on df.idarticulo=a.idarticulo where df.idfactura='$idfactura'";
        return ejecutarConsulta($sql);
    }
 
    //Implementar un método para listar los registros
    public function listar($idempresa)
    {
        $sql="select 
        c.idccobranza,
        date_format(c.fechaemision,'%d/%m/%y') as fecha,
        c.idcliente,
        p.razon_social as cliente,
        u.nombre as usuario,
        c.serienumero,
        c.tipo_moneda,
        format(c.total, 2) as total,
        c.tcambio,
        format((c.total * c.tcambio),2) as totalsoles,
        c.estado,
        e.numero_ruc,
        p.email,
        c.observacion 
        from 
        doccobranza c inner join persona p on c.idcliente=p.idpersona 
        inner join usuario u on c.idusuario=u.idusuario 
        inner join empresa e on c.idempresa=e.idempresa where
        e.idempresa='$idempresa' 
        order by idccobranza desc";
        return ejecutarConsulta($sql);  

    }

      public function listarValidar($ano, $mes, $dia, $idempresa)
    {
        $sql="select 
        f.idfactura,
        date_format(f.fecha_emision_01,'%d/%m/%y') as fecha,
        date_format(curdate(),'%Y%m%d') as fechabaja,
        f.idcliente,
        left(p.razon_social,20) as cliente,
        f.vendedorsitio,
        u.nombre as usuario,
        f.tipo_documento_07,
        f.numeracion_08,
        format(f.importe_total_venta_27,2)as importe_total_venta_27 ,
        f.sumatoria_igv_22_1,f.estado,
        e.numero_ruc,
        p.email 
        from 
        factura f inner join persona p on f.idcliente=p.idpersona 
        inner join usuario u on f.idusuario=u.idusuario 
        inner join empresa e on f.idempresa=e.idempresa where
        year(fecha_emision_01)='$ano' and month(fecha_emision_01)='$mes' and day(fecha_emision_01)='$dia' and e.idempresa='$idempresa'
        order by idfactura desc";
        return ejecutarConsulta($sql);  

    }

     public function listarDR($ano, $mes, $idempresa)
    {
        $sql="select 
        f.idfactura,
        f.idcliente,
        numeracion_08 as numerofactura,
        date_format(f.fecha_emision_01,'%d/%m/%y') as fecha,
        date_format(f.fecha_baja,'%d/%m/%y') as fechabaja,
        left(p.razon_social,20) as cliente,
        p.numero_documento as ruccliente,
        f.total_operaciones_gravadas_monto_18_2 as opgravada,        
        f.sumatoria_igv_22_1 as igv,
        format(f.importe_total_venta_27,2) as total,
        f.vendedorsitio,
        f.estado 
        from 
        factura f inner join persona p on f.idcliente=p.idpersona 
        inner join usuario u on f.idusuario=u.idusuario 
        inner join empresa e on f.idempresa=e.idempresa where  year(f.fecha_emision_01)='$ano' and month(f.fecha_emision_01)='$mes' and f.estado in ('0','3') and e.idempresa='$idempresa'
        order by idfactura desc";
        return ejecutarConsulta($sql);  
    }

     public function listarDRdetallado($idcomp, $idempresa)
    {
        $sql="select 
        ncd.codigo_nota,
        ncd.numeroserienota as numero,
        f.numeracion_08,
        date_format(ncd.fecha,'%d/%m/%y') as fecha,
        ncd.desc_motivo as motivo,
        ncd.total_val_venta_og as subtotal,
        ncd.sum_igv as igv,
        ncd.importe_total as total
        from 
        factura f inner join persona p on f.idcliente=p.idpersona 
        inner join usuario u on f.idusuario=u.idusuario 
        inner join empresa e on f.idempresa=e.idempresa inner join notacd ncd on f.idfactura=ncd.idcomprobante
        where f.idfactura='$idcomp'  and e.idempresa='$idempresa'";
        return ejecutarConsulta($sql);  

    }


    public function ventacabecera($iddoccobranza, $idempresa){
        $sql="select 
        d.idccobranza, 
        d.idcliente, 
        d.idusuario, 
        d.condicion, 
        p.razon_social as cliente, 
        p.domicilio_fiscal as direccion, 
        p.tipo_documento, 
        p.numero_documento, 
        p.email, 
        p.telefono1, 
        p.nombre_comercial, 
        concat(u.nombre,' ',u.apellidos) as usuario, 
        d.serienumero, 
        date_format(d.fechaemision,'%d-%m-%Y') as fecha, 
        date_format(d.fechaemision,'%Y-%m-%d') as fecha2,
        date_format(d.fechaemision, '%H:%i:%s') as hora, 
        d.neta, 
        d.tarifa, 
        d.igv, 
        d.total, 
        d.observacion,
        d.tcambio, 
        d.estado,
        d.tipo_moneda, 
        e.numero_ruc, 
        d.tipodoccobranza,
        d.otros
       
        from
          doccobranza d inner join persona p on d.idcliente=p.idpersona inner join empresa e 
          on e.idempresa=d.idempresa inner join  usuario u on d.idusuario=u.idusuario 
          where d.idccobranza='$iddoccobranza' and e.idempresa='$idempresa'";
        return ejecutarConsulta($sql);
    }

    

    public function ventadetalle($iddoccobranza, $tipodoccobranza){

      if ($tipodoccobranza=='producto') {
        $sql="select  
        a.nombre as articulo, 
        a.codigo, 
        format(dac.cantidad,2) as cantidad, 
        dac.precio , 
        format(dac.cantidad * dac.precio,2) as subtotal, 
        a.unidad_medida,
        dac.norden,
        dac.descdet
        from 
        detalle_articulo_doccobranza dac inner join articulo a on dac.iditem=a.idarticulo where dac.iddoccobranza='$iddoccobranza'";
      }else{

         $sql="select  
        a.nombre as articulo, 
        a.codigo, 
        format(dac.cantidad,2) as cantidad, 
        dac.precio, 
        format(dac.cantidad * dac.precio,2) as subtotal, 
        dac.norden,
        dac.descdet
        from 
        detalle_doccobranza dac inner join articulo a on dac.iditem=a.idarticulo where dac.iddoccobranza='$iddoccobranza'";

      }
        
        return ejecutarConsulta($sql);
    }

        public function listarD()
    {
        $sql="select documento from correlativo where documento='factura' or documento='boleta' or documento='nota de credito'or documento='nota de debito' group by documento";
        return ejecutarConsulta($sql);      
    }


     public function listarS($serie)
    {
        $sql="select serie from correlativo where documento='$serie'"; 
        return ejecutarConsulta($sql);      
    }

    public function sumarC($tipo_comprobante, $serie_comprobante){

        $sql="select (numero + 1) as addnumero from `correlativo` where documento='$tipo_comprobante' and serie='$serie_comprobante' order by numero desc limit 1";
        return ejecutarConsulta($sql);      
    }

    public function autogenerarN(){

    $sql="select (idfactura + 1) as Nnum from factura order by idfactura desc limit 1";
    return ejecutarConsulta($sql);      

    }

    public function datosemp($idempresa)
    {

    $sql="select * from empresa where idempresa='$idempresa'";
    return ejecutarConsulta($sql);      
    }

     public function tributo()
    {

    $sql="select * from catalogo5 where estado='1'";
    return ejecutarConsulta($sql);      
    }

    public function afectacionigv()
    {

    $sql="select * from catalogo7";
    return ejecutarConsulta($sql);      
    }

    public function correo()
    {

    $sql="select * from correo";
    return ejecutarConsulta($sql);      
    }


public function AutocompletarRuc($buscar){

  $connect = new mysqli(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);
      mysqli_query( $connect, 'SET NAMES "'.DB_ENCODE.'"');
      //Si tenemos un posible error en la conexión lo mostramos
      if (mysqli_connect_errno())
      {
            printf("Falló conexión a la base de datos: %s\n",mysqli_connect_error());
            exit();
      }

        $sql="select numero_documento, razon_social, domicilio_fiscal from persona where numero_documento like '%$buscar' and estado='1' and tipo_persona='cliente'";

        $Result=mysqli_query($connect, $sql);

        if ($Result->num_rows > 0)
        {
          while($fila=$result->fecth_array())
          {
            $datos[]=$fila['numero_documento'];
          }
          echo json_encode($datos);
        }

      }




      public function traerdcobranza($iddc){

        $sql="select 
        date_format(dc.fechaemision, '%Y-%m-%d') as fechaemi, 
        date_format(dc.fechaemision, '%H:%i:%s') as hora,
        dc.tipo_moneda as moneda, 
        dc.tcambio, 
        p.idpersona, 
        p.tipo_documento,
        p.email,
        p.numero_documento as ruc, 
        p.nombre_comercial, 
        p.domicilio_fiscal, 
        dc.condicion, 
        dc.neta, 
        dc.igv, 
        dc.total 
        from 
        doccobranza dc inner join persona p on dc.idcliente=p.idpersona where idccobranza='$iddc'";
        return ejecutarConsultaSimpleFila($sql);      
    }


    public function listarDetalledc($idcobranza)
    {
        $sql="select ddc.id, ddc.norden, ddc.iditem, a.idarticulo , a.nombre, ddc.descdet, ddc.cantidad, a.codigo, a.unidad_medida, ddc.precio, ddc.igvvalorventa, ddc.igvitem, ddc.vuniitem, ddc.valorventa, dc.neta, dc.igv, dc.total 
        from 
        detalle_doccobranza ddc inner join articulo a on ddc.iditem=a.idarticulo  inner join doccobranza dc on ddc.iddoccobranza=dc.idccobranza where ddc.iddoccobranza='$idcobranza'";
        return ejecutarConsulta($sql);
    }



       
}
?>