<?php
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

Class Numeracion
{
	//Implementamos nuestro constructor
	public function __construct()
	{

	}

	//Implementamos un método para insertar registros
	public function insertar($tipo_documento, $serie, $numero)
	{
		global $conexion;

		$sql = "INSERT INTO numeracion (tipo_documento, serie, numero) VALUES (?, ?, ?)";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando INSERT en numeracion: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("ssi", $tipo_documento, $serie, $numero);

		$resultado = $stmt->execute();

		// Si la inserción fue exitosa, asignar automáticamente la nueva serie a todos los usuarios activos
		if ($resultado) {
			$idnumeracion_nueva = $conexion->insert_id;

			// Asignar a todos los usuarios activos (condicion = 1)
			$sql_asignar = "INSERT INTO detalle_usuario_numeracion (idusuario, idnumeracion)
							SELECT idusuario, ? FROM usuario WHERE condicion = '1'";

			$stmt_asignar = $conexion->prepare($sql_asignar);
			if ($stmt_asignar) {
				$stmt_asignar->bind_param("i", $idnumeracion_nueva);
				$stmt_asignar->execute();
				$stmt_asignar->close();
			}
		}

		$stmt->close();

		return $resultado;
	}

	//Implementamos un método para editar registros
	public function editar($idnumeracion, $tipo_documento, $serie, $numero)
	{
		global $conexion;

		$sql = "UPDATE numeracion SET tipo_documento = ?, serie = ?, numero = ? WHERE idnumeracion = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando UPDATE en numeracion: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("ssii", $tipo_documento, $serie, $numero, $idnumeracion);

		$resultado = $stmt->execute();
		$stmt->close();

		return $resultado;
	}

	//Implementamos un método para desactivar familias
	public function desactivar($idnumeracion)
	{
		global $conexion;

		$sql = "UPDATE numeracion SET estado = '0' WHERE idnumeracion = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando desactivar en numeracion: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $idnumeracion);

		$resultado = $stmt->execute();
		$stmt->close();

		return $resultado;
	}

	//Implementamos un método para activar categorías
	public function activar($idnumeracion)
	{
		global $conexion;

		$sql = "UPDATE numeracion SET estado = '1' WHERE idnumeracion = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando activar en numeracion: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $idnumeracion);

		$resultado = $stmt->execute();
		$stmt->close();

		return $resultado;
	}

	//Implementamos un método para ELIMINAR FÍSICAMENTE registros
	public function eliminar($idnumeracion)
	{
		global $conexion;

		$sql = "DELETE FROM numeracion WHERE idnumeracion = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando DELETE en numeracion: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $idnumeracion);

		$resultado = $stmt->execute();
		$stmt->close();

		return $resultado;
	}

	//Implementar un método para mostrar los datos de un registro a modificar
	public function mostrar($idnumeracion)
	{
		global $conexion;

		$sql = "SELECT * FROM numeracion WHERE idnumeracion = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando SELECT en numeracion: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $idnumeracion);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}

	//Implementar un método para listar los registros
	public function listar()
	{
		global $conexion;

		$sql = "SELECT * FROM numeracion n
				INNER JOIN catalogo1 ct1 ON n.tipo_documento = ct1.codigo
				ORDER BY ct1.descripcion";

		$resultado = $conexion->query($sql);

		return $resultado;
	}

	//Implementar un método para listar los registros y mostrar en el select
	public function select()
	{
		global $conexion;

		$sql = "SELECT * FROM numeracion WHERE estado = '1'";

		$resultado = $conexion->query($sql);

		return $resultado;
	}

	//Llenar combo de series de Orden
	public function llenarSerieOrden()
	{
		global $conexion;

		$sql = "SELECT idnumeracion, serie FROM numeracion WHERE tipo_documento = '99'";

		$resultado = $conexion->query($sql);

		return $resultado;
	}

	//Llenar combo de series de guia remision
	public function llenarSerieGuia()
	{
		global $conexion;

		$sql = "SELECT idnumeracion, serie FROM numeracion
				WHERE tipo_documento = '09' OR tipo_documento = '56'";

		$resultado = $conexion->query($sql);

		return $resultado;
	}

	//Llenar combo de series de nota de credito
	public function llenarSerieNcredito($idusuario)
	{
		global $conexion;

		$sql = "SELECT n.idnumeracion, n.serie
				FROM numeracion n
				INNER JOIN detalle_usuario_numeracion dn ON n.idnumeracion = dn.idnumeracion
				INNER JOIN usuario u ON dn.idusuario = u.idusuario
				WHERE n.tipo_documento = '07' AND dn.idusuario = ?
				GROUP BY n.serie";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando llenarSerieNcredito: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $idusuario);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}

	public function llenarSerieNdebito($idusuario)
	{
		global $conexion;

		$sql = "SELECT n.idnumeracion, n.serie
				FROM numeracion n
				INNER JOIN detalle_usuario_numeracion dn ON n.idnumeracion = dn.idnumeracion
				INNER JOIN usuario u ON dn.idusuario = u.idusuario
				WHERE n.tipo_documento = '08' AND dn.idusuario = ?
				GROUP BY n.serie";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando llenarSerieNdebito: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $idusuario);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}

	//Llenar combo de series de Boleta
	public function llenarSerieBoleta($idusuario)
	{
		global $conexion;

		$sql = "SELECT n.idnumeracion, n.serie
				FROM numeracion n
				INNER JOIN detalle_usuario_numeracion dn ON n.idnumeracion = dn.idnumeracion
				INNER JOIN usuario u ON dn.idusuario = u.idusuario
				WHERE n.tipo_documento = '03' AND dn.idusuario = ?
				GROUP BY n.serie";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando llenarSerieBoleta: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $idusuario);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}

	//Llenar combo de series de Nota de Pedido
	public function llenarSerieNpedido($idusuario)
	{
		global $conexion;

		$sql = "SELECT n.idnumeracion, n.serie
				FROM numeracion n
				INNER JOIN detalle_usuario_numeracion dn ON n.idnumeracion = dn.idnumeracion
				INNER JOIN usuario u ON dn.idusuario = u.idusuario
				WHERE n.tipo_documento = '50' AND dn.idusuario = ?
				GROUP BY n.serie";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando llenarSerieNpedido: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $idusuario);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}

	//Llenar combo de series de Factura
	public function llenarSerieFactura($idusuario)
	{
		global $conexion;

		$sql = "SELECT n.idnumeracion, n.serie
				FROM numeracion n
				INNER JOIN detalle_usuario_numeracion dn ON n.idnumeracion = dn.idnumeracion
				INNER JOIN usuario u ON dn.idusuario = u.idusuario
				WHERE n.tipo_documento = '01' AND dn.idusuario = ?
				GROUP BY n.serie";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando llenarSerieFactura: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $idusuario);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}

	//Llenar combo de series de cotizacion
	public function llenarSeriecotizacion($idusuario)
	{
		global $conexion;

		$sql = "SELECT n.idnumeracion, n.serie
				FROM numeracion n
				INNER JOIN detalle_usuario_numeracion dn ON n.idnumeracion = dn.idnumeracion
				INNER JOIN usuario u ON dn.idusuario = u.idusuario
				WHERE n.tipo_documento = '20' AND dn.idusuario = ?
				GROUP BY n.serie";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando llenarSeriecotizacion: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $idusuario);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}

	//Llenar combo de series de docu cobranza
	public function llenarSeriedoccobranza($idusuario)
	{
		global $conexion;

		$sql = "SELECT n.idnumeracion, n.serie
				FROM numeracion n
				INNER JOIN detalle_usuario_numeracion dn ON n.idnumeracion = dn.idnumeracion
				INNER JOIN usuario u ON dn.idusuario = u.idusuario
				WHERE n.tipo_documento = '30' AND dn.idusuario = ?
				GROUP BY n.serie";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando llenarSeriedoccobranza: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $idusuario);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}

	//Función para incrementar numero de COTIZACION
	public function llenarNumerocotizacion($serie)
	{
		global $conexion;

		$sql = "SELECT (n.numero+1) AS Nnumero
				FROM numeracion n
				INNER JOIN detalle_usuario_numeracion dn ON n.idnumeracion = dn.idnumeracion
				INNER JOIN usuario u ON dn.idusuario = u.idusuario
				WHERE n.tipo_documento = '20' AND n.idnumeracion = ?
				LIMIT 1";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando llenarNumerocotizacion: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $serie);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}

	//Función para incrementar numero de doc.cobranza
	public function llenarNumerodoccobranza($serie)
	{
		global $conexion;

		$sql = "SELECT (n.numero+1) AS Nnumero
				FROM numeracion n
				INNER JOIN detalle_usuario_numeracion dn ON n.idnumeracion = dn.idnumeracion
				INNER JOIN usuario u ON dn.idusuario = u.idusuario
				WHERE n.tipo_documento = '30' AND n.idnumeracion = ?
				LIMIT 1";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando llenarNumerodoccobranza: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $serie);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}

	//Función para incrementar numero de factura
	public function llenarNumeroFactura($serie)
	{
		global $conexion;

		$sql = "SELECT (n.numero+1) AS Nnumero
				FROM numeracion n
				INNER JOIN detalle_usuario_numeracion dn ON n.idnumeracion = dn.idnumeracion
				INNER JOIN usuario u ON dn.idusuario = u.idusuario
				WHERE n.tipo_documento = '01' AND n.idnumeracion = ?
				LIMIT 1";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando llenarNumeroFactura: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $serie);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}

	//Función para incrementar numero de factura servicio
	public function llenarNumeroFacturaServicio($serie)
	{
		global $conexion;

		$sql = "SELECT (n.numero+1) AS Nnumero
				FROM servicios_inmuebles n
				INNER JOIN detalle_usuario_servicios_inmuebles dn ON n.idservicios_inmuebles = dn.idservicios_inmuebles
				INNER JOIN usuario u ON dn.idusuario = u.idusuario
				WHERE n.tipo_documento = '01' AND n.idservicios_inmuebles = ?
				LIMIT 1";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando llenarNumeroFacturaServicio: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $serie);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}

	//Función para incrementar numero de boleta
	public function llenarNumeroBoleta($serie)
	{
		global $conexion;

		$sql = "SELECT (n.numero+1) AS Nnumero
				FROM numeracion n
				INNER JOIN detalle_usuario_numeracion dn ON n.idnumeracion = dn.idnumeracion
				INNER JOIN usuario u ON dn.idusuario = u.idusuario
				WHERE n.tipo_documento = '03' AND n.idnumeracion = ?
				LIMIT 1";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando llenarNumeroBoleta: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $serie);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}

	//Función para incrementar numero de nota de pedido
	public function llenarNumeroNpedido($serie)
	{
		global $conexion;

		$sql = "SELECT (n.numero+1) AS Nnumero
				FROM numeracion n
				INNER JOIN detalle_usuario_numeracion dn ON n.idnumeracion = dn.idnumeracion
				INNER JOIN usuario u ON dn.idusuario = u.idusuario
				WHERE n.tipo_documento = '50' AND n.idnumeracion = ?
				LIMIT 1";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando llenarNumeroNpedido: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $serie);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}

	//Función para incrementar numero de ORDEN DE SERVICIO
	public function llenarNumeroOrden($idnumeracion)
	{
		global $conexion;

		$sql = "SELECT (numero+1) AS Nnumero
				FROM numeracion
				WHERE tipo_documento = '99' AND idnumeracion = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando llenarNumeroOrden: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $idnumeracion);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}

	//Función para incrementar numero de guia
	public function llenarNumeroGuia($serie)
	{
		global $conexion;

		$sql = "SELECT (numero+1) AS Nnumero
				FROM numeracion
				WHERE (tipo_documento = '09' OR tipo_documento = '56') AND idnumeracion = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando llenarNumeroGuia: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $serie);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}

	//Función para incrementar numero de nota de credito
	public function llenarNumeroNcredito($serie)
	{
		global $conexion;

		$sql = "SELECT (n.numero+1) AS Nnumero
				FROM numeracion n
				INNER JOIN detalle_usuario_numeracion dn ON n.idnumeracion = dn.idnumeracion
				INNER JOIN usuario u ON dn.idusuario = u.idusuario
				WHERE n.tipo_documento = '07' AND n.idnumeracion = ?
				LIMIT 1";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando llenarNumeroNcredito: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $serie);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}

	//Función para incrementar numero de nota de debito
	public function llenarNumeroNdedito($serie)
	{
		global $conexion;

		$sql = "SELECT (n.numero+1) AS Nnumero
				FROM numeracion n
				INNER JOIN detalle_usuario_numeracion dn ON n.idnumeracion = dn.idnumeracion
				INNER JOIN usuario u ON dn.idusuario = u.idusuario
				WHERE n.tipo_documento = '08' AND n.idnumeracion = ?
				LIMIT 1";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando llenarNumeroNdedito: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $serie);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}

	public function updateNumeracion($numero, $idnumeracion)
	{
		global $conexion;

		$sql = "UPDATE numeracion SET numero = ? WHERE idnumeracion = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando UPDATE numeracion: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("ii", $numero, $idnumeracion);

		$resultado = $stmt->execute();
		$stmt->close();

		return $resultado;
	}

	public function listarSeries()
	{
		global $conexion;

		$sql = "SELECT * FROM numeracion WHERE estado = '1'";

		$resultado = $conexion->query($sql);

		return $resultado;
	}

	public function listarSeriesNuevo()
	{
		global $conexion;

		$sql = "SELECT * FROM numeracion";

		$resultado = $conexion->query($sql);

		return $resultado;
	}
}

?>
