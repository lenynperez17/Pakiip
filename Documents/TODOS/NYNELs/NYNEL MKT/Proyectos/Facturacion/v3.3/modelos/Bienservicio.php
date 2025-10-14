<?php 
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

Class Bienes_inmuebles
{
	//Implementamos nuestro constructor
	public function __construct()
	{

	}

	//Implementamos un método para insertar registros
	public function insertar($descripcion, $codigo , $valor, $idempresa , $tipo, $ccontable )
	{
		$sql="insert into servicios_inmuebles
         (descripcion, codigo, valor, idempresa, tipo, ccontable) 
         values 
         ('$descripcion', '$codigo', '$valor', '$idempresa' , '$tipo', '$ccontable')";
		return ejecutarConsulta($sql);
	}

	//Implementamos un método para editar registros
	public function editar($id, $descripcion, $codigo , $valor, $idempresa , $tipo, $ccontable)
	{
		$sql="update servicios_inmuebles set descripcion='$descripcion' , codigo='$codigo', valor='$valor', idempresa='$idempresa', tipo='$tipo', ccontable='$ccontable' where id='$id'";
		return ejecutarConsulta($sql);
	}

	//Implementamos un método para desactivar familias
	public function desactivar($id)
	{
		$sql="update servicios_inmuebles set estado='0' where id='$id' ";
		return ejecutarConsulta($sql);
	}	

	//Implementamos un método para activar categorías
	public function activar($id)
	{
		$sql="update servicios_inmuebles set estado='1' where id='$id' ";
		return ejecutarConsulta($sql);
	}

	//Implementar un método para mostrar los datos de un registro a modificar
	public function mostrar($id)
	{
		$sql="select * from servicios_inmuebles where id='$id'";
		return ejecutarConsultaSimpleFila($sql);
	}

	//Implementar un método para listar los registros
	public function listar($idempresa)
	{
	$sql="select * from servicios_inmuebles si inner join empresa e on si.idempresa=e.idempresa where e.idempresa='$idempresa'";
		return ejecutarConsulta($sql);		
	}
	//Implementar un método para listar los registros y mostrar en el select
	public function select()
	{
		$sql="select * from servicios_inmuebles where estado='1'";
		return ejecutarConsulta($sql);		
	}



	//Llenar combo de series de Factura
	public function llenarSerieOrden(){
    $sql="select idservicios_inmuebles, serie from servicios_inmuebles where tipo_documento='99'";
    return ejecutarConsulta($sql); 
	}

    //Llenar combo de series de guia remision
	public function llenarSerieGuia(){
    $sql="select idservicios_inmuebles, serie from servicios_inmuebles where tipo_documento='09' or tipo_documento='56' ";
    return ejecutarConsulta($sql);      
    }

    //Llenar combo de series de nota de credito
	public function llenarSerieNcredito(){
    $sql="select idservicios_inmuebles, serie from servicios_inmuebles where tipo_documento='07'";
    return ejecutarConsulta($sql);  
	}

    public function llenarSerieNdebito(){
    $sql="select idservicios_inmuebles, serie from servicios_inmuebles where tipo_documento='08'";
    return ejecutarConsulta($sql);          
    }

    //Llenar combo de series de Boleta
	public function llenarSerieBoleta($idusuario){
    $sql="select n.idservicios_inmuebles, n.serie from servicios_inmuebles n inner join detalle_usuario_servicios_inmuebles dn on n.idservicios_inmuebles=dn.idservicios_inmuebles inner join usuario u on dn.idusuario=u.idusuario where n.tipo_documento='03' and dn.idusuario='$idusuario' group by n.serie";
    return ejecutarConsulta($sql);    // Las series van deacuerdo a las asginaciones que e le de en los permisos de usuario       
    }

    	//Llenar combo de series de Factura
	public function llenarSerieFactura($idusuario){
    //$sql="select idservicios_inmuebles, serie from servicios_inmuebles where tipo_documento='01'";
    $sql="select n.idservicios_inmuebles, n.serie from servicios_inmuebles n inner join detalle_usuario_servicios_inmuebles dn on n.idservicios_inmuebles=dn.idservicios_inmuebles inner join usuario u on dn.idusuario=u.idusuario where n.tipo_documento='01' and dn.idusuario='$idusuario' group by n.serie";
    return ejecutarConsulta($sql); // Las series van deacuerdo a las asginaciones que e le de en los permisos de usuario     
	}

    //Función para incrementar numero de factura.
    public function llenarNumeroFactura($serie){
    //$sql="select (numero+1) as Nnumero from servicios_inmuebles where tipo_documento='01' and idservicios_inmuebles='$serie'";

	$sql="select (n.numero+1) as Nnumero from servicios_inmuebles n inner join detalle_usuario_servicios_inmuebles dn on n.idservicios_inmuebles=dn.idservicios_inmuebles inner join usuario u on dn.idusuario=u.idusuario where n.tipo_documento='01' and n.idservicios_inmuebles='$serie' limit 1";    
    return ejecutarConsulta($sql);    // Las series van deacuerdo a las asginaciones que e le de en los permisos de usuario        
    }

    //Función para incrementar numero de boleta.
    public function llenarNumeroBoleta($serie){
    $sql="select (n.numero+1) as Nnumero from servicios_inmuebles n inner join detalle_usuario_servicios_inmuebles dn on n.idservicios_inmuebles=dn.idservicios_inmuebles inner join usuario u on dn.idusuario=u.idusuario where n.tipo_documento='03' and n.idservicios_inmuebles='$serie' limit 1";
    return ejecutarConsulta($sql);  // Las series van deacuerdo a las asginaciones que e le de en los permisos de usuario     
    }

    //Función para incrementar numero de ORDEN DE SERVICIO.
    public function llenarNumeroOrden($idservicios_inmuebles){
    $sql="select (numero+1) as Nnumero from servicios_inmuebles where tipo_documento='99' and idservicios_inmuebles='$idservicios_inmuebles'";
    return ejecutarConsulta($sql);      
    }

    //Función para incrementar numero de guia.
    public function llenarNumeroGuia($serie){
    $sql="select (numero+1) as Nnumero from servicios_inmuebles where tipo_documento='09' or tipo_documento='56'  and idservicios_inmuebles='$serie'";
    return ejecutarConsulta($sql);      
    }

    //Función para incrementar numero de nota de credito.
    public function llenarNumeroNcredito($serie){
    $sql="select (numero+1) as Nnumero from servicios_inmuebles where tipo_documento='07' and idservicios_inmuebles='$serie'";
    return ejecutarConsulta($sql);      
    }


        //Función para incrementar numero de nota de debito.
    public function llenarNumeroNdedito($serie){
    $sql="select (numero+1) as Nnumero from servicios_inmuebles where tipo_documento='08' and idservicios_inmuebles='$serie'";
    return ejecutarConsulta($sql);      
    }


    

    public function updateservicios_inmuebles($numero, $idservicios_inmuebles){
    $sql="update servicios_inmuebles set numero='$numero' where idservicios_inmuebles='$idservicios_inmuebles'";
    return ejecutarConsulta($sql);

    }

    public function listarSeries()
	{
		$sql="select * from servicios_inmuebles where estado='1'";
		return ejecutarConsulta($sql);		
	}


	public function listarActivosVenta($idempresa)
    {
        $sql="select * from servicios_inmuebles si inner join empresa e on si.idempresa=e.idempresa where si.estado='1' and e.idempresa='$idempresa' order by id desc";
        return ejecutarConsulta($sql);      
    }

    public function listarActivosVentaDC($idempresa)
    {
        $sql="select id, descripcion, codigo, valor from servicios_inmuebles si inner join empresa e on si.idempresa=e.idempresa where si.estado='1' and e.idempresa='$idempresa' order by id desc";
        return ejecutarConsulta($sql);      
    }

    public function listarActivosVentaDCS()
    {
        $sql="select idarticulo as id, nombre as descripcion, codigo, precio_venta as valor from articulo 
        where 
        estado='1' and tipoitem='servicios' order by id desc";
        return ejecutarConsulta($sql);      
    }
}

?>