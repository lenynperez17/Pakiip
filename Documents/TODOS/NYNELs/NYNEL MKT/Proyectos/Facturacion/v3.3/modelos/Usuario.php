<?php
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

class Usuario
{
	//Implementamos nuestro constructor
	public function __construct()
	{

	}

	/**
	 * Implementamos un método para insertar registros de usuario
	 * Refactorizado con prepared statements para prevenir SQL Injection
	 */
	public function insertar($nombre, $apellidos, $tipo_documento, $num_documento, $direccion, $telefono, $email, $cargo, $login, $clave, $imagen, $permisos, $series, $empresa)
	{
		// INSERT principal de usuario con prepared statement
		$sql = "INSERT INTO usuario (nombre, apellidos, tipo_documento, num_documento, direccion, telefono, email, cargo, login, clave, imagen)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

		$idusuarionew = ejecutarConsultaPreparada_retornarID(
			$sql,
			"sssssssssss",
			[$nombre, $apellidos, $tipo_documento, $num_documento, $direccion, $telefono, $email, $cargo, $login, $clave, $imagen]
		);

		// Verificar si el INSERT principal fue exitoso
		if (!$idusuarionew) {
			error_log("Error al insertar usuario: " . $login);
			return false;
		}

		// Insertar en vendedorsitio SOLO si cargo es igual a 1 (Ventas)
		if ($cargo == 1) {
			$sql_vendedor = "INSERT INTO vendedorsitio (nombre, estado, idempresa) VALUES (?, ?, ?)";
			ejecutarConsultaPreparada($sql_vendedor, "sii", [$nombre, 1, 1]);
		}

		$sw = true;

		// Insertar permisos con prepared statements
		foreach ($permisos as $permiso) {
			$sql_detalle = "INSERT INTO usuario_permiso (idusuario, idpermiso) VALUES (?, ?)";
			$result = ejecutarConsultaPreparada($sql_detalle, "ii", [$idusuarionew, $permiso]);
			if (!$result) {
				$sw = false;
				error_log("Error al insertar permiso $permiso para usuario $idusuarionew");
			}
		}

		// Insertar series de numeración con prepared statements
		foreach ($series as $serie) {
			$sql_detalle_series = "INSERT INTO detalle_usuario_numeracion (idusuario, idnumeracion) VALUES (?, ?)";
			$result = ejecutarConsultaPreparada($sql_detalle_series, "ii", [$idusuarionew, $serie]);
			if (!$result) {
				$sw = false;
				error_log("Error al insertar serie $serie para usuario $idusuarionew");
			}
		}

		// Insertar empresas asignadas con prepared statements
		foreach ($empresa as $emp) {
			$sql_usuario_empresa = "INSERT INTO usuario_empresa (idusuario, idempresa) VALUES (?, ?)";
			$result = ejecutarConsultaPreparada($sql_usuario_empresa, "ii", [$idusuarionew, $emp]);
			if (!$result) {
				$sw = false;
				error_log("Error al insertar empresa $emp para usuario $idusuarionew");
			}
		}

		return $sw;
	}




	/**
	 * Implementamos un método para editar registros de usuario
	 * Refactorizado con prepared statements para prevenir SQL Injection
	 */
	public function editar($idusuario, $nombre, $apellidos, $tipo_documento, $num_documento, $direccion, $telefono, $email, $cargo, $login, $clave, $imagen, $permisos, $series, $empresa)
	{
		// UPDATE principal de usuario con prepared statement
		$sql = "UPDATE usuario SET
				nombre=?, apellidos=?, tipo_documento=?, num_documento=?, direccion=?,
				telefono=?, email=?, cargo=?, login=?, clave=?, imagen=?
				WHERE idusuario=?";

		$result = ejecutarConsultaPreparada(
			$sql,
			"sssssssssssi",
			[$nombre, $apellidos, $tipo_documento, $num_documento, $direccion, $telefono, $email, $cargo, $login, $clave, $imagen, $idusuario]
		);

		if (!$result) {
			error_log("Error al actualizar usuario: $idusuario");
			return false;
		}

		// Eliminar todos los permisos asignados para volverlos a registrar
		$sqldel = "DELETE FROM usuario_permiso WHERE idusuario=?";
		ejecutarConsultaPreparada($sqldel, "i", [$idusuario]);

		// Eliminar todas las series asignadas
		$sqldelSeries = "DELETE FROM detalle_usuario_numeracion WHERE idusuario=?";
		ejecutarConsultaPreparada($sqldelSeries, "i", [$idusuario]);

		// Eliminar todas las empresas asignadas
		$sqldelEmpresa = "DELETE FROM usuario_empresa WHERE idusuario=?";
		ejecutarConsultaPreparada($sqldelEmpresa, "i", [$idusuario]);

		$sw = true;

		// Insertar nuevos permisos con prepared statements
		foreach ($permisos as $permiso) {
			$sql_detalle = "INSERT INTO usuario_permiso (idusuario, idpermiso) VALUES (?, ?)";
			$result = ejecutarConsultaPreparada($sql_detalle, "ii", [$idusuario, $permiso]);
			if (!$result) {
				$sw = false;
				error_log("Error al insertar permiso $permiso para usuario $idusuario");
			}
		}

		// Insertar nuevas series con prepared statements
		foreach ($series as $serie) {
			$sql_detalleSeries = "INSERT INTO detalle_usuario_numeracion (idusuario, idnumeracion) VALUES (?, ?)";
			$result = ejecutarConsultaPreparada($sql_detalleSeries, "ii", [$idusuario, $serie]);
			if (!$result) {
				$sw = false;
				error_log("Error al insertar serie $serie para usuario $idusuario");
			}
		}

		// Insertar nuevas empresas con prepared statements
		foreach ($empresa as $emp) {
			$sql_Usuario_emresa = "INSERT INTO usuario_empresa (idusuario, idempresa) VALUES (?, ?)";
			$result = ejecutarConsultaPreparada($sql_Usuario_emresa, "ii", [$idusuario, $emp]);
			if (!$result) {
				$sw = false;
				error_log("Error al insertar empresa $emp para usuario $idusuario");
			}
		}

		return $sw;
	}

