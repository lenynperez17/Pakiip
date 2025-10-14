<?php 

//Incluímos inicialmente la conexión a la base de datos

require "../config/Conexion.php";

 

Class Guiaremision

{

    //Implementamos nuestro constructor

    public function __construct()

    {

 

    }

 







 //Implementamos un método para insertar registros para factura

    public function insertar(
      $idguia,
      $serie,
      $numero, 
      $pllegada, 
      $destinatario, 
      $nruc, 
      $ppartida, 
      $fecha, 
      $ncomprobante, 
      $ocompra, 
      $motivo, 
      $idcomprobante,
      $idserie, 
      $idempresa, 
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
      $observaciones,
      $pesobruto,
      $umedidapbruto,
      $codtipotras,
      $tipodoctrans,
      $dniconductor,
      $tipocomprobante,
      $idpersona,
      $ubigeopartida,
      $ubigeollegada)

    {



        $sw=true;

        $sql="insert into guia (
        snumero,  
        pllegada, 
        destinatario, 
        nruc, 
        ppartida, 
        fechat, 
        ncomprobante, 
        ocompra, 
        motivo, 
        idcomprobante, 
        idempresa, 
        fechatraslado,
        rsocialtransportista,
        ructran,
        placa,
        marca,
        cinc,
        container,
        nlicencia,
        ncoductor,
        npedido,
        vendedor,
        costmt,
        fechacomprobante,
        observaciones,
        pesobruto,
        umedidapbruto,
        codtipotras,
        tipodoctrans,
        dniconductor,
        comprobante,
        DetalleSunat,
        idpersona,
        ubigeopartida,
        ubigeollegada)

        values

        (
        '$serie-$numero', 
        '$pllegada', 
        '$destinatario', 
        '$nruc', 
        '$ppartida', 
        '$fecha', 
        '$ncomprobante', 
        '$ocompra', 
        '$motivo', 
        '$idcomprobante', 
        '$idempresa',
        '$fechatraslado',
        '$rsocialtransportista',
        '$ructran',
        '$placa',
        '$marca',
        '$cinc',
        '$container',
        '$nlicencia',
        '$ncoductor',
        '$npedido',
        '$vendedor',
        '$costmt',
        '$fechacomprobante',
        '$observaciones',
        '$pesobruto',
        '$umedidapbruto',
        '$codtipotras',
        '$tipodoctrans',
        '$dniconductor',
        '$tipocomprobante',
        'EMITIDO',
        '$idpersona',
        '$ubigeopartida',
        '$ubigeollegada'
    )";

        //return ejecutarConsulta($sql);

        $idguianew=ejecutarConsulta_retornarID($sql);

        //Para actualizar numeracion de las series de la factura

        if ($tipocomprobante=='01') {
         $sql_update_comprobante="update factura set idguia='$idguianew', guia_remision_29_2='$serie-$numero'  where idfactura='$idcomprobante'";
        }else{
          $sql_update_comprobante="update boleta set idguia='$idguianew', guia_remision_25='$serie-$numero'  where idboleta='$idcomprobante'";
        }
         ejecutarConsulta($sql_update_comprobante) or $sw = false;
         //Fin





        //Para actualizar numeracion de las series de la factura

         $sql_update_numeracion="update numeracion set numero='$numero' where idnumeracion='$idserie'";

         ejecutarConsulta($sql_update_numeracion) or $sw = false;

         //Fin

         return $sw;



    }





function buscarComprobante($idempresa){
    $sql="select 
    f.idfactura, p.tipo_documento as tdcliente, p.numero_documento as ndcliente, p.razon_social as rzcliente ,p.domicilio_fiscal as domcliente, f.tipo_documento_07 as tipocomp, f.numeracion_08 as numerodoc, format(f.total_operaciones_gravadas_monto_18_2,2) as subtotal, format(f.sumatoria_igv_22_1,2) as igv, format(f.importe_total_venta_27,2) as total,  date_format(f.fecha_emision_01, '%Y-%m-%d') as fechafactura, p.idpersona
     from 
     factura f inner join persona p on f.idcliente=p.idpersona inner join empresa e on f.idempresa=e.idempresa 
    where 
    p.tipo_persona='cliente' and f.estado in ('1','5') and e.idempresa='$idempresa' order by f.idfactura desc";

        return ejecutarConsulta($sql); 
}


