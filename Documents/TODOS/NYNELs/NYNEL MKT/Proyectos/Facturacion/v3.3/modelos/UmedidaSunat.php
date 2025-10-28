<?php
// Modelo para gestión de Unidades de Medida SUNAT (Catálogo 03)
// Incluimos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

Class UmedidaSunat
{
	// Constructor
	public function __construct()
	{
		// Constructor vacío - la conexión se maneja en Conexion.php
	}

	/**
	 * Insertar nueva unidad de medida SUNAT
	 * @param string $codigo Código SUNAT (1-3 caracteres, ej: NIU, ZZ, KGM)
	 * @param string $descripcion Descripción completa de la unidad
	 * @param string $simbolo Símbolo corto (opcional, ej: UND, KG, M)
	 * @param string $notas Notas adicionales u observaciones
	 * @param int $estado Estado activo (1) o inactivo (0)
	 * @return mixed Resultado de la ejecución
	 */
	public function insertar($codigo, $descripcion, $simbolo, $notas, $estado)
	{
		$sql = "INSERT INTO umedida_sunat (codigo, descripcion, simbolo, notas, estado)
		        VALUES ('$codigo', '$descripcion', '$simbolo', '$notas', '$estado')";
		return ejecutarConsulta($sql);
	}

	/**
	 * Editar unidad de medida SUNAT existente
	 * NOTA: El código NO se puede modificar (es clave primaria)
	 * @param int $idsunat_um ID autoincremental de la unidad
	 * @param string $descripcion Descripción completa
	 * @param string $simbolo Símbolo corto
	 * @param string $notas Notas adicionales
	 * @param int $estado Estado activo/inactivo
	 * @return mixed Resultado de la ejecución
	 */
	public function editar($idsunat_um, $descripcion, $simbolo, $notas, $estado)
	{
		$sql = "UPDATE umedida_sunat
		        SET descripcion = '$descripcion',
		            simbolo = '$simbolo',
		            notas = '$notas',
		            estado = '$estado'
		        WHERE idsunat_um = '$idsunat_um'";
		return ejecutarConsulta($sql);
	}

	/**
	 * Desactivar unidad de medida SUNAT
	 * @param int $idsunat_um ID de la unidad a desactivar
	 * @return mixed Resultado de la ejecución
	 */
	public function desactivar($idsunat_um)
	{
		$sql = "UPDATE umedida_sunat SET estado = '0' WHERE idsunat_um = '$idsunat_um'";
		return ejecutarConsulta($sql);
	}

	/**
	 * Activar unidad de medida SUNAT
	 * @param int $idsunat_um ID de la unidad a activar
	 * @return mixed Resultado de la ejecución
	 */
	public function activar($idsunat_um)
	{
		$sql = "UPDATE umedida_sunat SET estado = '1' WHERE idsunat_um = '$idsunat_um'";
		return ejecutarConsulta($sql);
	}

	/**
	 * Eliminar unidad de medida SUNAT
	 * ADVERTENCIA: Solo se puede eliminar si no está siendo utilizada en compras
	 * La tabla detalle_compra_producto tiene FK a umedida_sunat.codigo
	 * @param int $idsunat_um ID de la unidad a eliminar
	 * @return mixed Resultado de la ejecución
	 */
	public function eliminar($idsunat_um)
	{
		$sql = "DELETE FROM umedida_sunat WHERE idsunat_um = '$idsunat_um'";
		return ejecutarConsulta($sql);
	}

	/**
	 * Validar si el código SUNAT ya existe en la base de datos
	 * Se usa para prevenir duplicados al insertar nuevas unidades
	 * @param string $codigo Código SUNAT a validar
	 * @return mixed Fila encontrada o false
	 */
	public function validarCodigo($codigo)
	{
		$sql = "SELECT * FROM umedida_sunat WHERE codigo = '$codigo'";
		return ejecutarConsultaSimpleFila($sql);
	}

	/**
	 * Mostrar datos de una unidad de medida SUNAT específica
	 * @param int $idsunat_um ID de la unidad a mostrar
	 * @return mixed Fila con los datos de la unidad
	 */
	public function mostrar($idsunat_um)
	{
		$sql = "SELECT * FROM umedida_sunat WHERE idsunat_um = '$idsunat_um'";
		return ejecutarConsultaSimpleFila($sql);
	}

	/**
	 * Listar todas las unidades de medida SUNAT (activas e inactivas)
	 * @return mixed Resultado con todas las unidades ordenadas por código
	 */
	public function listar()
	{
		$sql = "SELECT * FROM umedida_sunat ORDER BY codigo ASC";
		return ejecutarConsulta($sql);
	}

	/**
	 * Listar solo unidades de medida SUNAT activas
	 * Se usa para llenar selects en formularios (compras, productos, etc.)
	 * @return mixed Resultado con unidades activas ordenadas por código
	 */
	public function listarActivas()
	{
		$sql = "SELECT * FROM umedida_sunat WHERE estado = '1' ORDER BY codigo ASC";
		return ejecutarConsulta($sql);
	}

	/**
	 * Buscar unidad de medida SUNAT por código
	 * Útil para validaciones y búsquedas rápidas
	 * @param string $codigo Código SUNAT a buscar
	 * @return mixed Fila encontrada o false
	 */
	public function buscarPorCodigo($codigo)
	{
		$sql = "SELECT * FROM umedida_sunat WHERE codigo = '$codigo' AND estado = '1'";
		return ejecutarConsultaSimpleFila($sql);
	}
}

?>