	/**
	 * Método para desactivar usuario
	 * Refactorizado con prepared statement
	 */
	public function desactivar($idusuario)
	{
		$sql = "UPDATE usuario SET condicion='0' WHERE idusuario=?";
		return ejecutarConsultaPreparada($sql, "i", [$idusuario]);
	}

	/**
	 * Método para activar usuario
	 * Refactorizado con prepared statement
	 */
	public function activar($idusuario)
	{
		$sql = "UPDATE usuario SET condicion='1' WHERE idusuario=?";
		return ejecutarConsultaPreparada($sql, "i", [$idusuario]);
	}

	/**
	 * Método para mostrar los datos de un registro a modificar
	 * Refactorizado con prepared statement
	 */
	public function mostrar($idusuario)
	{
		$sql = "SELECT * FROM usuario WHERE idusuario=?";
		return ejecutarConsultaPreparadaSimpleFila($sql, "i", [$idusuario]);
	}

	//Implementar un método para listar los registros
	public function listar()
	{
		$sql = "select * from usuario";
		return ejecutarConsulta($sql);
	}
	//Implementar un método para listar los registros y mostrar en el select
	public function select()
	{
		$sql = "select * from usuario where condicion=1";
		return ejecutarConsulta($sql);
	}

	/**
	 * Método para listar los permisos marcados de un usuario
	 * Refactorizado con prepared statement
	 */
	public function listarmarcados($idusuario)
	{
		$sql = "SELECT * FROM usuario_permiso WHERE idusuario=?";
		return ejecutarConsultaPreparada($sql, "i", [$idusuario]);
	}

	/**
	 * Método para listar las empresas asignadas a un usuario
	 * Refactorizado con prepared statement
	 */
	public function listarmarcadosEmpresa($idusuario)
	{
		$sql = "SELECT * FROM usuario_empresa WHERE idusuario=?";
		return ejecutarConsultaPreparada($sql, "i", [$idusuario]);
	}

	/**
	 * Método para listar todas las relaciones usuario-empresa
	 * No requiere prepared statement (sin parámetros)
	 */
	public function listarmarcadosEmpresaTodos()
	{
		$sql = "SELECT * FROM usuario_empresa";
		return ejecutarConsulta($sql);
	}

	/**
	 * Método para listar las series de numeración asignadas a un usuario
	 * Refactorizado con prepared statement
	 */
	public function listarmarcadosNumeracion($idusuario)
	{
		$sql = "SELECT * FROM detalle_usuario_numeracion WHERE idusuario=?";
		return ejecutarConsultaPreparada($sql, "i", [$idusuario]);
	}

	/**
	 * Funcion para verificar el acceso al sistema
	 * NOTA: La verificación de contraseña se hace en usuario.php con password_verify()
	 * @param string $login Login del usuario
	 * @return mysqli_result Resultado con los datos del usuario
	 */
	public function verificar($login)
	{
		$sql = "SELECT
			u.idusuario,
			u.nombre,
			u.tipo_documento,
			u.num_documento,
			u.telefono,
			u.email,
			u.cargo,
			u.imagen,
			u.login,
			u.clave,
			e.nombre_razon_social,
			e.idempresa,
			co.igv,
			e.nombre_comercial,
			e.numero_ruc,
			e.domicilio_fiscal
		FROM
			usuario u
			INNER JOIN usuario_empresa ue ON u.idusuario=ue.idusuario
			INNER JOIN empresa e ON ue.idempresa=e.idempresa
			INNER JOIN configuraciones co ON e.idempresa=co.idempresa
		WHERE u.login=? AND u.condicion='1'";

		return ejecutarConsultaPreparada($sql, "s", [$login]);
	}


	/**
	 * Método para activar/desactivar el temporizador
	 * Refactorizado con prepared statement
	 */
	public function onoffTempo($st)
	{
		$sql = "UPDATE temporizador SET estado=? WHERE id='1'";
		return ejecutarConsultaPreparada($sql, "s", [$st]);
	}

	/**
	 * Método para consultar el estado del temporizador
	 * No requiere prepared statement (sin parámetros dinámicos)
	 */
	public function consultatemporizador()
	{
		$sql = "SELECT id as idtempo, tiempo, estado FROM temporizador WHERE id='1'";
		return ejecutarConsulta($sql);
	}

	/**
	 * Método para guardar detalles de sesión del usuario
	 * Refactorizado con prepared statement
	 */
	public function savedetalsesion($idusuario)
	{
		$sql = "INSERT INTO detalle_usuario_sesion (idusuario, tcomprobante, idcomprobante, fechahora)
				VALUES (?, '', NULL, NOW())";
		return ejecutarConsultaPreparada($sql, "i", [$idusuario]);
	}

	/**
	 * Actualiza la contraseña de un usuario (usado para migración SHA1 → bcrypt)
	 * @param int $idusuario ID del usuario
	 * @param string $nuevo_hash Hash bcrypt del password
	 * @return bool true si se actualizó correctamente
	 */
	public function actualizarPassword($idusuario, $nuevo_hash)
	{
		$sql = "UPDATE usuario SET clave=? WHERE idusuario=?";
		$stmt = ejecutarConsultaPreparada($sql, "si", [$nuevo_hash, $idusuario]);
		return $stmt !== false;
	}

}

?>