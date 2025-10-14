<?php 

//Incluímos inicialmente la conexión a la base de datos

require "../config/Conexion.php";

 

Class Liquidacion

{

    //Implementamos nuestro constructor

    public function __construct()

    {

 

    }

 

    //Implementamos un método para insertar registros

    public function insertar($idalmacen,$codigo_proveedor,$codigo,$nombre,$idfamilia,$unidad_medida,$costo_compra,$saldo_iniu,$valor_iniu,$saldo_finu,$valor_finu,$stock,$comprast,$ventast,$portador,$merma, $precio_venta,$imagen, $codigosunat, $ccontable, $precio2, $precio3, $cicbper, $nticbperi, $ctticbperi, $mticbperu, $codigott, $desctt, $codigointtt, $nombrett, $lote, $marca, $fechafabricacion, $fechavencimiento, $procedencia, $fabricante, $registrosanitario, $fechaingalm, $fechafinalma, $proveedor, $seriefaccompra, $numerofaccompra, $fechafacturacompra, $limitestock, $tipoitem,  $umedidacompra, $factorc, $descripcion)

    {

        $sql="insert into

         articulo 

         (

            idalmacen,
            codigo_proveedor,
            codigo,
            nombre,
            idfamilia,
            unidad_medida, 
            costo_compra, 
            saldo_iniu, 
            valor_iniu,
            saldo_finu, 
            valor_finu, 
            stock, 
            comprast, 
            ventast, 
            portador, 
            merma, 
            precio_venta, 
            imagen, 
            valor_fin_kardex, 
            precio_final_kardex,
            fecharegistro,
            codigosunat, 
            ccontable,
            precio2,
            precio3,
            cicbper,
            nticbperi,
            ctticbperi,
            mticbperu,
            codigott, desctt, codigointtt, nombrett,
            lote, 
            marca,
            fechafabricacion,
            fechavencimiento, 
            procedencia, 
            fabricante, 
            registrosanitario, 
            fechaingalm, 
            fechafinalma, 
            proveedor, 
            seriefaccompra, 
            numerofaccompra, 
            fechafacturacompra,
            limitestock,
            tipoitem,
            umedidacompra,
            factorc,
            descrip

            )

    values ('$idalmacen','$codigo_proveedor','$codigo','$nombre','$idfamilia','$unidad_medida','$costo_compra','$saldo_iniu','$valor_iniu','$saldo_finu','$valor_finu','$stock','$comprast','$ventast','$portador','$merma','$precio_venta','$imagen','$valor_finu','$costo_compra', now()  ,'$codigosunat', '$ccontable','$precio2', '$precio3' ,'$cicbper','$nticbperi','$ctticbperi','$mticbperu', '$codigott', '$desctt', '$codigointtt', '$nombrett', '$lote', '$marca', '$fechafabricacion', '$fechavencimiento', '$procedencia', '$fabricante', '$registrosanitario', '$fechaingalm', '$fechafinalma', '$proveedor', '$seriefaccompra', '$numerofaccompra', '$fechafacturacompra', '$limitestock', '$tipoitem', '$umedidacompra', '$factorc', '$descripcion')";

            $idartinew= ejecutarConsulta_retornarID($sql);


            $sqlreginv="insert into 
            reginventariosanos 
            (
              codigo, 
              denominacion, 
              costoinicial, 
              saldoinicial, 
              valorinicial, 
              compras,
              ventas,
              saldofinal,
              costo,
              valorfinal,
              ano
              ) 
            values
                (
              '$codigo', 
              '$nombre', 
              '$costo_compra', 
              '$saldo_iniu', 
              '$valor_iniu', 
              '$comprast',
              '$ventast',
              '$saldo_finu',
              '$costo_compra',
              '$valor_finu',
              year(CURDATE())
              )";

          ejecutarConsulta($sqlreginv);


        $sqlsubarti="insert into 
            subarticulo 
            (
              idarticulo, 
              codigobarra, 
              valorunitario, 
              preciounitario, 
              stock, 
              umventa,
              estado
              ) 
            values
                (
             '$idartinew',
             '$codigo',
             '$costo_compra', 
             '$costo_compra', 
             '$stock',
             '$unidad_medida',
             '1'
              )";

         return ejecutarConsulta($sqlsubarti);

         



    }

 

    //Implementamos un método para editar registros

    public function editar($idarticulo,$idalmacen,$codigo_proveedor,$codigo,$nombre,$idfamilia,$unidad_medida,$costo_compra,$saldo_iniu,$valor_iniu,$saldo_finu,$valor_finu,$stock,$comprast,$ventast,$portador,$merma, $precio_venta,$imagen, $codigosunat, $ccontable, $precio2, $precio3, $cicbper, $nticbperi, $ctticbperi, $mticbperu, $codigott, $desctt, $codigointtt, $nombrett, $lote, $marca, $fechafabricacion, $fechavencimiento, $procedencia, $fabricante, $registrosanitario, $fechaingalm, $fechafinalma, $proveedor, $seriefaccompra, $numerofaccompra, $fechafacturacompra, $limitestock, $tipoitem, $umedidacompra, $factorc, $descripcion)

    {

        $sql="update articulo 
        set 
        idalmacen='$idalmacen', 
        codigo_proveedor='$codigo_proveedor', 
        codigo='$codigo', 
        nombre='$nombre', 
        idfamilia='$idfamilia', 
        unidad_medida='$unidad_medida', 
        costo_compra='$costo_compra', 
        saldo_iniu='$saldo_iniu', 
        valor_iniu='$valor_iniu', 
        saldo_finu='$saldo_finu', 
        valor_finu='$valor_finu', 
        stock='$stock', 
        comprast='$comprast', 
        ventast='$ventast', 
        portador='$portador', 
        merma='$merma', 
        precio_venta='$precio_venta', 
        imagen='$imagen',
        codigosunat='$codigosunat',
        ccontable='$ccontable',
        precio2='$precio2',
        precio3='$precio3',
        cicbper='$cicbper',
        nticbperi='$nticbperi',
         ctticbperi='$ctticbperi',
         mticbperu='$mticbperu',
         codigott='$codigott', 
         desctt='$desctt',
         codigointtt='$codigointtt', 
         nombrett='$nombrett',
         lote='$lote',
         marca='$marca',
         fechafabricacion='$fechafabricacion',
         fechavencimiento='$fechavencimiento',
         procedencia='$procedencia',
         fabricante='$fabricante',
         registrosanitario='$registrosanitario',
         fechaingalm='$fechaingalm',
         fechafinalma='$fechafinalma',
         proveedor='$proveedor',
         seriefaccompra='$seriefaccompra',
         numerofaccompra='$numerofaccompra',
         fechafacturacompra='$fechafacturacompra',
         limitestock='$limitestock',
         tipoitem='$tipoitem',
         umedidacompra='$umedidacompra',
         factorc='$factorc', 
         descrip='$descripcion'
        where 
        idarticulo='$idarticulo'";

        $sqlsubartidel="delete from subarticulo where idarticulo='$idarticulo'";
        ejecutarConsulta($sqlsubartidel);

        $sqlsubarticrear="insert into subarticulo (idarticulo, codigobarra, valorunitario, preciounitario, stock, umventa, estado) values  ('$idarticulo', '$codigo', '$costo_compra','$costo_compra', '$stock', '$unidad_medida', '1')";
        ejecutarConsulta($sqlsubarticrear);

        return ejecutarConsulta($sql);

    }

 




    public function buscarClientes($doc)
	{
		$sql="select 
		* 
		from persona 
		where 
		tipo_persona='CLIENTE' and numero_documento='$doc' and estado='1' ";
		return ejecutarConsultaSimpleFila($sql);		
	}








	public function buscarclientepredict($key)
	{

define('DB_SERVER', 'localhost');
define('DB_SERVER_USERNAME', 'YOUR DATA BASE USERNAME');
define('DB_SERVER_PASSWORD', 'YOUR DATA BASE PASSWORD');
define('DB_DATABASE', 'YOUR DATA BASE NAME');

$connexion = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

$html = '';
$cc=1;
//$key = $_POST['key'];

$result = $connexion->query(
    'select * from persona where tipo_persona="cliente" and razon_social like "%'.strip_tags($key).'%" or numero_documento like "%'.strip_tags($key).'%" and not idpersona="1" limit 8'
);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {                
        $html .= '<div ><a class="suggest-element"  ndocumento="'.utf8_encode($row['numero_documento']).'" 
         nombrecli="'.utf8_encode($row['razon_social']).'" id="'.$row['idpersona'].'">'.utf8_encode($row['razon_social']).'</a></div>';
        $cc =  $cc +  1;
    }
}
echo $html;
}







}

 

?>