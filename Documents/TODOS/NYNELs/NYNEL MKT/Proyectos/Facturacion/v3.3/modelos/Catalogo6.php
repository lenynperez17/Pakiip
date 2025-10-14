<?php
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

Class Catalogo6
{
	//Implementamos nuestro constructor
	public function __construct()
	{

	}

	//Implementamos un método para insertar registros
	public function insertar($codigo, $descripcion, $abrev)
	{
		global $conexion;

		$sql = "INSERT INTO catalogo6 (id, codigo, descripcion, abrev)
		        VALUES (null, ?, ?, ?)";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando INSERT en catalogo6: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("sss", $codigo, $descripcion, $abrev);

		$resultado = $stmt->execute();
		$stmt->close();

		return $resultado;
	}

	//Implementamos un método para editar registros
	public function editar($id, $codigo, $descripcion, $abrev)
	{
		global $conexion;

		$sql = "UPDATE catalogo6
		        SET codigo = ?, descripcion = ?, abrev = ?
		        WHERE id = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando UPDATE en catalogo6: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("sssi", $codigo, $descripcion, $abrev, $id);

		$resultado = $stmt->execute();
		$stmt->close();

		return $resultado;
	}

	//Implementamos un método para desactivar familias
	public function desactivar($id)
	{
		global $conexion;

		$sql = "UPDATE catalogo6 SET estado = '0' WHERE id = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando desactivar en catalogo6: " . $conexion->error);
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

		$sql = "UPDATE catalogo6 SET estado = '1' WHERE id = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando activar en catalogo6: " . $conexion->error);
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

		$sql = "SELECT * FROM catalogo6 WHERE id = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando SELECT en catalogo6: " . $conexion->error);
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

		$sql = "SELECT * FROM catalogo6";

		$resultado = $conexion->query($sql);

		return $resultado;
	}

	public function listar2()
	{
		global $conexion;

		$sql = "SELECT id, codigo, descripcion, estado FROM catalogo6";

		$resultado = $conexion->query($sql);

		return $resultado;
	}

}

?>
