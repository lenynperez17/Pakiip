<?php
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

Class Catalogo7
{
	//Implementamos nuestro constructor
	public function __construct()
	{

	}

	//Implementamos un método para insertar registros
	// CORREGIDO: Eliminado parámetro $abrev (la tabla catalogo7 no tiene columna abrev)
	public function insertar($codigo, $descripcion)
	{
		global $conexion;

		$sql = "INSERT INTO catalogo7 (id, codigo, descripcion)
		        VALUES (null, ?, ?)";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando INSERT en catalogo7: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("ss", $codigo, $descripcion);

		$resultado = $stmt->execute();
		$stmt->close();

		return $resultado;
	}

	//Implementamos un método para editar registros
	public function editar($id, $codigo, $descripcion)
	{
		global $conexion;

		$sql = "UPDATE catalogo7
		        SET codigo = ?, descripcion = ?
		        WHERE id = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando UPDATE en catalogo7: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("ssi", $codigo, $descripcion, $id);

		$resultado = $stmt->execute();
		$stmt->close();

		return $resultado;
	}

	//Implementamos un método para desactivar familias
	public function desactivar($id)
	{
		global $conexion;

		$sql = "UPDATE catalogo7 SET estado = '0' WHERE id = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando desactivar en catalogo7: " . $conexion->error);
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

		$sql = "UPDATE catalogo7 SET estado = '1' WHERE id = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando activar en catalogo7: " . $conexion->error);
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

		$sql = "SELECT * FROM catalogo7 WHERE id = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando SELECT en catalogo7: " . $conexion->error);
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

		$sql = "SELECT * FROM catalogo7";

		$resultado = $conexion->query($sql);

		return $resultado;
	}

	public function listar2()
	{
		global $conexion;

		$sql = "SELECT id, codigo, descripcion, estado FROM catalogo7";

		$resultado = $conexion->query($sql);

		return $resultado;
	}

}

?>
