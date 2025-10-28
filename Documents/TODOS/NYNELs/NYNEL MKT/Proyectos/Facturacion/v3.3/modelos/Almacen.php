<?php
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

Class Almacen
{
	//Implementamos nuestro constructor
	public function __construct()
	{

	}

	public function insertaralmacen($nombre, $direc, $idempresa, $telefono = null, $email = null, $idusuario_responsable = null, $tipo_almacen = 'SECUNDARIO', $capacidad_max = null, $notas = null)
	{
		global $conexion;

		$sql = "INSERT INTO almacen (nombre, direccion, idempresa, telefono, email, idusuario_responsable, tipo_almacen, capacidad_max, notas) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando INSERT en almacen: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("ssissisis", $nombre, $direc, $idempresa, $telefono, $email, $idusuario_responsable, $tipo_almacen, $capacidad_max, $notas);

		$resultado = $stmt->execute();
		$stmt->close();

		return $resultado;
	}

	//Implementamos un método para editar registros
	public function editar($idalmacen, $nombre, $direccion, $telefono = null, $email = null, $idusuario_responsable = null, $tipo_almacen = 'SECUNDARIO', $capacidad_max = null, $notas = null)
	{
		global $conexion;

		$sql = "UPDATE almacen SET nombre = ?, direccion = ?, telefono = ?, email = ?, idusuario_responsable = ?, tipo_almacen = ?, capacidad_max = ?, notas = ? WHERE idalmacen = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando UPDATE en almacen: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("sssisisii", $nombre, $direccion, $telefono, $email, $idusuario_responsable, $tipo_almacen, $capacidad_max, $notas, $idalmacen);

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

		$sql = "SELECT
					a.*,
					u.nombre as responsable_nombre,
					COUNT(DISTINCT ar.idarticulo) as total_productos,
					COALESCE(SUM(ar.stock * ar.precio_venta), 0) as valor_inventario
				FROM almacen a
				LEFT JOIN usuario u ON a.idusuario_responsable = u.idusuario
				LEFT JOIN articulo ar ON a.idalmacen = ar.idalmacen AND ar.estado = 1
				GROUP BY a.idalmacen
				ORDER BY a.idalmacen";

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

	// Obtener estadísticas generales de almacenes
	public function obtenerEstadisticas()
	{
		global $conexion;

		$sql = "SELECT
					COUNT(DISTINCT a.idalmacen) as total_almacenes,
					COUNT(DISTINCT CASE WHEN a.estado = 1 THEN a.idalmacen END) as almacenes_activos,
					COUNT(DISTINCT CASE WHEN a.estado = 0 THEN a.idalmacen END) as almacenes_inactivos,
					COUNT(DISTINCT ar.idarticulo) as total_productos,
					COALESCE(SUM(ar.stock * ar.precio_venta), 0) as valor_total_inventario,
					COUNT(DISTINCT CASE WHEN a.tipo_almacen = 'PRINCIPAL' THEN a.idalmacen END) as almacenes_principales,
					COUNT(DISTINCT CASE WHEN a.tipo_almacen = 'SECUNDARIO' THEN a.idalmacen END) as almacenes_secundarios,
					COUNT(DISTINCT CASE WHEN a.tipo_almacen = 'TEMPORAL' THEN a.idalmacen END) as almacenes_temporales
				FROM almacen a
				LEFT JOIN articulo ar ON a.idalmacen = ar.idalmacen AND ar.estado = 1";

		$resultado = $conexion->query($sql);

		return $resultado;
	}

	// Obtener lista de usuarios para select de responsables
	public function obtenerUsuariosResponsables()
	{
		global $conexion;

		$sql = "SELECT idusuario, nombre, login
				FROM usuario
				WHERE condicion = 1
				ORDER BY nombre ASC";

		$resultado = $conexion->query($sql);

		return $resultado;
	}

	// Listar productos por almacén con detalles completos
	public function listarProductosPorAlmacen($idalmacen)
	{
		global $conexion;

		$sql = "SELECT
					a.idarticulo,
					a.codigo,
					a.nombre,
					a.stock,
					a.precio_venta,
					a.costo_compra,
					COALESCE(a.stock * a.precio_venta, 0) as valor_total,
					u.abre as unidad_medida,
					alm.nombre as almacen_nombre,
					alm.direccion as almacen_direccion,
					a.estado
				FROM articulo a
				INNER JOIN umedida u ON a.unidad_medida = u.idunidad
				INNER JOIN almacen alm ON a.idalmacen = alm.idalmacen
				WHERE a.idalmacen = ?
				AND a.estado = 1
				ORDER BY a.nombre ASC";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando listarProductosPorAlmacen: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $idalmacen);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}

	// Obtener resumen de productos por almacén
	public function obtenerResumenAlmacen($idalmacen)
	{
		global $conexion;

		$sql = "SELECT
					COUNT(DISTINCT a.idarticulo) as total_productos,
					COALESCE(SUM(a.stock), 0) as total_unidades,
					COALESCE(SUM(a.stock * a.precio_venta), 0) as valor_total_inventario,
					MIN(a.stock) as stock_minimo,
					MAX(a.stock) as stock_maximo
				FROM articulo a
				WHERE a.idalmacen = ?
				AND a.estado = 1";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando obtenerResumenAlmacen: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $idalmacen);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}
}

?>