function buscarComprobanteBoleta($idempresa){
    $sql="select b.idboleta, p.tipo_documento as tdcliente, p.numero_documento as ndcliente, p.razon_social as rzcliente ,p.domicilio_fiscal as domcliente, b.tipo_documento_06 as tipocomp, b.numeracion_07 as numerodoc, 
    format(b.monto_15_2,2) as subtotal, format(b.sumatoria_igv_18_1,2) as igv, format(b.importe_total_23,2) as total,  date_format(b.fecha_emision_01, '%Y-%m-%d') as fechaboleta, p.idpersona
     from 
     boleta b inner join persona p on b.idcliente=p.idpersona inner join empresa e on b.idempresa=e.idempresa 
    where 
    p.tipo_persona='cliente' and b.estado in ('1','5') and e.idempresa='$idempresa' order by b.idboleta desc";

        return ejecutarConsulta($sql); 
}



function buscarComprobanteId($idcomprobante){

    $sql="select  f.idfactura, p.tipo_documento, p.numero_documento, p.razon_social,p.domicilio_fiscal as domicilio, f.tipo_documento_07 as tipocomp, f.numeracion_08 as numerodoc,  df.cantidad_item_12 as cantidad, a.codigo, a.nombre as descripcion, df.valor_uni_item_14 as vui, df.igv_item as igvi, df.precio_venta_item_15_2 as pvi, df.valor_venta_item_21 as vvi, f.total_operaciones_gravadas_monto_18_2 as subtotal, f.sumatoria_igv_22_1 as igv, f.importe_total_venta_27 as total, um.nombreum as unidad_medida 

    from 

    factura f inner join detalle_fac_art df on f.idfactura=df.idfactura inner join articulo a on df.idarticulo=a.idarticulo inner join persona p on f.idcliente=p.idpersona inner join umedida um on um.idunidad=a.unidad_medida

    where p.tipo_persona='cliente'  and f.idfactura='$idcomprobante' and f.estado in('1','5')";

        return ejecutarConsulta($sql); 
}


