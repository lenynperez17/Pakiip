<?php
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

Class Almacen
{
	//Implementamos nuestro constructor
	public function __construct()
	{

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

	//Implementamos un método para editar registros
	public function editar($idalmacen, $nombre, $direccion)
	{
		global $conexion;

		$sql = "UPDATE almacen SET nombre = ?, direccion = ? WHERE idalmacen = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando UPDATE en almacen: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("ssi", $nombre, $direccion, $idalmacen);

		$resultado = $stmt->execute();
		$stmt->close();

		return $resultado;
	}

	//Implementamos un método para desactivar almacens
	public function desactivar($idalmacen)
	{
		global $conexion;

		$sql = "UPDATE almacen SET estado = '0' WHERE idalmacen = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando desactivar en almacen: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $idalmacen);

		$resultado = $stmt->execute();
		$stmt->close();

		return $resultado;
	}

	//Implementamos un método para activar categorías
	public function activar($idalmacen)
	{
		global $conexion;

		$sql = "UPDATE almacen SET estado = '1' WHERE idalmacen = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando activar en almacen: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $idalmacen);

		$resultado = $stmt->execute();
		$stmt->close();

		return $resultado;
	}

	//validar duplicado
	public function validarAlmacen($nombre)
	{
		global $conexion;

		$sql = "SELECT * FROM almacen WHERE nombre = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando validación en almacen: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("s", $nombre);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}

	//Implementar un método para mostrar los datos de un registro a modificar
	public function mostrar($idalmacen)
	{
		global $conexion;

		$sql = "SELECT * FROM almacen WHERE idalmacen = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando SELECT en almacen: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $idalmacen);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}

	//Implementar un método para listar los registros
	public function listar()
	{
		global $conexion;

		$sql = "SELECT * FROM almacen ORDER BY idalmacen";

		$resultado = $conexion->query($sql);

		return $resultado;
	}

	//Implementar un método para listar los registros y mostrar en el select
	public function select($idempresa)
	{
		global $conexion;

		$sql = "SELECT * FROM almacen a
				INNER JOIN empresa e ON a.idempresa = e.idempresa
				WHERE e.idempresa = ?
				ORDER BY idalmacen DESC";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando SELECT en almacen: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $idempresa);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}

	public function selectunidad()
	{
		global $conexion;

		$sql = "SELECT *
				FROM umedida
				ORDER BY
					CASE WHEN idunidad = 58 THEN 0 ELSE 1 END,
					idunidad DESC";

		$resultado = $conexion->query($sql);

		return $resultado;
	}

	public function almacenlista()
	{
		global $conexion;

		$sql = "SELECT * FROM almacen WHERE NOT idalmacen = '1' ORDER BY idalmacen";

		$resultado = $conexion->query($sql);

		return $resultado;
	}
}

?>
