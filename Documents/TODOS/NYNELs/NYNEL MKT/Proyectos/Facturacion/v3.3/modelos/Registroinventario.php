<?php 
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

Class Registroinv
{
	//Implementamos nuestro constructor
	public function __construct()
	{

	}

	//Implementamos un método para insertar registros
	public function insertar($ano, $codigo, $denominacion, $costoinicial, $saldoinicial, $valorinicial, $compras, $ventas, $saldofinal, $costo, $valorfinal)
	{
		$sql="insert into reginventariosanos (ano, codigo, denominacion, costoinicial, saldoinicial, valorinicial, compras, ventas, saldofinal, costo, valorfinal) 
		values
		 ('$ano', '$codigo', '$denominacion', '$costoinicial', '$saldoinicial', '$valorinicial', '$compras', '$ventas',  '$saldofinal', '$costo', '$valorfinal')";
		return ejecutarConsulta($sql);
	}

	//Implementamos un método para editar registros
	public function editar($idregistro, $ano, $codigo, $denominacion, $costoinicial, $saldoinicial, $valorinicial, $compras, $ventas, $saldofinal, $costo, $valorfinal)
	{
		$sql="update reginventariosanos set 
		codigo='$codigo', 
		denominacion='$denominacion', 
		costoinicial='$costoinicial', 
		saldoinicial='$saldoinicial', 
		valorinicial='$valorinicial', 
		compras='$compras',
		ventas='$ventas', 
		saldofinal='$saldofinal',
		costo='$costo', 
		valorfinal='$valorfinal', 
		ano='$ano'  
		where idregistro='$idregistro'";
		return ejecutarConsulta($sql);
	}



	//Implementar un método para mostrar los datos de un registro a modificar
	public function mostrar($idarticulo)
	{
		$sql="select 
		ri.idregistro,
		ri.codigo, 
		ri.denominacion, 
		ri.costoinicial, 
		ri.saldoinicial, 
		ri.valorinicial, 
		ri.compras, 
		ri.ventas, 
		ri.saldofinal, 
		ri.costo, 
		ri.valorfinal, 
		ri.ano 
		from 
		reginventariosanos ri  where ri.idregistro='$idarticulo'";
		return ejecutarConsultaSimpleFila($sql);
	}

	//Implementar un método para listar los registros
	public function listar()
	{
		$sql="select *  from reginventariosanos order by codigo, ano";
		return ejecutarConsulta($sql);		
	}


	public function eliminar($idregistro)
	{
		$sql="delete from reginventariosanos where idregistro='$idregistro'";
		return ejecutarConsulta($sql);		
	}



}

?>