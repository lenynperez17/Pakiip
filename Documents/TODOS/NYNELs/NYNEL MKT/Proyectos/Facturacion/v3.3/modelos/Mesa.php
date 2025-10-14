<?php 
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

Class Mesa
{
	//Implementamos nuestro constructor
	public function __construct()
	{

	}

	//Implementamos un método para insertar registros
	public function insertar($nromesa)
	{
		$sql="insert into mesa (nromesa)
		values ('$nromesa')";
		return ejecutarConsulta($sql);
	}



	//Implementamos un método para editar registros
	public function editar($idmesa,$nromesa)
	{
		$sql="update mesa set nromesa='$nromesa' where idmesa='$idmesa'";
		return ejecutarConsulta($sql);
	}

	//Implementamos un método para desactivar familias
	public function desactivar($idmesa)
	{
		$sql="update mesa set estado='0' where idmesa='$idmesa'";
		return ejecutarConsulta($sql);
	}

	//Implementamos un método para activar categorías
	public function activar($idmesa)
	{
		$sql="update mesa set estado='1' where idmesa='$idmesa'";
		return ejecutarConsulta($sql);
	}

	//Implementar un método para mostrar los datos de un registro a modificar
	public function mostrar($idmesa)
	{
		$sql="select * from mesa where idmesa='$idmesa'";
		return ejecutarConsultaSimpleFila($sql);
	}

	//Implementar un método para listar los registros
	public function listar()
	{
		$sql="select * from mesa";
		return ejecutarConsulta($sql);		
	}
	//Implementar un método para listar los registros y mostrar en el select
	public function select()
	{
		$sql="select * from mesa where estado=1 and not idmesa='1'  order by idmesa desc ";
		return ejecutarConsulta($sql);		
	}
}

?>