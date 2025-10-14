<?php 
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

Class Ventadiaria
{
	//Implementamos nuestro constructor
	public function __construct()
	{

	}

	//Implementamos un método para insertar registros
	public function insertar($fecharegistroingreso, $tipo, $total)
	{
		$sql="insert into ventadiaria ( fecharegistroingreso, tipo, total)
		values ('$fecharegistroingreso', '$tipo' , '$total')";
		return ejecutarConsulta($sql);
	}

	//Implementamos un método para editar registros
	public function editar($idventa, $descripcion, $total)
	{
		$sql="";
		return ejecutarConsulta($sql);
	}


	//Implementar un método para mostrar los datos de un registro a modificar
	public function mostrar($idventa)
	{
		$sql="select * from ventadiaria where idventa='$idventa'";
		return ejecutarConsultaSimpleFila($sql);
	}

	//Implementar un método para listar los registros
	public function listar()
	{
		$sql="select * from ventadiaria where
        date(fecharegistroingreso)=current_date";
		return ejecutarConsulta($sql);		
	}




		public function eliminar($idventa)
	{
		$sql="delete from ventadiaria where idventa='$idventa'";
		return ejecutarConsulta($sql);
	}

	public function datosemp()
    {

    $sql="select * from empresa";
    return ejecutarConsulta($sql);      
    }

    public function ingresoagrupadoxtipo()
	{
		$sql="select tipo, sum(total) as totali from ventadiaria where
        date(fecharegistroingreso)=current_date and not tipo in('efectivot','tarjeta') group by tipo order by tipo ";
		return ejecutarConsulta($sql);
	}

	 public function ingresoagrupadoxtipo2()
	{
		$sql="select tipo, sum(total) as totali from ventadiaria where
        date(fecharegistroingreso)=current_date and tipo in('efectivot','tarjeta') group by tipo order by tipo ";
		return ejecutarConsulta($sql);
	}


	public function ingresoagrupadototal()
	{
		$sql="select sum(total) as totalgene from ventadiaria where
        date(fecharegistroingreso)=current_date and not tipo in('efectivot','tarjeta')";
		return ejecutarConsulta($sql);
	}



	
}

?>