function buscarComprobanteIdBoleta($idcomprobante){

    $sql="select  b.idboleta, p.tipo_documento, p.numero_documento, p.razon_social,p.domicilio_fiscal as domicilio, b.tipo_documento_06 as tipocomp, b.numeracion_07 as numerodoc,  db.cantidad_item_12 as cantidad, a.codigo, a.nombre as descripcion, db.valor_uni_item_31 as vui, db.igv_item as igvi, db.precio_uni_item_14_2 as pvi, db.valor_venta_item_32 as vvi, b.monto_15_2 as subtotal, b.sumatoria_igv_18_1 as igv, b.importe_total_23 as total, um.nombreum as unidad_medida 
    from 
    boleta b inner join detalle_boleta_producto db on b.idboleta=db.idboleta inner join articulo a on db.idarticulo=a.idarticulo inner join persona p on b.idcliente=p.idpersona inner join umedida um on um.idunidad=a.unidad_medida

    where p.tipo_persona='cliente'  and b.idboleta='$idcomprobante' and b.estado in('1','5')";
    return ejecutarConsulta($sql); 
}



     //Implementar un método para listar los registros

    public function listar($idempresa)

    {

        $sql="select 
        g.idguia, 
        g.fechat, 
        g.snumero, 
        g.destinatario, 
        g.ncomprobante, 
        g.estado,
        g.DetalleSunat
        from 
        guia g  
        order by g.idguia desc";

        return ejecutarConsulta($sql);      

    }

    

    public function cabecera($idguia){

        $sql="select  
        snumero, 
        pllegada, 
        destinatario, 
        nruc, 
        ppartida, 
        date_format(fechat, '%d-%m-%Y' ) as fechat , 
        ncomprobante, 
        c20.descripcion as motivo, 
        g.estado, 
           date_format(fechatraslado, '%d-%m-%Y' ) as fechatraslado ,
            rsocialtransportista,
            ructran,
            placa,
            marca,
            cinc,
            container,
            nlicencia,
            ncoductor,
            npedido,
            vendedor,
            costmt,
            date_format(fechacomprobante, '%d-%m-%Y' ) as fechacomprobante ,
            ocompra,
            comprobante,
            g.observaciones,
            pesobruto,
            um.abre
            
         from 
         guia g inner join catalogo20 c20  
         on g.motivo=c20.codigo  
         inner join umedida um on g.umedidapbruto=um.idunidad
         where g.idguia='$idguia'";

        return ejecutarConsulta($sql);

    }



    public function ventadetalle($idguia, $idempresa, $tipocomp)
    {
      if ($tipocomp=='01') {
        $sql="select 
        a.nombre as articulo, 
        a.codigo, 
        format(dfa.cantidad_item_12,2) as cantidad_item_12, 
        dfa.valor_uni_item_14, 
        format((dfa.cantidad_item_12 * dfa.precio_venta_item_15_2),2) as subtotal, 
        dfa.precio_venta_item_15_2, 
        dfa.valor_venta_item_21, 
        um.nombreum as unidad_medida,
        dfa.descdet, 
        dfa.numero_orden_item_33 as norden 
        from detalle_fac_art dfa inner join articulo a on dfa.idarticulo=a.idarticulo inner join factura f on f.idfactura=dfa.idfactura inner join empresa e on f.idempresa=e.idempresa inner join umedida um on a.unidad_medida=um.idunidad
          where f.idguia='$idguia' and e.idempresa='$idempresa' order by norden";
      }else{
        $sql="select 
        a.nombre as articulo, 
        a.codigo, 
        format(dba.cantidad_item_12,2) as cantidad_item_12, 
        dba.valor_uni_item_31 as valor_uni_item_14 , 
        format((dba.cantidad_item_12 * dba.precio_uni_item_14_2),2) as subtotal, 
        dba.precio_uni_item_14_2 as precio_venta_item_15_2, 
        dba.valor_venta_item_32 as valor_venta_item_21, 
        um.nombreum as unidad_medida,
        dba.descdet, 
        dba.numero_orden_item_29 as norden 
        from detalle_boleta_producto dba inner join articulo a on dba.idarticulo=a.idarticulo inner join boleta b on b.idboleta=dba.idboleta inner join empresa e on b.idempresa=e.idempresa inner join umedida um on a.unidad_medida=um.idunidad
          where b.idguia='$idguia' and e.idempresa='$idempresa' order by norden";

      }
        return ejecutarConsulta($sql);

    }







    public function generarxml($idguia, $idempresa)

    {

      $connect = new mysqli(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);
      mysqli_query( $connect, 'SET NAMES "'.DB_ENCODE.'"');
      //Si tenemos un posible error en la conexión lo mostramos
      if (mysqli_connect_errno())
      {
            printf("Falló conexión a la base de datos: %s\n",mysqli_connect_error());
            exit();
      }

    require_once "../modelos/Factura.php";
    $factura = new Factura();
    $datos = $factura->datosemp($idempresa);
    $datose = $datos->fetch_object();
    $configuraciones = $factura->configuraciones($idempresa);
    $configE=$configuraciones->fetch_object();


    //Inclusion de la tabla RUTAS
    require_once "../modelos/Rutas.php";
    $rutas = new Rutas();
    $Rrutas = $rutas->mostrar2($idempresa);
    $Prutas = $Rrutas->fetch_object();
    $rutadata=$Prutas->rutadata; // ruta de la carpeta DATA
    $rutafirma=$Prutas->rutafirma; // ruta de la carpeta FIRMA
    $rutadatalt=$Prutas->rutadatalt; // ruta de la carpeta DATAALTERNA
    $rutaenvio=$Prutas->rutaenvio; // ruta de la carpeta DATAALTERNA



    $query = "select
     date_format(g.fechat, '%Y-%m-%d') as fechaemision, 
     g.snumero as numeroguia,
     date_format(g.fechat, '%H:%i:%s') as hora,
     g.observaciones,
     g.tipocomprefe,
     g.ncomprobante,
     e.nombre_razon_social,
     e.numero_ruc as rucremitente,
     p.numero_documento as ndocudesti,
     p.tipo_documento as tipodocudesti,
     p.razon_social as razosocdesti,
     g.motivo,
     c20.descripcion as descmotivo,
     g.pesobruto,
     um.abre as  umedidapbruto_,
     g.codtipotras,
     date_format(g.fechatraslado, '%Y-%m-%d') as fechatraslado, 
     g.tipodoctrans,
     g.rsocialtransportista,
     g.ructran,
     g.dniconductor,
     g.placa,
     g.pllegada,
     g.ppartida,
     g.comprobante,
     g.idpersona,
     g.ubigeopartida,
     g.ubigeollegada
     from 
     guia g 
     inner join persona p on g.idpersona=p.idpersona 
     inner join catalogo20 c20 on g.motivo=c20.codigo
     inner join catalogo18 c18 on g.codtipotras=c18.codigo
     inner join empresa e on g.idempresa=e.idempresa
     inner join umedida um on g.umedidapbruto=um.idunidad
     where g.idguia='$idguia'";

     $result = mysqli_query($connect, $query);  

      

    $nombrecomercial=$datose->nombre_comercial;
    $domiciliofiscal=$datose->domicilio_fiscal;
    $codestablecimiento=$datose->ubigueo;
    $codubigueo=$datose->codubigueo;
    $ciudad=$datose->ciudad;
    $distrito=$datose->distrito;
    $interior=$datose->interior;
    $codigopais=$datose->codigopais;


      //Parametros de salida

      $fechaemision=array();
      $numeroguia=array();
      $hora=array();
      $observaciones=array();
      $tipocomprefe=array();
      $ncomprobante=array();
      $nombre_razon_social=array();
      $ndocudesti=array();
      $tipodocudesti=array();
      $razosocdesti=array();
      $motivo=array();
      $descmotivo=array();
      $pesobruto=array();
      $umedidapbruto_=array();
      $codtipotras=array();
      $fechatraslado=array();

       $tipodoctrans=array();
       $rsocialtransportista=array();
       $ructran=array();
       $dniconductor=array();
       $placa=array();
       $pllegada=array();
       $ppartida=array();

       $ubigeopartida=array();
       $ubigueollegada=array();

       $comprobante="";



       $tipocomp='09';

      $con=0; //COntador de variable
      $icbper="";

            

      while($row=mysqli_fetch_assoc($result)){
      for($i=0; $i <= count($result); $i++){
           $fechaemision[$i]=$row["fechaemision"]; //Fecha emision
           $numeroguia=$row["numeroguia"]; 
           $hora[$i]=$row["hora"]; 
           $observaciones=$row["observaciones"]; 
           $tipocomprefe[$i]=$row["tipocomprefe"]; 
           $ncomprobante[$i]=$row["ncomprobante"]; 
           $nombre_razon_social=$datose->nombre_razon_social; 
           $ndocudesti[$i]=$row["ndocudesti"]; 
           $tipodocudesti[$i]=$row["tipodocudesti"]; 
           $razosocdesti[$i]=$row["razosocdesti"]; 
           $motivo[$i]=$row["motivo"]; 
           $descmotivo[$i]=$row["descmotivo"]; 
           $pesobruto[$i]=$row["pesobruto"]; 
           $umedidapbruto_[$i]=$row["umedidapbruto_"]; 
           $codtipotras[$i]=$row["codtipotras"]; 
           $fechatraslado[$i]=$row["fechatraslado"]; 
           $tipodoctrans[$i]=$row["tipodoctrans"]; 
           $rsocialtransportista[$i]=$row["rsocialtransportista"]; 
           $ructran[$i]=$row["ructran"]; 
           $dniconductor[$i]=$row["dniconductor"]; 
           $placa[$i]=$row["placa"]; 
           $pllegada[$i]=$row["pllegada"]; 
           $ppartida[$i]=$row["ppartida"];
           $comprobante=$row["comprobante"] ;

           $ubigeopartida=$row["ubigeopartida"] ;
           $ubigeollegada=$row["ubigeollegada"] ;

           $ruc=$datose->numero_ruc;


//======================================== FORMATO XML ========================================================

 $domiciliofiscal=$datose->domicilio_fiscal;

//Primera parte

$GuiaXML ='<?xml version="1.0" encoding="utf-8"?>
            <DespatchAdvice xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:qdt="urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2" xmlns:sac="urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:udt="urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ccts="urn:un:unece:uncefact:documentation:2" xmlns="urn:oasis:names:specification:ubl:schema:xsd:DespatchAdvice-2">

                <ext:UBLExtensions>
                    <ext:UBLExtension>
                        <ext:ExtensionContent/>
                    </ext:UBLExtension>
                </ext:UBLExtensions>

                <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
                <cbc:CustomizationID>1.0</cbc:CustomizationID>
                <cbc:ID>'.$numeroguia.'</cbc:ID>
                <cbc:IssueDate>'.$fechaemision[$i].'</cbc:IssueDate>
                <cbc:IssueTime>'.$hora[$i].'</cbc:IssueTime>
                <cbc:DespatchAdviceTypeCode>'.$tipocomp.'</cbc:DespatchAdviceTypeCode>
                <cbc:Note>'.$observaciones.'</cbc:Note>

                  <!-- Guía de Remisión Dada de Baja
                Numero de documento
                Código del tipo de documento
                Tipo de Documento -->

              <cac:OrderReference>
                    <cbc:ID>'.$numeroguia.'</cbc:ID>
                    <cbc:OrderTypeCode name="Guía de Remisión">09</cbc:OrderTypeCode>
              </cac:OrderReference> 


                <!-- Documento Adicional Relacionado
                Numero de documento
                Código del tipo de documento -->
                <cac:AdditionalDocumentReference>
                <cbc:ID>'.$ncomprobante[$i].'</cbc:ID>
                <cbc:DocumentTypeCode>06</cbc:DocumentTypeCode>
                </cac:AdditionalDocumentReference>


                <!-- FIRMA DIGITAL -->

                <cac:Signature>
                    <cbc:ID>'.$numeroguia.'</cbc:ID>
                    
                    <cac:SignatoryParty>
                        <cac:PartyIdentification>
                            <cbc:ID>'.$ruc.'</cbc:ID>
                        </cac:PartyIdentification>
                        <cac:PartyName>
                            <cbc:Name><![CDATA['.$nombre_razon_social.']]></cbc:Name>
                        </cac:PartyName>
                    </cac:SignatoryParty>
                    <cac:DigitalSignatureAttachment>
                        <cac:ExternalReference>
                            <cbc:URI>#SIGN-SENCON</cbc:URI>
                        </cac:ExternalReference>
                    </cac:DigitalSignatureAttachment>
                </cac:Signature>

                   
  <!-- Datos del Remitente
                13. Numero de documento de identidad del remitente
                14. Tipo de documento de identidad del remitente
                15. Apellidos y nombres, denominación o razón social del remitente -->

    <cac:DespatchSupplierParty>
      <cbc:CustomerAssignedAccountID schemeID="6">'.$ruc.'</cbc:CustomerAssignedAccountID>
      <cac:Party>
         <cac:PartyLegalEntity>
            <cbc:RegistrationName><![CDATA['.$nombre_razon_social.']]></cbc:RegistrationName>
         </cac:PartyLegalEntity>
      </cac:Party>
   </cac:DespatchSupplierParty>


          <!-- Datos del Destinatario
                16. Numero de documento de identidad del destinatario
                17. Tipo de documento de identidad
                18. Apellidos y nombres, denominación o razón social del destinatario -->

        <cac:DeliveryCustomerParty>
                <cbc:CustomerAssignedAccountID schemeID="'.$tipodocudesti[$i].'">'.$ndocudesti[$i].'</cbc:CustomerAssignedAccountID>
                <cac:Party>
                <cac:PartyLegalEntity>
                <cbc:RegistrationName><![CDATA['.$razosocdesti[$i].']]></cbc:RegistrationName>
                </cac:PartyLegalEntity>
                </cac:Party>
                </cac:DeliveryCustomerParty>



                  <!-- Datos del establecimiento del tercero
                19. Numero de documento de identidad del tercero
                20. Tipo de documento de identidad del tercero
                21. Apellidos y nombres, denominación o razón social del tercero 

                <cac:DeliveryCustomerParty>
                <cbc:CustomerAssignedAccountID schemeID=”6”>10209865209</cbc:CustomerAssignedAccountID>
                <cac:Party>
                <cac:PartyLegalEntity>
                <cbc:RegistrationName><![CDATA[RODRIGUEZ ROQUE AQUILES RUFO]]>
                </cbc:RegistrationName>
                </cac:PartyLegalEntity>
                </cac:Party>
                </cac:DeliveryCustomerParty> -->


                <!-- Datos del envio
                22. Motivo de Traslado
                23. Descripcion del motive del traslado
                24. Indicador del transbordo programado
                25. Peso bruto total de la guía
                26. Numero de bultos o pallets
                27. Modalidad del traslado 
                28. Fecha de inicio de traslado o entrega de bienes al transportista -->

                <cac:Shipment>
                <cbc:ID>1</cbc:ID>
                <cbc:HandlingCode>'.$motivo[$i].'</cbc:HandlingCode>
                <cbc:Information><![CDATA['.$descmotivo[$i].']]></cbc:Information>
                <cbc:GrossWeightMeasure unitCode="KGM">'.$pesobruto[$i].'</cbc:GrossWeightMeasure>
                <cac:ShipmentStage>
                <cbc:TransportModeCode>'.$codtipotras[$i].'</cbc:TransportModeCode>
                <cac:TransitPeriod>
                <cbc:StartDate>'.$fechatraslado[$i].'</cbc:StartDate>
                </cac:TransitPeriod>

                <cac:CarrierParty>
                <cac:PartyIdentification>
                <cbc:ID schemeID="'.$tipodoctrans[$i].'">'.$ructran[$i].'</cbc:ID>
                </cac:PartyIdentification>
                <cac:PartyName>
                <cbc:Name><![CDATA['.$rsocialtransportista[$i].']]>
                </cbc:Name>
                </cac:PartyName>
                </cac:CarrierParty>

                 <cac:DriverPerson>
                <cbc:ID schemeID="01">'.$dniconductor[$i].'</cbc:ID>
                </cac:DriverPerson>

             

                </cac:ShipmentStage>

            <cac:Delivery>
              <cac:DeliveryAddress>
                <cbc:ID>'.$ubigeopartida.'</cbc:ID>
                <cbc:StreetName><![CDATA['.$pllegada[$i].']]></cbc:StreetName>
                </cac:DeliveryAddress>
              </cac:Delivery>

                 
                <cac:OriginAddress>
                <cbc:ID>'.$ubigeollegada.'</cbc:ID>
                <cbc:StreetName><![CDATA['.$ppartida[$i].']]></cbc:StreetName>
                </cac:OriginAddress>

                </cac:Shipment>';



                        }//For cabecera
                        $i=$i+1;
                        $con=$con+1;           
                        }//While cabecera


if ($comprobante=="01") {
 $querydetguia = "select 
        dfa.numero_orden_item_33 as norden,
        format(dfa.cantidad_item_12,2) as cantidad_item_12, 
        um.abre as unidad_medida,
        dfa.descdet, 
        a.nombre as articulo, 
        a.codigo
        from 
        detalle_fac_art dfa inner join articulo a on dfa.idarticulo=a.idarticulo 
        inner join factura f on f.idfactura=dfa.idfactura 
        inner join empresa e on f.idempresa=e.idempresa 
        inner join umedida um on a.unidad_medida=um.idunidad
        where f.idguia='$idguia' order by norden";
}else{
   $querydetguia = "select 
         dba.numero_orden_item_29 as norden,
        format(dba.cantidad_item_12,2) as cantidad_item_12, 
        um.abre as unidad_medida,
        dba.descdet, 
        a.nombre as articulo, 
        a.codigo
        from 
        detalle_boleta_producto dba inner join articulo a on dba.idarticulo=a.idarticulo 
        inner join boleta b on b.idboleta=dba.idboleta 
        inner join empresa e on b.idempresa=e.idempresa 
        inner join umedida um on a.unidad_medida=um.idunidad
        where b.idguia='$idguia' order by norden";
}

 

      $resultf = mysqli_query($connect, $querydetguia); 


      $norden=array();  
      $cantidad_item_12=array(); 
      $unidad_medida=array();  
      $descdet=array();  
      $articulo=array();
      $codigo=array();  


  while($rowf=mysqli_fetch_assoc($resultf)){
      for($if=0; $if < count($resultf); $if++){
           $norden[$if]=$rowf["norden"];
           $cantidad_item_12[$if]=$rowf["cantidad_item_12"];
           $unidad_medida[$if]=$rowf["unidad_medida"];
           $descdet[$if]=$rowf["descdet"];
           $articulo[$if]=$rowf["articulo"];           
           $codigo[$if]=$rowf["codigo"];

                $GuiaXML.='
                <cac:DespatchLine>
                <cbc:ID>'.$norden[$if].'</cbc:ID>
                <cbc:DeliveredQuantity unitCode="'.$unidad_medida[$if].'">'.$cantidad_item_12[$if].'</cbc:DeliveredQuantity>
                <cac:OrderLineReference>
                <cbc:LineID>'.$norden[$if].'</cbc:LineID>
                </cac:OrderLineReference>
                <cac:Item>
                <cbc:Name><![CDATA['.$articulo[$if].']]></cbc:Name>
                <cac:SellersItemIdentification>
                <cbc:ID>'.$codigo[$if].'</cbc:ID>
                </cac:SellersItemIdentification>
                </cac:Item>
                </cac:DespatchLine>';

     }//Fin for
     }//Find e while 

   $GuiaXML.='</DespatchAdvice>';

//FIN DE CABECERA ===================================================================





// Nos aseguramos de que la cadena que contiene el XML esté en UTF-8
  $GuiaXML = mb_convert_encoding($GuiaXML, "UTF-8");
  // Grabamos el XML en el servidor como un fichero plano, para
  // poder ser leido por otra aplicación.
  $gestor = fopen($rutafirma.$ruc."-".$tipocomp."-".$numeroguia.".xml", 'w');
  fwrite($gestor, $GuiaXML);
  fclose($gestor);



  $cabextxml=$rutafirma.$ruc."-".$tipocomp."-".$numeroguia.".xml";
  $cabxml=$ruc."-".$tipocomp."-".$numeroguia.".xml";
  $nomxml=$ruc."-".$tipocomp."-".$numeroguia;
  $nomxmlruta=$rutafirma.$ruc."-".$tipocomp."-".$numeroguia;


              require_once ("../greemter/Greenter.php");
              $invo = new Greenter();
              $out=$invo->getDatFac($cabextxml);
              $filenaz = $nomxml.".zip";
              $zip = new ZipArchive();
              if($zip->open($filenaz,ZIPARCHIVE::CREATE)===true) {
                //$zip->addEmptyDir("dummy");
                $zip->addFile($cabextxml,$cabxml);
                $zip->close();


                //if(!file_exists($rutaz)){mkdir($rutaz);}

                $imagen = file_get_contents($filenaz);
                $imageData = base64_encode($imagen);
                rename($cabextxml, $rutafirma.$cabxml);
                rename($filenaz, $rutaenvio.$filenaz);
              }
              else
              {
                $out="Error al comprimir archivo";

              }

              $data[0] = "";
              $sxe = new SimpleXMLElement($cabextxml, null, true);
              $urn = $sxe->getNamespaces(true);
              $sxe->registerXPathNamespace('ds', $urn['ds']);
              $data = $sxe->xpath('//ds:DigestValue');
              

            $rpta = array ('cabextxml'=>$cabextxml,'cabxml'=>$cabxml, 'rutafirma'=>$cabextxml);
            $sqlDetalle="update guia set DetalleSunat='FIRMADO' , hashc='$data[0]', estado='4' where idguia='$idguia'";
            ejecutarConsulta($sqlDetalle);
  return $rpta;
  } //Fin de funcion




  public function enviarSUN($idguia, $idempresa)
  {
    require_once "../modelos/Factura.php";
    $factura = new Factura();
    $datos = $factura->correo();
    $correo = $datos->fetch_object();


    require_once "../modelos/Consultas.php";  
    $consultas = new consultas();
    $paramcerti = $consultas->paramscerti();
    $datosc = $paramcerti->fetch_object();

     //Inclusion de la tabla RUTAS
    require_once "../modelos/Rutas.php";
    $rutas = new Rutas();
    $Rrutas = $rutas->mostrar2($idempresa);
    $Prutas = $Rrutas->fetch_object();
    $rutafirma=$Prutas->rutafirma; // ruta de la carpeta FIRMA
    $rutaenvio=$Prutas->rutaenvio; // ruta de la carpeta FIRMA
    $rutarpta=$Prutas->rutarpta; // ruta de la carpeta FIRMA
    $rutaunzip=$Prutas->unziprpta; // ruta de la carpeta rpta xml

      $connect = new mysqli(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);
      mysqli_query( $connect, 'SET NAMES "'.DB_ENCODE.'"');
      //Si tenemos un posible error en la conexión lo mostramos
      if (mysqli_connect_errno())
      {
            printf("Falló conexión a la base de datos: %s\n",mysqli_connect_error());
            exit();
      }

        $sqlsendmail="select 
        g.idguia, 
        e.numero_ruc,
        g.snumero
        from 
        guia g inner join empresa e on 
        g.idempresa=e.idempresa 
        where 
        g.idguia='$idguia' and e.idempresa='$idempresa' ";

        $result = mysqli_query($connect, $sqlsendmail); 

      $con=0;
      while($row=mysqli_fetch_assoc($result)){
  //Agregar=====================================================
  // Ruta del directorio donde están los archivos
        $path  = $rutafirma; 
        $files = array_diff(scandir($path), array('.', '..')); 
  //=============================================================
  $guia=$row['numero_ruc']."-09-".$row['snumero'];

    //Validar si existe el archivo firmado
    foreach($files as $file){
    // Divides en dos el nombre de tu archivo utilizando el . 
    $dataSt = explode(".", $file);
    // Nombre del archivo
    $fileName = $dataSt[0];
    $st="1";
    // Extensión del archivo 
    $fileExtension = $dataSt[1];
    if($guia == $fileName){
        $archivoGuia=$fileName;
        // Realizamos un break para que el ciclo se interrumpa
         break;
      }
    }
    //$url=$rutafirma.$archivoFactura.'.xml';
    $ZipGuia=$rutaenvio.$archivoGuia.'.zip';
    copy($ZipGuia, $archivoGuia.'.zip');
    $ZipFinal=$guia.'.zip';
    //echo $ZipFactura;

    $webservice=$datosc->webserviceguia;

    //$webservice="https://e-beta.sunat.gob.pe/ol-ti-itemision-guia-gem-beta/billService?wsdl";
    $usuarioSol=$datosc->usuarioSol;
    $claveSol=$datosc->claveSol;
    $nruc=$datosc->numeroruc;

  //Llamada al WebService=======================================================================
  $service = $webservice; 
  $headers = new CustomHeaders($nruc.$usuarioSol, $claveSol); 
  $client = new SoapClient($service, [ 
    'cache_wsdl' => WSDL_CACHE_NONE, 
    'trace' => TRUE , 
    'soap_version' => SOAP_1_1 ] 
  ); 
  
   try{
   $client->__setSoapHeaders([$headers]); 
   $fcs = $client->__getFunctions();
   $params = array('fileName' => $ZipFinal, 'contentFile' => file_get_contents($ZipFinal) ); 

   
    //Llamada al WebService=======================================================================
   $status = $client->sendBill($params); // Comando para enviar xml a SUNAT
   $conte  =  $client->__getLastResponse();
   $texto=trim(strip_tags($conte));

   
   $zip = new ZipArchive();
   if($zip->open("R".$ZipFinal,ZIPARCHIVE::CREATE)===true) {
   $zip->addEmptyDir("dummy");
   $zip->close();}


     $rpt = fopen("R".$ZipFinal, 'w') or die("no se pudo crear archivo");
     fwrite($rpt, base64_decode($texto));
     fclose($rpt);
     rename("R".$ZipFinal, $rutarpta."R".$ZipFinal);
     unlink($ZipFinal);

  $rutarptazip= $rutarpta."R".$ZipFinal;
  $zip = new ZipArchive;
  if ($zip->open($rutarptazip) === TRUE) 
  {
    $zip->extractTo($rutaunzip);
    $zip->close();
  }
   $xmlFinal=$rutaunzip.'R-'.$guia.'.xml';
   $data[0] = "";
   $rpta[0]="";
      $sxe = new SimpleXMLElement($xmlFinal, null, true);
      $urn = $sxe->getNamespaces(true);
      $sxe->registerXPathNamespace('cac', $urn['cbc']);
      $data = $sxe->xpath('//cbc:Description');
      $rpta = $sxe->xpath('//cbc:ResponseCode');
      
      if ($rpta[0]=='0') {
          $msg="Aceptada por SUNAT";
          $sqlCodigo="update guia set CodigoRptaSunat='$rpta[0]', DetalleSunat='$data[0]', estado='5' where idguia='$idguia'";
        }else{
          $sqlCodigo="update guia set CodigoRptaSunat='$rpta[0]', DetalleSunat='No enviado revizar', estado='4' where idguia='$idguia'";    
      }

      ejecutarConsulta($sqlCodigo);


  return $data[0];


// Llamada al WebService=======================================================================
   }catch (SoapFault $exception){

   $exception=print_r($client->__getLastResponse());
   //$exception=print_r($sqlsendmail);
   $sqlCodigo="update guia set CodigoRptaSunat='', DetalleSunat='VERIFICAR ENVIO' where idguia='$idguia'";    
   ejecutarConsulta($sqlCodigo);
   
   }

  }//Fin While
  //return $exception;
  }


  public function selectD()
  {
    $sql="select idDepa, departamento from ubdepartamento";
    return ejecutarConsulta($sql);    
  }



  //Implementar un método para listar los registros y mostrar en el select
  public function selectP($id)
  {
    $sql="select p.idProv, p.provincia from ubdepartamento d INNER JOIN ubprovincia p ON d.idDepa=p.idDepa
     where d.idDepa='$id' and p.idDepa='$id'";
    return ejecutarConsulta($sql);    
  }


  //Implementar un método para listar los registros y mostrar en el select
  public function selectDI($id)
  {
    $sql="select d.idDist, d.distrito from ubprovincia p inner join ubdistrito d on p.idProv=d.idProv 
    where p.idProv='$id' and d.idProv='$id'";
    return ejecutarConsulta($sql);    
  }



    

}

?>

