<?php
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

Class Catalogo5
{
	//Implementamos nuestro constructor
	public function __construct()
	{

	}

	//Implementamos un método para insertar registros
	public function insertar($codigo, $descripcion, $unece5153)
	{
		global $conexion;

		$sql = "INSERT INTO catalogo5 (codigo, descripcion, unece5153)
		        VALUES (?, ?, ?)";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando INSERT en catalogo5: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("sss", $codigo, $descripcion, $unece5153);

		$resultado = $stmt->execute();
		$stmt->close();

		return $resultado;
	}

	//Implementamos un método para editar registros
	public function editar($id, $codigo, $descripcion, $unece5153)
	{
		global $conexion;

		$sql = "UPDATE catalogo5
		        SET codigo = ?, descripcion = ?, unece5153 = ?
		        WHERE id = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando UPDATE en catalogo5: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("sssi", $codigo, $descripcion, $unece5153, $id);

		$resultado = $stmt->execute();
		$stmt->close();

		return $resultado;
	}

	//Implementamos un método para desactivar familias
	public function desactivar($id)
	{
		global $conexion;

		$sql = "UPDATE catalogo5 SET estado = '0' WHERE id = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando desactivar en catalogo5: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $id);

		$resultado = $stmt->execute();
		$stmt->close();

		return $resultado;
	}

	//Implementamos un método para activar categorías
	public function activar($id)
	{
		global $conexion;

		$sql = "UPDATE catalogo5 SET estado = '1' WHERE id = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando activar en catalogo5: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $id);

		$resultado = $stmt->execute();
		$stmt->close();

		return $resultado;
	}

	//Implementar un método para mostrar los datos de un registro a modificar
	public function mostrar($id)
	{
		global $conexion;

		$sql = "SELECT * FROM catalogo5 WHERE id = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando SELECT en catalogo5: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $id);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}

	//Implementar un método para listar los registros
	public function listar()
	{
		global $conexion;

		$sql = "SELECT * FROM catalogo5";

		$resultado = $conexion->query($sql);

		return $resultado;
	}

}

?>
