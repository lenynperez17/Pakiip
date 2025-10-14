<?php
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

Class Familia
{
	//Implementamos nuestro constructor
	public function __construct()
	{

	}

	//Implementamos un método para insertar registros
	public function insertarCategoria($nombre)
	{
		global $conexion;

		$sql = "INSERT INTO familia (descripcion) VALUES (?)";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando INSERT en familia: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("s", $nombre);

		$resultado = $stmt->execute();
		$stmt->close();

		return $resultado;
	}

	public function insertaralmacen($nombre, $direc, $idempresa)
	{
		global $conexion;

		$sql = "INSERT INTO almacen (nombre, direccion, idempresa) VALUES (?, ?, ?)";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando INSERT en almacen: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("ssi", $nombre, $direc, $idempresa);

		$resultado = $stmt->execute();
		$stmt->close();

		return $resultado;
	}

	public function insertaraunidad($nombre, $abre, $equivalencia)
	{
		global $conexion;

		$sql = "INSERT INTO umedida (nombreum, abre, equivalencia) VALUES (?, ?, ?)";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando INSERT en umedida: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("sss", $nombre, $abre, $equivalencia);

		$resultado = $stmt->execute();
		$stmt->close();

		return $resultado;
	}

	//Implementamos un método para editar registros
	public function editar($idfamilia, $nombre)
	{
		global $conexion;

		$sql = "UPDATE familia SET descripcion = ? WHERE idfamilia = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando UPDATE en familia: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("si", $nombre, $idfamilia);

		$resultado = $stmt->execute();
		$stmt->close();

		return $resultado;
	}

	//Implementamos un método para desactivar familias
	public function desactivar($idfamilia)
	{
		global $conexion;

		$sql = "UPDATE familia SET estado = '0' WHERE idfamilia = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando desactivar en familia: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $idfamilia);

		$resultado = $stmt->execute();
		$stmt->close();

		return $resultado;
	}

	//Implementamos un método para activar categorías
	public function activar($idfamilia)
	{
		global $conexion;

		$sql = "UPDATE familia SET estado = '1' WHERE idfamilia = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando activar en familia: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $idfamilia);

		$resultado = $stmt->execute();
		$stmt->close();

		return $resultado;
	}

	//Implementar un método para mostrar los datos de un registro a modificar
	public function mostrar($idfamilia)
	{
		global $conexion;

		$sql = "SELECT * FROM familia WHERE idfamilia = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando SELECT en familia: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $idfamilia);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}

	//validar duplicado
	public function validarCategoria($nombre)
	{
		global $conexion;

		$sql = "SELECT * FROM familia WHERE descripcion = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando validación en familia: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("s", $nombre);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}

	//Implementar un método para listar los registros
	public function listar()
	{
		global $conexion;

		$sql = "SELECT * FROM familia";

		$resultado = $conexion->query($sql);

		return $resultado;
	}

	//Implementar un método para listar los registros y mostrar en el select
	public function select()
	{
		global $conexion;

		$sql = "SELECT * FROM familia WHERE estado = 1 AND NOT idfamilia = '0' ORDER BY idfamilia DESC";

		$resultado = $conexion->query($sql);

		return $resultado;
	}
}

?>
