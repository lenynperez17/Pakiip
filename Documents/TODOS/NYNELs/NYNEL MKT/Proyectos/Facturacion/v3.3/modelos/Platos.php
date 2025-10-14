<?php 
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";
 
Class Plato
{
    //Implementamos nuestro constructor
    public function __construct()
    {
 
    }
 
    //Implementamos un método para insertar registros
    public function insertar($idcategoria, $codigo, $nombre, $precio, $imagen)
    {
        $sql="insert into
         platos 
         (
            idcategoria,
            codigo,
            nombre,
            precio, 
            imagen, 
            estado
             
            )
    values ('$idcategoria','$codigo','$nombre','$precio','$imagen', '1')";
        return ejecutarConsulta($sql);
    }
 
    //Implementamos un método para editar registros
    public function editar($idplato, $idcategoria, $codigo, $nombre, $precio, $imagen)
    {


        $sql="update platos 
        set 
        idcategoria='$idcategoria', 
        codigo='$codigo', 
        nombre='$nombre', 
        precio='$precio', 
        imagen='$imagen'
        
        where 
        idplato='$idplato'";
        return ejecutarConsulta($sql);
    }
 
    //Implementamos un método para desactivar registros
    public function desactivar($idarticulo)
    {
        $sql="update articulo SET estado='0' where idarticulo='$idarticulo'";
        return ejecutarConsulta($sql);
    }
 
    //Implementamos un método para activar registros
    public function activar($idarticulo)
    {
        $sql="update articulo 
        set 
        estado='1' 
        where 
        idarticulo='$idarticulo'";
        return ejecutarConsulta($sql);
    }
 
    //Implementar un método para mostrar los datos de un registro a modificar
    public function mostrar($idplato)
    {
        $sql="select * from  platos  
         p inner join categoria_plato c on p.idcategoria=c.idcategoria 
         where 
         p.idplato='$idplato'";
        return ejecutarConsultaSimpleFila($sql);
    }


    public function valoresiniciales($codigoarti)
    {
        $sql="select year(fecharegistro) as anoarti from  articulo  where codigo='$codigoarti'";
        return ejecutarConsulta($sql);
    }


    public function articuloBusqueda($codigo)
    {
        $sql="select nombre, stock, precio_venta, unidad_medida from articulo where codigo='$codigo'";
        return ejecutarConsultaSimpleFila($sql);
    }


 
    //Implementar un método para listar los registros
    public function listar()
    {
        $sql="select 
        idplato, 
        codigo, 
        nombre, 
        precio, 
        imagen,
        estado 
        from 
        platos";
        return ejecutarConsulta($sql);      
    }
 
    public function kardexArticulo($ano,$codigo, $idempresa){

        $sql="select
        a.idarticulo, 
        a.codigo, 
        a.nombre, 
        a.saldo_iniu, 
        a.costo_compra, 
        a.valor_iniu, 
        a.valor_finu, 
        date_format(k.fecha, '%d/%m/%y') as fecha, 
        ct1.descripcion, 
        k.numero_doc, 
        k.transaccion, 
        k.cantidad as cantidad, 
        k.costo_1, 
        a.unidad_medida, 
        k.saldo_final, 
        k.costo_2, 
        format(k.valor_final,2) as valor_final,
        a.ventast,
        a.comprast,
        a.saldo_finu,
        k.tcambio,
        k.moneda,
        a.precio_final_kardex,
        year(a.fecharegistro) as anoarti
        from 
        kardex k inner join articulo a on k.idarticulo=a.idarticulo inner join catalogo1 ct1 on k.tipo_documento=ct1.codigo inner join  almacen al on a.idalmacen=al.idalmacen inner join empresa e on al.idempresa=e.idempresa where year(k.fecha)='$ano'  and  a.codigo='$codigo' and e.idempresa='$idempresa' order by k.fecha, k.idkardex";
        return ejecutarConsulta($sql);      
    }

    public function kardexArticulovaloriniciales($ano, $fecha, $codigo, $idempresa){

        $sql="select 
        a.codigo, 
        a.nombre, 
        a.saldo_iniu, 
        a.costo_compra, 
        a.valor_iniu, 
        a.valor_finu, 
        date_format(k.fecha, '%d/%m/%y') as fecha, 
        ct1.descripcion, 
        k.numero_doc, 
        k.transaccion, 
        k.cantidad as cantidad, 
        k.costo_1, 
        a.unidad_medida, 
        k.saldo_final, 
        k.costo_2, 
        format(k.valor_final,2) as valor_final,
        a.ventast,
        a.comprast,
        a.saldo_finu,
        k.tcambio,
        k.moneda,
        a.idarticulo,
        a.precio_final_kardex,
        year(a.fecharegistro) as anoarti
        from 
        kardex k inner join articulo a on k.idarticulo=a.idarticulo inner join catalogo1 ct1 on k.tipo_documento=ct1.codigo inner join  almacen al on a.idalmacen=al.idalmacen inner join empresa e on al.idempresa=e.idempresa where year(k.fecha)='$ano'  and  a.codigo='$codigo' and month(k.fecha) in ($fecha) and e.idempresa='$idempresa' order by k.fecha, k.idkardex";
        return ejecutarConsulta($sql);      
    }

    public function obteneridarticulo($codigo){

        $sql="select
        idarticulo,
        nombre,
        costo_compra, 
        saldo_iniu, 
        valor_iniu, 
        valor_finu, 
        saldo_finu,
        stock
        from 
        articulo
        where
        codigo='$codigo'";
        return ejecutarConsulta($sql);      
    }


    public function insertarkardexArticulo($idempresa, $idarticulo, $codigoarti ,  $ano, $costoi, $saldoi, $valori, $costof, $saldof, $valorf){

        $sqlValor="select * from  valfinarticulo";
        $regVal=ejecutarConsulta($sqlValor);   

        $idempresa_='';
        $idarticulo_='';
        $codigoa_='';
        $ano_='';
         $date = date('Y-m-d', time());

        while($reg= $regVal->fetch_object()){
            $idempresa_=$reg->idempresa;
            $idarticulo_=$reg->idarticulo;
            $codigoa_=$reg->codigoart;
            $ano_=$reg->ano;
        }   

        if ($idempresa_== $idempresa_ &&  $idarticulo==$idarticulo_ && $ano==$ano_  && $codigoa_==$codigoarti )  {
                //$sql="delete from valfinarticulo";
                //$idNew=ejecutarConsulta_retornarID($sql);

                $sql="update valfinarticulo set costoi='$costoi', saldoi='$saldoi', valori='$valori', costof='$costof', saldof='$saldof', valorf='$valorf' where idempresa='$idempresa' and idarticulo='$idarticulo' and codigo='$codigoarti' and ano='$ano'";
        }else{
                $sql="insert into valfinarticulo (idempresa, idarticulo, codigoart, ano, costoi, saldoi, valori, costof, saldof, valorf, fechag) values ('$idempresa','$idarticulo', '$codigoarti','$ano','$costoi','$saldoi','$valori','$costof','$saldof','$valorf','$date')";
                $idNew=ejecutarConsulta_retornarID($sql);
                $sql="update valfinarticulo set  id2='$idNew' where idempresa='$idempresa' and idarticulo='$idarticulo' and codigo='$codigoarti' and ano='$ano'";
        }
        return ejecutarConsulta($sql);      
    }


    public function saldoanterior($ano, $codigo, $idempresa)
    {

        $sql="select costof, saldof, valorf from valfinarticulo where codigoart='$codigo' and ano='$ano' - 1 and idempresa='$idempresa' order by id desc limit 1";
        return ejecutarConsulta($sql);      
    }


        public function saldoinicialV($ano, $codigo, $idempresa)
    {

        $sql="select costoi, saldoi, valori, nombre from valfinarticulo vfa inner join articulo a on vfa.idarticulo=a.idarticulo where codigoart='$codigo' and ano='$ano' and idempresa='$idempresa' order by id desc limit 1";
        return ejecutarConsulta($sql);      
    }

     public function kardexArticulototales($ano, $fecha, $codigo){

        $sql="select 
        a.saldo_iniu, 
        a.costo_compra, 
        a.valor_iniu, 
        a.valor_finu, 
        date_format(k.fecha, '%d/%m/%y') as fecha, 
        ct1.descripcion, 
        k.numero_doc, 
        k.transaccion, 
        k.cantidad as cantidad, 
        format(k.costo_1,2) as costo_1, 
        a.unidad_medida, 
        k.saldo_final, 
        k.costo_2, 
        format(k.valor_final,2) as valor_final,
        a.ventast,
        a.comprast
        from 
        kardex k inner join articulo a on k.idarticulo=a.idarticulo inner join catalogo1 ct1 on k.tipo_documento=ct1.codigo  where year(k.fecha)='$ano'  and  a.codigo='$codigo' and month(k.fecha) in ($fecha) order by k.fecha, k.idkardex";
        return ejecutarConsulta($sql);      
    }

    //===========================INVENTARIO==============================

      public function inventariovalorizado($idempresa){
        $sql="select codigo, 
        a.nombre, 
        format(a.saldo_iniu,2) as saldo_iniu, 
        format(a.comprast,2) as comprast, 
        format(a.ventast,2) as ventast, 
        format(a.saldo_finu,2) as saldo_finu, 
        a.costo_compra, 
        format(a.valor_finu,2) as valor_finu 
        from 
        articulo a inner join almacen al on a.idalmacen=al.idalmacen inner join empresa e on al.idempresa=e.idempresa where not codigo='1000ncdg' and e.idempresa='$idempresa' order by codigo";
        return ejecutarConsulta($sql);
    }

      public function inventariovalorizadoxcodigo($codigo, $idempresa){
        $sql="select 
        a.codigo, 
        a.nombre, 
        format(a.saldo_iniu,2) as saldo_iniu, 
        format(a.comprast,2) as comprast, 
        format(a.ventast,2) as ventast, 
        format(a.saldo_finu,2) as saldo_finu, 
        a.costo_compra, 
        format(a.valor_finu,2) as valor_finu 
        from 
        articulo a inner join almacen al on a.idalmacen=al.idalmacen inner join empresa e on al.idempresa=e.idempresa where a.codigo='$codigo' and not a.codigo='1000ncdg' and e.idempresa='$idempresa'";
        return ejecutarConsulta($sql);
    }

    public function totalinventariovalorizado($idempresa){
        $sql="select 
        format(sum(a.saldo_iniu),2) as saldo_iniu, 
        format(sum(a.comprast),2) as comprast, 
        format(sum(a.ventast),2) as ventast, 
        format(sum(a.saldo_finu),2) as saldo_finu, 
        format(sum(a.valor_finu),2) as  valor_finu  
        from 
        articulo a inner join almacen al on a.idalmacen=al.idalmacen inner join empresa e on al.idempresa=e.idempresa where not codigo='1000ncdg' and e.idempresa='$idempresa'";
        return ejecutarConsulta($sql);
    }


    public function listarActivosVentaxCodigo($codigob, $idempresa)
    {
        $sql="select 
        a.idarticulo,
        a.idalmacen, 
        a.codigo_proveedor, 
        a.codigo, 
        a.nombre, 
        a.idfamilia, 
        f.descripcion as familia, 
        a.costo_compra, 
        a.precio_venta, 
        a.stock, 
        a.imagen, 
        a.estado, 
        a.unidad_medida, 
        (a.precio_venta * 1.18) as precio_unitario,
        a.cicbper,
        a.nticbperi,
        a.ctticbperi,
        format(a.mticbperu,2) as mticbperu
         from articulo a inner join familia f on a.idfamilia=f.idfamilia inner join almacen al on a.idalmacen=al.idalmacen inner join empresa e on al.idempresa=e.idempresa where a.estado='1' and a.codigo='$codigob' and e.idempresa='$idempresa' or a.nombre='$codigob'";
        return ejecutarConsultaSimpleFila($sql);   
        //format(a.precio_venta,2) as precio_venta,   
    }


    public function kardexxarticulo($codigo, $ano)
    {
        $sql="select a.codigo, a.nombre , va.ano, va.costoi, format(va.saldoi,2) as saldoi ,format(va.valori,2) as valori, format(va.costof,2) as costof, format(va.saldof,2) as saldof, format(va.valorf,2) as valorf, va.fechag from  valfinarticulo va inner join articulo a on va.idarticulo=a.idarticulo where codigo='$codigo' and ano='$ano' order by va.id desc limit 1";
        return ejecutarConsulta($sql);   
        //format(a.precio_venta,2) as precio_venta,   
    }

    public function resetearvalores()
    {
        $sql="delete from valfinarticulo";
        return ejecutarConsulta($sql);   
        //format(a.precio_venta,2) as precio_venta,   
    }


    public function buscararticulo($key)
    {

define('DB_SERVER', 'localhost');
define('DB_SERVER_USERNAME', 'YOUR DATA BASE USERNAME');
define('DB_SERVER_PASSWORD', 'YOUR DATA BASE PASSWORD');
define('DB_DATABASE', 'YOUR DATA BASE NAME');

$connexion = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

$html = '';

$result = $connexion->query(
    'select
        a.idarticulo,
        a.idalmacen, 
        a.codigo_proveedor, 
        a.codigo, 
        a.nombre, 
        a.idfamilia, 
        f.descripcion as familia, 
        a.costo_compra, 
        format(a.precio_venta,2) as precio_venta, 
        a.stock, 
        a.imagen, 
        a.estado, 
        a.unidad_medida, 
        (a.precio_venta * 1.18) as precio_unitario
        from articulo a inner join familia f on a.idfamilia=f.idfamilia
        where a.estado="1" and not a.nombre ="1000ncdg" and a.nombre like "%'.strip_tags($key).'%" limit 8'
);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {                
        $html .= '<div><a class="suggest-element"  codigo="'.utf8_encode($row['codigo']).'"  unidad_medida="'.utf8_encode($row['unidad_medida']).'"  precio_venta="'.utf8_encode($row['precio_venta']).'" stock="'.utf8_encode($row['stock']).'"  nombre="'.utf8_encode($row['nombre']).'"  precio_unitario="'.utf8_encode($row['precio_unitario']).'" id="'.$row['idarticulo'].'">'.utf8_encode($row['nombre']).'</a></div>';
    }
}
echo $html;
}



public function select()
	{
		$sql="select * from categoria_plato where estado=1 order by idcategoria desc ";
		return ejecutarConsulta($sql);		
	}



	//Implementamos un método para insertar registros
	public function insertarCategoria($nombreCategoria)
	{
		$sql="insert into categoria_plato (nombreCategoria, estado)
		values ('$nombreCategoria','1')";
		return ejecutarConsulta($sql);
	}


	//Implementamos un método para editar registros
	public function editarCategoria($idfamilia,$nombre)
	{
		$sql="update familia set descripcion='$nombre' where idfamilia='$idfamilia'";
		return ejecutarConsulta($sql);
	}



}
 
?>