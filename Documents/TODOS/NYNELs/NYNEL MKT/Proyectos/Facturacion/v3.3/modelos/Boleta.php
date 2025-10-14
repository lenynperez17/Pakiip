<?php
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";
class Boleta
{
  // Propiedad para almacenar el último error MySQL
  private $lastError = '';

  //Implementamos nuestro constructor
  public function __construct()
  {



  }

  /**
   * Obtiene el último error MySQL ocurrido en las operaciones de la clase
   * @return string El mensaje del último error, o cadena vacía si no hay error
   */
  public function getLastError()
  {
    return $this->lastError;
  }

  //Implementamos un método para insertar registros para boleta

  public function insertar(
    $idusuario,
    $fecha_emision_01,
    $firma_digital_36,
    $idempresa,
    $tipo_documento_06,
    $numeracion_07,
    $idcl,
    $codigo_tipo_15_1,
    $monto_15_2,
    $sumatoria_igv_18_1,
    $sumatoria_igv_18_2,
    $sumatoria_igv_18_3,
    $sumatoria_igv_18_4,
    $sumatoria_igv_18_5,
    $importe_total_23,
    $codigo_leyenda_26_1,
    $descripcion_leyenda_26_2,
    $tipo_documento_25_1,
    $guia_remision_25,
    $version_ubl_37,
    $version_estructura_38,
    $tipo_moneda_24,
    $tasa_igv,
    $idarticulo,
    $numero_orden_item_29,
    $cantidad_item_12,
    $codigo_precio_14_1,
    $precio_unitario,
    $igvBD,
    $igvBD2,
    $afectacion_igv_3,
    $afectacion_igv_4,
    $afectacion_igv_5,
    $afectacion_igv_6,
    $igvBD3,
    $vvu,
    $subtotalBD,
    $codigo,
    $unidad_medida,
    $idserie,
    $SerieReal,
    $numero_boleta,
    $tipodocuCliente,
    $rucCliente,
    $RazonSocial,
    $hora,
    $dctoitem,
    $vendedorsitio,
    $tcambio,
    $totaldescu,
    $domicilio_fiscal,
    $tipopago,
    $nroreferencia,
    $ipagado,
    $saldo,
    $descdet,
    $total_icbper,
    $tipoboleta,
    $cantidadreal,
    $ccuotas,
    $fechavecredito,
    $montocuota,
    $tadc,
    $transferencia,
    $ncuotahiden,
    $montocuotacre,
    $fechapago,
    $fechavenc,
    $efectivo,
    $visa,
    $yape,
    $plin,
    $mastercard,
    $deposito
  ) {
    global $conexion;

    // Iniciar transacción
    mysqli_begin_transaction($conexion);

    try {
      $st = 1;  // Estado inicial: 1 = EMITIDO (INTEGER para columna estado)

      if ($SerieReal == '0001' || $SerieReal == '0002') {
        $st = 6;  // Estado 6 para series especiales
      }
      $formapago = '';
      $montofpago = NULL;  // DECIMAL: debe ser NULL si no tiene valor
      $monedafpago = NULL;  // VARCHAR: NULL si no tiene valor

      if ($tipopago == 'Contado') {
        $formapago = 'Contado';
      } else {
        $formapago = 'Credito';
        $montofpago = $importe_total_23;
        $monedafpago = $tipo_moneda_24;
      }

      $montotar = 0;
      $montotran = 0;
      if ($tadc == '1') {
        $montotar = $importe_total_23;
      }

      if ($transferencia == '1') {
        $montotran = $importe_total_23;
      }

      $fecha_hora_emision = $fecha_emision_01 . ' ' . $hora;
      $numeracion_completa = $SerieReal . '-' . $numero_boleta;

      // INSERT boleta con prepared statement
      $sql = "INSERT INTO boleta (
        idusuario, fecha_emision_01, firma_digital_36, idempresa, tipo_documento_06,
        numeracion_07, idcliente, codigo_tipo_15_1, monto_15_2, sumatoria_igv_18_1,
        sumatoria_igv_18_2, codigo_tributo_18_3, nombre_tributo_18_4, codigo_internacional_18_5,
        importe_total_23, codigo_leyenda_26_1, descripcion_leyenda_26_2, tipo_documento_25_1,
        guia_remision_25, version_ubl_37, version_estructura_38, tipo_moneda_24, tasa_igv,
        estado, tipodocuCliente, rucCliente, RazonSocial, tdescuento, vendedorsitio, tcambio,
        tipopago, nroreferencia, ipagado, saldo, DetalleSunat, icbper, tipoboleta, formapago,
        montofpago, monedafpago, ccuotas, fechavecredito, montocuota, tarjetadc, transferencia,
        montotarjetadc, montotransferencia, fechavenc, efectivo, visa, yape, plin, mastercard, deposito
      ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
        (SELECT codigo FROM catalogo5 WHERE codigo = ?),
        (SELECT descripcion FROM catalogo5 WHERE codigo = ?),
        (SELECT unece5153 FROM catalogo5 WHERE codigo = ?),
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
        ?, 'EMITIDO', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
      )";

      $stmt = $conexion->prepare($sql);
      if (!$stmt) {
        $this->lastError = $conexion->error;
        error_log("Error preparando INSERT boleta: " . $conexion->error);
        mysqli_rollback($conexion);
        return false;
      }

      $stmt->bind_param(
        "issiisisssssssssssssssissssssssssssssssssssssssssssss",
        $idusuario, $fecha_hora_emision, $firma_digital_36, $idempresa, $tipo_documento_06,
        $numeracion_completa, $idcl, $codigo_tipo_15_1, $monto_15_2, $sumatoria_igv_18_1,
        $sumatoria_igv_18_2, $sumatoria_igv_18_3, $sumatoria_igv_18_3, $sumatoria_igv_18_3,
        $importe_total_23, $codigo_leyenda_26_1, $descripcion_leyenda_26_2, $tipo_documento_25_1,
        $guia_remision_25, $version_ubl_37, $version_estructura_38, $tipo_moneda_24, $tasa_igv,
        $st, $tipodocuCliente, $rucCliente, $RazonSocial, $totaldescu, $vendedorsitio, $tcambio,
        $tipopago, $nroreferencia, $ipagado, $saldo, $total_icbper, $tipoboleta, $formapago,
        $montofpago, $monedafpago, $ccuotas, $fechavecredito, $montocuota, $tadc, $transferencia,
        $montotar, $montotran, $fechavenc, $efectivo, $visa, $yape, $plin, $mastercard, $deposito
      );

      if (!$stmt->execute()) {
        $this->lastError = $stmt->error;
        error_log("Error ejecutando INSERT boleta: " . $stmt->error);
        $stmt->close();
        mysqli_rollback($conexion);
        return false;
      }

      $idBoletaNew = $conexion->insert_id;
      $stmt->close();

      $sw = true;

      // SI EL NUMERO DE COMPROBANTE YA EXISTE NO HARA LA OPERACIon
      if ($idBoletaNew == "") {
        $this->lastError = "No se pudo obtener el ID de la boleta insertada (posible duplicado de numeración)";
        $sw = false;
        $idserie = "";
        mysqli_rollback($conexion);
        return false;
      } else {
        //============= PROCESAR DETALLES (eliminar while+count bug) =============

        $total_items = count($idarticulo);
        for ($num_elementos = 0; $num_elementos < $total_items; $num_elementos++) {
          // INSERT detalle_boleta_producto con prepared statement
          $sql_detalle = "INSERT INTO detalle_boleta_producto (
            idboleta, idarticulo, numero_orden_item_29, cantidad_item_12, codigo_precio_14_1,
            precio_uni_item_14_2, afectacion_igv_item_monto_27_1, afectacion_igv_item_monto_27_2,
            afectacion_igv_3, afectacion_igv_4, afectacion_igv_5, afectacion_igv_6,
            igv_item, valor_uni_item_31, valor_venta_item_32, dcto_item, descdet, umedida
          ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?,
            (SELECT codigo FROM catalogo7 WHERE codigo = ?),
            (SELECT codigo FROM catalogo5 WHERE codigo = ?),
            (SELECT descripcion FROM catalogo5 WHERE codigo = ?),
            (SELECT unece5153 FROM catalogo5 WHERE codigo = ?),
            ?, ?, ?, ?, ?, ?
          )";

          $stmt_det = $conexion->prepare($sql_detalle);
          if (!$stmt_det) {
            $this->lastError = $conexion->error;
            error_log("Error preparando INSERT detalle: " . $conexion->error);
            mysqli_rollback($conexion);
            return false;
          }

          $stmt_det->bind_param(
            "iissssssssssssssss",
            $idBoletaNew,
            $idarticulo[$num_elementos],
            $numero_orden_item_29[$num_elementos],
            $cantidad_item_12[$num_elementos],
            $codigo_precio_14_1,
            $precio_unitario[$num_elementos],
            $igvBD[$num_elementos],
            $igvBD2[$num_elementos],
            $afectacion_igv_3[$num_elementos],
            $afectacion_igv_4[$num_elementos],
            $afectacion_igv_4[$num_elementos],
            $afectacion_igv_4[$num_elementos],
            $igvBD3[$num_elementos],
            $vvu[$num_elementos],
            $subtotalBD[$num_elementos],
            $dctoitem[$num_elementos],
            $descdet[$num_elementos],
            $unidad_medida[$num_elementos]
          );

          if (!$stmt_det->execute()) {
            $this->lastError = $stmt_det->error;
            error_log("Error ejecutando INSERT detalle: " . $stmt_det->error);
            $stmt_det->close();
            mysqli_rollback($conexion);
            return false;
          }
          $stmt_det->close();

          // INSERT kardex con prepared statement (comentado en original pero preparado)
          /* $sql_kardex = "INSERT INTO kardex (
            idcomprobante, idarticulo, transaccion, codigo, fecha, tipo_documento,
            numero_doc, cantidad, costo_1, unidad_medida, saldo_final, costo_2,
            valor_final, idempresa, tcambio, moneda
          ) VALUES (?, ?, 'VENTA', ?, ?, '03', ?, ?, ?, ?, '', '', '', ?, ?, ?)";

          $stmt_kar = $conexion->prepare($sql_kardex);
          $stmt_kar->bind_param("iissssssiss",
            $idBoletaNew, $idarticulo[$num_elementos], $codigo[$num_elementos],
            $fecha_emision_01, $numeracion_completa, $cantidadreal[$num_elementos],
            $vvu[$num_elementos], $unidad_medida[$num_elementos], $idempresa,
            $tcambio, $tipo_moneda_24);
          $stmt_kar->execute();
          $stmt_kar->close(); */

          // UPDATE persona con prepared statement (solo una vez, no en loop)
          if ($num_elementos == 0) {
            $sqlupdatecliente = "UPDATE persona
              SET domicilio_fiscal = ?, razon_social = ?, nombre_comercial = ?, nombres = ?
              WHERE idpersona = ?";

            $stmt_cli = $conexion->prepare($sqlupdatecliente);
            if (!$stmt_cli) {
              $this->lastError = $conexion->error;
              error_log("Error preparando UPDATE persona: " . $conexion->error);
              mysqli_rollback($conexion);
              return false;
            }

            $stmt_cli->bind_param("ssssi", $domicilio_fiscal, $RazonSocial, $RazonSocial, $RazonSocial, $idcl);

            if (!$stmt_cli->execute()) {
              $this->lastError = $stmt_cli->error;
              error_log("Error ejecutando UPDATE persona: " . $stmt_cli->error);
              $stmt_cli->close();
              mysqli_rollback($conexion);
              return false;
            }
            $stmt_cli->close();
          }

          // UPDATE articulo con prepared statement (solo si no es servicios)
          if ($tipoboleta != 'servicios') {
            $sql_update_articulo = "UPDATE articulo
              SET saldo_finu = saldo_finu - ?,
                  ventast = ventast + ?,
                  valor_finu = (saldo_iniu + comprast - ventast) * precio_final_kardex,
                  stock = saldo_finu,
                  valor_fin_kardex = (SELECT valor_final FROM kardex
                                     WHERE idarticulo = ? AND transaccion = 'VENTA'
                                     ORDER BY idkardex DESC LIMIT 1)
              WHERE idarticulo = ?";

            $stmt_art = $conexion->prepare($sql_update_articulo);
            if (!$stmt_art) {
              $this->lastError = $conexion->error;
              error_log("Error preparando UPDATE articulo: " . $conexion->error);
              mysqli_rollback($conexion);
              return false;
            }

            $stmt_art->bind_param(
              "ddii",
              $cantidadreal[$num_elementos],
              $cantidadreal[$num_elementos],
              $idarticulo[$num_elementos],
              $idarticulo[$num_elementos]
            );

            if (!$stmt_art->execute()) {
              $this->lastError = $stmt_art->error;
              error_log("Error ejecutando UPDATE articulo: " . $stmt_art->error);
              $stmt_art->close();
              mysqli_rollback($conexion);
              return false;
            }
            $stmt_art->close();
          }
        }

      }

      // INSERT detalle_usuario_sesion con prepared statement
      $sqldetallesesionusuario = "INSERT INTO detalle_usuario_sesion
        (idusuario, tcomprobante, idcomprobante, fechahora)
        VALUES (?, ?, ?, NOW())";

      $stmt_sesion = $conexion->prepare($sqldetallesesionusuario);
      if (!$stmt_sesion) {
        $this->lastError = $conexion->error;
        error_log("Error preparando INSERT sesion: " . $conexion->error);
        mysqli_rollback($conexion);
        return false;
      }

      $stmt_sesion->bind_param("isi", $idusuario, $tipo_documento_06, $idBoletaNew);

      if (!$stmt_sesion->execute()) {
        $this->lastError = $stmt_sesion->error;
        error_log("Error ejecutando INSERT sesion: " . $stmt_sesion->error);
        $stmt_sesion->close();
        mysqli_rollback($conexion);
        return false;
      }
      $stmt_sesion->close();

      // PROCESAR CUOTAS (eliminar while+count bug)
      if ($tipopago == 'Credito') {
        $total_cuotas = count($ncuotahiden);

        // Preparar statement UNA VEZ fuera del loop
        $sql_cuota = "INSERT INTO cuotas (
          tipocomprobante, idcomprobante, ncuota, montocuota, fechacuota, estadocuota
        ) VALUES ('03', ?, ?, ?, ?, '1')";

        $stmt_cuota = $conexion->prepare($sql_cuota);
        if (!$stmt_cuota) {
          $this->lastError = $conexion->error;
          error_log("Error preparando INSERT cuota: " . $conexion->error);
          mysqli_rollback($conexion);
          return false;
        }

        for ($numcuotas = 0; $numcuotas < $total_cuotas; $numcuotas++) {
          $stmt_cuota->bind_param(
            "isss",
            $idBoletaNew,
            $ncuotahiden[$numcuotas],
            $montocuotacre[$numcuotas],
            $fechapago[$numcuotas]
          );

          if (!$stmt_cuota->execute()) {
            $this->lastError = $stmt_cuota->error;
            error_log("Error ejecutando INSERT cuota: " . $stmt_cuota->error);
            $stmt_cuota->close();
            mysqli_rollback($conexion);
            $sw = false;
            return false;
          }
        }
        $stmt_cuota->close();

      } else { // SI ES AL CONTADO
        $sql_cuota_contado = "INSERT INTO cuotas (
          tipocomprobante, idcomprobante, ncuota, montocuota, fechacuota, estadocuota
        ) VALUES ('03', ?, '1', ?, ?, '0')";

        $stmt_contado = $conexion->prepare($sql_cuota_contado);
        if (!$stmt_contado) {
          $this->lastError = $conexion->error;
          error_log("Error preparando INSERT cuota contado: " . $conexion->error);
          mysqli_rollback($conexion);
          return false;
        }

        $stmt_contado->bind_param("iss", $idBoletaNew, $importe_total_23, $fecha_emision_01);

        if (!$stmt_contado->execute()) {
          $this->lastError = $stmt_contado->error;
          error_log("Error ejecutando INSERT cuota contado: " . $stmt_contado->error);
          $stmt_contado->close();
          mysqli_rollback($conexion);
          $sw = false;
          return false;
        }
        $stmt_contado->close();
      }

      // UPDATE numeracion con prepared statement
      $sql_update_numeracion = "UPDATE numeracion SET numero = ? WHERE idnumeracion = ?";

      $stmt_num = $conexion->prepare($sql_update_numeracion);
      if (!$stmt_num) {
        $this->lastError = $conexion->error;
        error_log("Error preparando UPDATE numeracion: " . $conexion->error);
        mysqli_rollback($conexion);
        return false;
      }

      $stmt_num->bind_param("si", $numero_boleta, $idserie);

      if (!$stmt_num->execute()) {
        $this->lastError = $stmt_num->error;
        error_log("Error ejecutando UPDATE numeracion: " . $stmt_num->error);
        $stmt_num->close();
        mysqli_rollback($conexion);
        return false;
      }
      $stmt_num->close();

      // Commit de transacción
      mysqli_commit($conexion);
      return $idBoletaNew;

    } catch (Exception $e) {
      $this->lastError = $e->getMessage();
      error_log("Error en Boleta::insertar(): " . $e->getMessage());
      mysqli_rollback($conexion);
      return false;
    }
  }
  //=============== EXPORTAR COMPROBANTES A TXT ========================





  //Implementamos un método para anular la factura

  public function anular($idboleta)
  {
    global $conexion;
    
    // Iniciar transacción
    mysqli_begin_transaction($conexion);
    
    try {
      // ============= PARTE 1: PROCESAR DETALLE DE BOLETA =============
      
      // SELECT detalle_boleta_producto con prepared statement
      $sql_select = "SELECT idboleta, idarticulo 
                     FROM detalle_boleta_producto 
                     WHERE idboleta = ?";
      
      $stmt_select = $conexion->prepare($sql_select);
      if (!$stmt_select) {
        error_log("Error preparando SELECT detalle_boleta: " . $conexion->error);
        mysqli_rollback($conexion);
        return false;
      }
      
      $stmt_select->bind_param("i", $idboleta);
      
      if (!$stmt_select->execute()) {
        error_log("Error ejecutando SELECT detalle_boleta: " . $stmt_select->error);
        $stmt_select->close();
        mysqli_rollback($conexion);
        return false;
      }
      
      $resultado = $stmt_select->get_result();
      $stmt_select->close();
      
      // Procesar cada fila directamente en while (SIN for loop)
      while ($fila = $resultado->fetch_assoc()) {
        $idboleta_detalle = $fila["idboleta"];
        $idarticulo = $fila["idarticulo"];
        
        // UPDATE articulo con prepared statement (revertir stock)
        $sql_update_articulo = "UPDATE detalle_boleta_producto de 
                               INNER JOIN articulo a ON de.idarticulo = a.idarticulo 
                               SET a.saldo_finu = a.saldo_finu + de.cantidad_item_12, 
                                   a.stock = a.stock + de.cantidad_item_12, 
                                   a.ventast = a.ventast - de.cantidad_item_12, 
                                   a.valor_finu = (a.saldo_finu + a.comprast - a.ventast) * a.costo_compra 
                               WHERE de.idboleta = ? AND de.idarticulo = ?";
        
        $stmt_update = $conexion->prepare($sql_update_articulo);
        if (!$stmt_update) {
          error_log("Error preparando UPDATE articulo: " . $conexion->error);
          mysqli_rollback($conexion);
          return false;
        }
        
        $stmt_update->bind_param("ii", $idboleta_detalle, $idarticulo);
        
        if (!$stmt_update->execute()) {
          error_log("Error ejecutando UPDATE articulo: " . $stmt_update->error);
          $stmt_update->close();
          mysqli_rollback($conexion);
          return false;
        }
        $stmt_update->close();
        
        // INSERT en kardex con prepared statement
        $sql_kardex = "INSERT INTO kardex (
                         idcomprobante, idarticulo, transaccion, codigo, fecha, 
                         tipo_documento, numero_doc, cantidad, costo_1, unidad_medida, 
                         saldo_final, costo_2, valor_final
                       )
                       SELECT 
                         ?, 
                         a.idarticulo, 
                         'ANULADO', 
                         a.codigo,
                         (SELECT fecha_emision_01 FROM boleta WHERE idboleta = ?),
                         '03',
                         (SELECT numeracion_07 FROM boleta WHERE idboleta = ?),
                         dtb.cantidad_item_12,
                         dtb.valor_uni_item_31,
                         a.unidad_medida,
                         0, 0, 0
                       FROM articulo a 
                       INNER JOIN detalle_boleta_producto dtb ON a.idarticulo = dtb.idarticulo 
                       WHERE a.idarticulo = ? AND dtb.idboleta = ?";
        
        $stmt_kardex = $conexion->prepare($sql_kardex);
        if (!$stmt_kardex) {
          error_log("Error preparando INSERT kardex: " . $conexion->error);
          mysqli_rollback($conexion);
          return false;
        }
        
        $stmt_kardex->bind_param("iiiii", $idboleta, $idboleta_detalle, $idboleta_detalle, $idarticulo, $idboleta_detalle);
        
        if (!$stmt_kardex->execute()) {
          error_log("Error ejecutando INSERT kardex: " . $stmt_kardex->error);
          $stmt_kardex->close();
          mysqli_rollback($conexion);
          return false;
        }
        $stmt_kardex->close();
      }
      
      // UPDATE estado de boleta
      $sql_estado = "UPDATE boleta SET estado = '0' WHERE idboleta = ?";
      $stmt_estado = $conexion->prepare($sql_estado);
      if (!$stmt_estado) {
        error_log("Error preparando UPDATE estado boleta: " . $conexion->error);
        mysqli_rollback($conexion);
        return false;
      }
      
      $stmt_estado->bind_param("i", $idboleta);
      
      if (!$stmt_estado->execute()) {
        error_log("Error ejecutando UPDATE estado boleta: " . $stmt_estado->error);
        $stmt_estado->close();
        mysqli_rollback($conexion);
        return false;
      }
      $stmt_estado->close();
      
      // ============= PARTE 2: GENERAR ARCHIVO SUNAT =============
      
      // Obtener datos de empresa
      require_once "../modelos/Factura.php";
      $factura = new Factura();
      $datos = $factura->datosemp($_SESSION['idempresa']);
      $datose = $datos->fetch_object();
      
      // Obtener rutas
      require_once "../modelos/Rutas.php";
      $rutas = new Rutas();
      $Rrutas = $rutas->mostrar2();
      $Prutas = $Rrutas->fetch_object();
      $rutadata = $Prutas->rutadata;
      $rutabaja = $Prutas->rutabaja;
      
      // SELECT datos para archivo SUNAT con prepared statement
      $sql_sunat = "SELECT 
                      DATE_FORMAT(fecha_emision_01, '%Y-%m-%d') AS fecha, 
                      DATE_FORMAT(fecha_baja, '%Y%m%d') AS fechabaja2, 
                      DATE_FORMAT(fecha_baja, '%Y-%m-%d') AS fechabaja, 
                      RIGHT(SUBSTRING_INDEX(numeracion_07,'-',1),3) AS serie,
                      tipodocuCliente, rucCliente, RazonSocial, tipo_moneda_24, 
                      monto_15_2 AS subtotal, sumatoria_igv_18_1 AS igv, 
                      importe_total_23 AS total, tipo_documento_06 AS tipocomp, 
                      numeracion_07 AS numerodoc, b.estado, comentario_baja  
                    FROM boleta b 
                    INNER JOIN persona p ON b.idcliente = p.idpersona 
                    WHERE b.idboleta = ?";
      
      $stmt_sunat = $conexion->prepare($sql_sunat);
      if (!$stmt_sunat) {
        error_log("Error preparando SELECT datos SUNAT: " . $conexion->error);
        mysqli_rollback($conexion);
        return false;
      }
      
      $stmt_sunat->bind_param("i", $idboleta);
      
      if (!$stmt_sunat->execute()) {
        error_log("Error ejecutando SELECT datos SUNAT: " . $stmt_sunat->error);
        $stmt_sunat->close();
        mysqli_rollback($conexion);
        return false;
      }
      
      $result = $stmt_sunat->get_result();
      $stmt_sunat->close();
      
      // Variables para archivo
      $fecdeldia = date("Ymd");
      
      // Procesar cada fila directamente en while (SIN for loop)
      while ($row = $result->fetch_assoc()) {
        $fecha = $row["fecha"];
        $fechabaja = $row["fechabaja"];
        $tipocomp = $row["tipocomp"];
        $numeroc = $row["numerodoc"];
        $comen = $row["comentario_baja"];
        $ruc = $datose->numero_ruc;
        $fbaja2 = $row["fechabaja2"];
        
        // Generar archivo .cba
        $path = $rutadata . $ruc . "-RA-" . $fbaja2 . "-011.cba";
        $handle = fopen($path, "a");
        
        if ($handle) {
          fwrite($handle, $fecha . "|" . $fechabaja . "|" . $tipocomp . "|" . $numeroc . "|" . $comen . "|\r\n");
          fclose($handle);
        } else {
          error_log("Error abriendo archivo SUNAT: " . $path);
          mysqli_rollback($conexion);
          return false;
        }
      }
      
      // Commit de transacción
      mysqli_commit($conexion);
      return true;
      
    } catch (Exception $e) {
      error_log("Error en anular(): " . $e->getMessage());
      mysqli_rollback($conexion);
      return false;
    }
  }



  public function baja($idboleta, $fecha_baja, $com, $hora)
  {
    global $conexion;

    // Iniciar transacción
    mysqli_begin_transaction($conexion);

    try {
      // SELECT detalle_boleta_producto con prepared statement
      $sql_select = "SELECT dt.idboleta, a.idarticulo, dt.cantidad_item_12, dt.valor_uni_item_31, a.codigo, a.unidad_medida
                     FROM detalle_boleta_producto dt
                     INNER JOIN articulo a ON dt.idarticulo = a.idarticulo
                     WHERE dt.idboleta = ?";

      $stmt_select = $conexion->prepare($sql_select);
      if (!$stmt_select) {
        error_log("Error preparando SELECT detalle_boleta en baja(): " . $conexion->error);
        mysqli_rollback($conexion);
        return false;
      }

      $stmt_select->bind_param("i", $idboleta);

      if (!$stmt_select->execute()) {
        error_log("Error ejecutando SELECT detalle_boleta en baja(): " . $stmt_select->error);
        $stmt_select->close();
        mysqli_rollback($conexion);
        return false;
      }

      $resultado = $stmt_select->get_result();
      $stmt_select->close();

      // Procesar cada fila directamente en while (SIN for loop)
      while ($fila = $resultado->fetch_assoc()) {
        $idboleta_detalle = $fila["idboleta"];
        $idarticulo = $fila["idarticulo"];
        $cantidad = $fila["cantidad_item_12"];
        $codigo = $fila["codigo"];
        $valor_unitario = $fila["valor_uni_item_31"];
        $unidad_medida = $fila["unidad_medida"];

        // UPDATE articulo - revertir stock (paso 1)
        $sql_update_articulo = "UPDATE detalle_boleta_producto de
                               INNER JOIN articulo a ON de.idarticulo = a.idarticulo
                               SET a.saldo_finu = a.saldo_finu + ?,
                                   a.stock = a.stock + ?,
                                   a.ventast = a.ventast - ?
                               WHERE de.idboleta = ? AND de.idarticulo = ?";

        $stmt_update = $conexion->prepare($sql_update_articulo);
        if (!$stmt_update) {
          error_log("Error preparando UPDATE articulo en baja(): " . $conexion->error);
          mysqli_rollback($conexion);
          return false;
        }

        $stmt_update->bind_param("dddii", $cantidad, $cantidad, $cantidad, $idboleta_detalle, $idarticulo);

        if (!$stmt_update->execute()) {
          error_log("Error ejecutando UPDATE articulo en baja(): " . $stmt_update->error);
          $stmt_update->close();
          mysqli_rollback($conexion);
          return false;
        }
        $stmt_update->close();

        // UPDATE articulo - recalcular valor_finu (paso 2)
        $sql_update_articulo_2 = "UPDATE detalle_boleta_producto de
                                 INNER JOIN articulo a ON de.idarticulo = a.idarticulo
                                 SET a.valor_finu = (a.saldo_iniu + a.comprast - a.ventast) * a.costo_compra
                                 WHERE de.idboleta = ? AND de.idarticulo = ?";

        $stmt_update_2 = $conexion->prepare($sql_update_articulo_2);
        if (!$stmt_update_2) {
          error_log("Error preparando UPDATE articulo 2 en baja(): " . $conexion->error);
          mysqli_rollback($conexion);
          return false;
        }

        $stmt_update_2->bind_param("ii", $idboleta_detalle, $idarticulo);

        if (!$stmt_update_2->execute()) {
          error_log("Error ejecutando UPDATE articulo 2 en baja(): " . $stmt_update_2->error);
          $stmt_update_2->close();
          mysqli_rollback($conexion);
          return false;
        }
        $stmt_update_2->close();

        // INSERT en kardex con prepared statement
        $fecha_completa = $fecha_baja . ' ' . $hora;

        $sql_kardex = "INSERT INTO kardex (
                         idcomprobante, idarticulo, transaccion, codigo, fecha,
                         tipo_documento, numero_doc, cantidad, costo_1, unidad_medida,
                         saldo_final, costo_2, valor_final
                       ) VALUES (
                         ?, ?, 'ANULADO', ?, ?, '03',
                         (SELECT numeracion_07 FROM boleta WHERE idboleta = ?),
                         ?, ?, ?, 0, 0, 0
                       )";

        $stmt_kardex = $conexion->prepare($sql_kardex);
        if (!$stmt_kardex) {
          error_log("Error preparando INSERT kardex en baja(): " . $conexion->error);
          mysqli_rollback($conexion);
          return false;
        }

        $stmt_kardex->bind_param("iissidds", $idboleta, $idarticulo, $codigo, $fecha_completa, $idboleta_detalle, $cantidad, $valor_unitario, $unidad_medida);

        if (!$stmt_kardex->execute()) {
          error_log("Error ejecutando INSERT kardex en baja(): " . $stmt_kardex->error);
          $stmt_kardex->close();
          mysqli_rollback($conexion);
          return false;
        }
        $stmt_kardex->close();
      }

      // UPDATE estado de boleta a '3' (Dada de baja)
      $fecha_completa = $fecha_baja . ' ' . $hora;
      $sql_estado = "UPDATE boleta
                    SET estado = '3',
                        fecha_baja = ?,
                        comentario_baja = ?,
                        DetalleSunat = 'C/Baja',
                        CodigoRptaSunat = '3'
                    WHERE idboleta = ?";

      $stmt_estado = $conexion->prepare($sql_estado);
      if (!$stmt_estado) {
        error_log("Error preparando UPDATE estado boleta en baja(): " . $conexion->error);
        mysqli_rollback($conexion);
        return false;
      }

      $stmt_estado->bind_param("ssi", $fecha_completa, $com, $idboleta);

      if (!$stmt_estado->execute()) {
        error_log("Error ejecutando UPDATE estado boleta en baja(): " . $stmt_estado->error);
        $stmt_estado->close();
        mysqli_rollback($conexion);
        return false;
      }
      $stmt_estado->close();

      // Commit de transacción
      mysqli_commit($conexion);
      return true;

    } catch (Exception $e) {
      error_log("Error en baja(): " . $e->getMessage());
      mysqli_rollback($conexion);
      return false;
    }
  }
  public function mostrar($idboleta)
  {

    $sql = "select 

        b.idboleta,

        date(b.fecha_emision_01) as fecha,

        b.idcliente,p.razon_social as cliente,

        p.numero_documento,

        p.domicilio_fiscal,

        u.idusuario,

        u.nombre as usuario,

        b.tipo_documento_06,

        b.numeracion_07, 

        b.importe_total_23, 

        b.estado 

        from 

        boleta b inner join persona p on b.idcliente=p.idpersona inner join usuario u on b.idusuario=u.idusuario WHERE b.idboleta='$idboleta'";

    return ejecutarConsultaSimpleFila($sql);

  }



  public function listarDetalle($idboleta)
  {

    $sql = "select 

        df.idboleta,

        df.idarticulo,

        a.nombre,

        df.cantidad_item_12, 

        df.valor_uni_item_14, 

        df.valor_venta_item_21, 

        df.igv_item 

        from 

        detalle_fac_art df inner join articulo a on df.idarticulo=a.idarticulo where df.idboleta='$idboleta'";

    return ejecutarConsulta($sql);

  }



  //Implementar un método para listar los registros

  public function listar($idempresa)
  {

    $sql = "select 
        b.idboleta,
        date_format(b.fecha_emision_01, '%d/%m/%y') as fecha,
        b.idcliente,
        left(p.razon_social, 20) as cliente,
        b.vendedorsitio,
        u.nombre as usuario,
        b.tipo_documento_06,
        b.numeracion_07,
        format(b.importe_total_23, 2) as importe_total_23, 
        b.estado, 
        p.nombres, 
        p.apellidos,
        e.numero_ruc,
        p.email,
        b.CodigoRptaSunat,
        b.DetalleSunat,
        b.tarjetadc,
        b.montotarjetadc,
        b.transferencia,
        b.montotransferencia,
        b.tipo_moneda_24 as moneda,
        b.tcambio,
        (b.tcambio * importe_total_23) as valordolsol,
        b.formapago,
        group_concat(a.nombre) as nombre_articulo
    from 
        boleta b 
        inner join persona p on b.idcliente = p.idpersona 
        inner join usuario u on b.idusuario = u.idusuario 
        inner join empresa e on b.idempresa = e.idempresa
        left join detalle_boleta_producto db on b.idboleta = db.idboleta 
        left join articulo a on db.idarticulo = a.idarticulo
    where
        date(b.fecha_emision_01) = current_date and e.idempresa = '$idempresa'
    group by
        b.idboleta
    order by
        b.idboleta desc;
    ";

    return ejecutarConsulta($sql);

  }



  //Implementar un método para listar los registros

  public function listarValidar($ano, $mes, $dia, $idempresa)
  {



    if ($mes == "'01','02','03','04','05','06','07','08','09','10', '11','12'") {

      $sql = "select 

        b.idboleta,

        date_format(b.fecha_emision_01,'%d/%m/%y') as fecha,

        b.idcliente,

        left(p.razon_social,20) as cliente,

        b.vendedorsitio,

        u.nombre as usuario,

        b.tipo_documento_06,

        b.numeracion_07,

        format(b.importe_total_23,2) as importe_total_23, 

        b.estado, 

        p.nombres, 

        p.apellidos,

        e.numero_ruc,

        p.email,

        TIMESTAMPDIFF(DAY,b.fecha_emision_01 ,  curdate()) AS diast,

        b.DetalleSunat,
        b.tarjetadc,
        b.montotarjetadc,
        b.transferencia,
        b.montotransferencia,
        b.tipo_moneda_24 as  moneda,
        b.tcambio,
        (b.tcambio * importe_total_23) as valordolsol

        from 

        boleta b inner join persona p on b.idcliente=p.idpersona 

        inner join usuario u on b.idusuario=u.idusuario 

        inner join empresa e on b.idempresa=e.idempresa where

        year(fecha_emision_01)='$ano' and month(fecha_emision_01)  in($mes)  and e.idempresa='$idempresa'

        order by b.idboleta desc";





    } else if ($dia == '0') {



      $sql = "select 

        b.idboleta,

        date_format(b.fecha_emision_01,'%d/%m/%y') as fecha,

        b.idcliente,

        left(p.razon_social,20) as cliente,

        b.vendedorsitio,

        u.nombre as usuario,

        b.tipo_documento_06,

        b.numeracion_07,

        format(b.importe_total_23,2) as importe_total_23, 

        b.estado, 

        p.nombres, 

        p.apellidos,

        e.numero_ruc,

        p.email,

        TIMESTAMPDIFF(DAY,b.fecha_emision_01 ,  curdate()) AS diast,

        b.DetalleSunat,
        b.tarjetadc,
        b.montotarjetadc,
        b.transferencia,
        b.montotransferencia,
        b.tipo_moneda_24 as  moneda,
        b.tcambio,
        (b.tcambio * importe_total_23) as valordolsol

        from 

        boleta b inner join persona p on b.idcliente=p.idpersona 

        inner join usuario u on b.idusuario=u.idusuario 

        inner join empresa e on b.idempresa=e.idempresa where

        year(fecha_emision_01)='$ano' and month(fecha_emision_01)='$mes'  and e.idempresa='$idempresa'

        order by b.idboleta desc";



    } else {



      $sql = "select 

        b.idboleta,

        date_format(b.fecha_emision_01,'%d/%m/%y') as fecha,

        b.idcliente,

        left(p.razon_social,20) as cliente,

        b.vendedorsitio,

        u.nombre as usuario,

        b.tipo_documento_06,

        b.numeracion_07,

        format(b.importe_total_23,2) as importe_total_23, 

        b.estado, 

        p.nombres, 

        p.apellidos,

        e.numero_ruc,

        p.email,

        TIMESTAMPDIFF(DAY,b.fecha_emision_01 ,  curdate()) AS diast,

        b.DetalleSunat,
        b.tarjetadc,
        b.montotarjetadc,
        b.transferencia,
        b.montotransferencia,
        b.tipo_moneda_24 as  moneda,
        b.tcambio,
        (b.tcambio * importe_total_23) as valordolsol

        from 

        boleta b inner join persona p on b.idcliente=p.idpersona 

        inner join usuario u on b.idusuario=u.idusuario 

        inner join empresa e on b.idempresa=e.idempresa where

        year(fecha_emision_01)='$ano' and month(fecha_emision_01)='$mes' and day(fecha_emision_01)='$dia' and e.idempresa='$idempresa'

        order by b.idboleta desc";





    }

    return ejecutarConsulta($sql);

  }





  public function ventacabecera($idboleta, $idempresa)
  {
    $sql = "select 
        b.idboleta, 
        b.idcliente, 
        p.razon_social,
        p.nombre_comercial, 
        p.nombres as cliente, 
        p.domicilio_fiscal as direccion, 
        p.tipo_documento,
        p.numero_documento, 
        p.email, 
        p.telefono1, 
        b.idusuario, 
        u.nombre as usuario, 
        b.tipo_documento_06, 
        b.numeracion_07,
        right(substring_index(b.numeracion_07,'-',1),4) as serie, 
        b.numeracion_07 as numerofac,  
        date_format(b.fecha_emision_01,'%d-%m-%Y') as fecha, 
        date_format(b.fecha_emision_01,'%Y-%m-%d') as fecha2, 
        date_format(b.fecha_emision_01, '%H:%i:%s') as hora, 
        b.importe_total_23 as totalLetras, 
        b.importe_total_23 as Itotal, 
        b.estado,
        e.numero_ruc,
        b.tdescuento,
        b.descripcion_leyenda_26_2,
        b.guia_remision_25 as guia,
        b.vendedorsitio,
        b.monto_15_2 as subtotal,
        b.sumatoria_igv_18_1 as igv,
        b.nombre_tributo_18_4 as nombretrib,
        b.ipagado,
        b.saldo,
        b.tipopago,
        b.efectivo,
        b.visa,
        b.yape,
        b.plin,
        b.mastercard,
        b.deposito,
        b.nroreferencia,
        b.icbper,
        b.tipo_moneda_24 as moneda,
        b.hashc,
        GROUP_CONCAT(lpad(cu.ncuota,3,'0'),'|',cu.montocuota,'|',date_format(cu.fechacuota, '%Y-%m-%d')) as cuotas
        FROM
            boleta b
            INNER JOIN persona p ON b.idcliente = p.idpersona
            INNER JOIN usuario u ON b.idusuario = u.idusuario
            INNER JOIN empresa e ON b.idempresa = e.idempresa
            INNER JOIN cuotas cu ON cu.idcomprobante = b.idboleta
        WHERE
            b.idboleta = '$idboleta' AND e.idempresa = '$idempresa' and cu.tipocomprobante = '03'
        GROUP BY b.idboleta";
    return ejecutarConsulta($sql);

  }



  public function ventadetalle($idboleta)
  {

    $sql = "select  
        a.nombre as articulo, 
        a.codigo, 
        format(db.cantidad_item_12,2) as cantidad_item_12, 
        db.valor_uni_item_31, 
        db.precio_uni_item_14_2, 
        db.valor_venta_item_32, 
        format((db.cantidad_item_12 * db.precio_uni_item_14_2),2) as subtotal,
        db.dcto_item, 
        db.descdet,
        um.nombreum as umedidacompra,
        afectacion_igv_5 as nombretribu,
        db.precio_uni_item_14_2 as precio,
        format(db.valor_venta_item_32,2) as subtotal2,
        db.umedida,
        db.numero_orden_item_29 as norden
        from

        detalle_boleta_producto db inner join articulo a on db.idarticulo=a.idarticulo  inner join umedida um on a.umedidacompra=um.idunidad

        where 

        db.idboleta='$idboleta'";

    return ejecutarConsulta($sql);

  }



  public function listarDR($ano, $mes, $idempresa)
  {

    $sql = "

        select 

        idboleta,

        idcliente,

        numeracion_07 as numeroboleta,

        date_format(fecha_emision_01,'%d/%m/%y') as fecha,

        date_format(fecha_baja,'%d/%m/%y') as fechabaja,

        left(razon_social,20) as cliente,

        numero_documento as ruccliente,

        monto_15_2 as opgravada,        

        sumatoria_igv_18_1 as igv,

        format(importe_total_23,2) as total,

        vendedorsitio,

        estado 

        from 

        (select 

        b.idboleta,

        b.idcliente,

        b.numeracion_07,

        b.fecha_emision_01,

        b.fecha_baja,

        p.razon_social,

        p.numero_documento,

        b.monto_15_2,        

        b.sumatoria_igv_18_1,

        b.importe_total_23,

        b.vendedorsitio,

        b.estado 

        from 

        boleta b inner join persona p on b.idcliente=p.idpersona 

        inner join usuario u on b.idusuario=u.idusuario 

        inner join empresa e on b.idempresa=e.idempresa where year(b.fecha_emision_01)='$ano' and month(b.fecha_emision_01)='$mes' and b.estado in ('0','3') and e.idempresa='$idempresa'

        union all 

        select 

        b.idboleta,

        b.idcliente,

        b.numeracion_07,

        b.fecha_emision_01,

        b.fecha_baja,

        p.razon_social,

        p.numero_documento,

        b.monto_15_2,        

        b.sumatoria_igv_18_1,

        b.importe_total_23,

        b.vendedorsitio,

        b.estado 

        from 

        boletaservicio b inner join persona p on b.idcliente=p.idpersona 

        inner join usuario u on b.idusuario=u.idusuario 

        inner join empresa e on b.idempresa=e.idempresa where year(b.fecha_emision_01)='$ano' and month(b.fecha_emision_01)='$mes' and b.estado in ('0','3') and e.idempresa='$idempresa') as tabla

        order by idboleta desc";

    return ejecutarConsulta($sql);

  }



  public function listarD()
  {

    $sql = "select 

        documento 

        from 

        correlativo 

        where 

        documento='factura' or documento='boleta' or documento='nota de credito'or documento='nota de debito' group by documento";

    return ejecutarConsulta($sql);

  }

  public function EnviarBoletaWhatsap($idboleta, $numeracion_07)
  {
    $sql = "SELECT idboleta, numeracion_07 FROM boleta WHERE idboleta = $idboleta AND numeracion_07 = '$numeracion_07'";
    return ejecutarConsulta($sql);
  }



  public function datosemp($idempresa)
  {
    $sql = "select * from empresa where idempresa='$idempresa'";
    return ejecutarConsulta($sql);
  }



  //Implementamos un método para dar de baja a factura

  public function ActualizarEstado($idboleta, $st)
  {
    // SEGURIDAD: Usar prepared statement para prevenir SQL Injection
    $sw = true;
    $sqlestado = "UPDATE boleta SET estado=? WHERE idboleta=?";
    ejecutarConsultaPreparada($sqlestado, "si", [$st, $idboleta]) or $sw = false;
    return $sw;

  }



  public function mostrarultimocomprobante($idempresa)
  {

    $sql = "select numeracion_07 from boleta b inner join empresa e on b.idempresa=e.idempresa  where e.idempresa='$idempresa'  order by idboleta desc limit 1";

    return ejecutarConsultaSimpleFila($sql);

  }



  public function mostrarultimocomprobanteId($idempresa)
  {

    $sql = "select b.idboleta, e.tipoimpresion from boleta b inner join empresa e on b.idempresa=e.idempresa  where e.idempresa='$idempresa'  order by idboleta desc limit 1";

    return ejecutarConsultaSimpleFila($sql);

  }



  public function downftp($idboleta, $idempresa)
  {



    //Inclusion de la tabla RUTAS

    require_once "../modelos/Rutas.php";

    $rutas = new Rutas();

    $Rrutas = $rutas->mostrar2($idempresa);

    $Prutas = $Rrutas->fetch_object();

    $rutadata = $Prutas->rutadata; // ruta de la carpeta data



    $connect = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

    mysqli_query($connect, 'SET NAMES "' . DB_ENCODE . '"');

    //Si tenemos un posible error en la conexión lo mostramos

    if (mysqli_connect_errno()) {

      printf("Falló conexión a la base de datos: %s\n", mysqli_connect_error());

      exit();

    }



    $sql = "select 

        b.idboleta, 

        p.email,  

        p.nombres, 

        p.apellidos, 

        p.nombre_comercial, 

        e.numero_ruc,

        b.tipo_documento_06,

        b.numeracion_07 

        from 

        boleta b inner join persona p on 

        b.idcliente=p.idpersona inner join empresa e on 

        b.idempresa=e.idempresa 

        where 

        b.idboleta='$idboleta' and e.idempresa='$idempresa'";

    $result = mysqli_query($connect, $sql);

    $con = 0;

    while ($row = mysqli_fetch_assoc($result)) {

      for ($i = 0; $i <= count($result); $i++) {

        $correocliente = $row["email"];

      }

      //Agregar=====================================================

      // Ruta del directorio donde están los archivos

      $path = $rutadata;

      $files = array_diff(scandir($path), array('.', '..'));

      //=============================================================

      $boletaData = $row['numero_ruc'] . "-" . $row['tipo_documento_06'] . "-" . $row['numeracion_07'];

      //Validar si existe el archivo firmado

      foreach ($files as $file) {

        // Divides en dos el nombre de tu archivo utilizando el . 

        $dataSt = explode(".", $file);

        // Nombre del archivo

        $fileName = $dataSt[0];

        $st = "1";

        // Extensión del archivo 

        $fileExtension = $dataSt[1];

        if ($boletaData == $fileName) {

          $archivoBoletaData = $fileName;

          // Realizamos un break para que el ciclo se interrumpa

          break;

        }

      }

      $cabext = $rutadata . $archivoBoletaData . '.json';

      // $cabext=$rutadata.$archivoFacturaData.'.cab';

      // $detext=$rutadata.$archivoFacturaData.'.det';

      // $leyext=$rutadata.$archivoFacturaData.'.ley';

      // $triext=$rutadata.$archivoFacturaData.'.tri';



      // $ficheroData = file_get_contents($url);



      $cab = $archivoBoletaData . '.json';

      // $cab=$archivoFacturaData.'.cab';

      // $det=$archivoFacturaData.'.det';

      // $ley=$archivoFacturaData.'.ley';

      // $tri=$archivoFacturaData.'.tri';



      $rpta = array(
        'cabext' => $cabext,
        'cab' => $cab

        // 'detext'=>$detext, 'det'=>$det,

        // 'leyext'=>$leyext, 'ley'=>$ley,

        // 'triext'=>$triext, 'tri'=>$tri

      );



      return $rpta;



      $i = $i + 1;

      $con = $con + 1;

    }

  }


  //Implementar un método para mostrar los datos de un registro a modificar

  public function enviarcorreo($idboleta, $ema)
  {
    require_once "../modelos/Factura.php";
    $factura = new Factura();
    $datos = $factura->correo();
    $correo = $datos->fetch_object();
    //Inclusion de la tabla RUTAS
    require_once "../modelos/Rutas.php";
    $rutas = new Rutas();
    $Rrutas = $rutas->mostrar2($idempresa);
    $Prutas = $Rrutas->fetch_object();
    $rutafirma = $Prutas->rutafirma; // ruta de la carpeta FIRMA

    $connect = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    mysqli_query($connect, 'SET NAMES "' . DB_ENCODE . '"');
    //Si tenemos un posible error en la conexión lo mostramos
    if (mysqli_connect_errno()) {
      printf("Falló conexión a la base de datos: %s\n", mysqli_connect_error());
      exit();
    }
    $sqlsendmail = "select 
        b.idboleta, 
        p.email,  
        p.nombres, 
        p.apellidos, 
        p.nombre_comercial, 
        e.numero_ruc,
        b.tipo_documento_06,
        b.numeracion_07 
        from 
        boleta b inner join persona p on 
        b.idcliente=p.idpersona inner join empresa e on 
        b.idempresa=e.idempresa 
        where 
        b.idboleta='$idboleta'";
    $result = mysqli_query($connect, $sqlsendmail);
    //$variable=array();
    $con = 0;
    while ($row = mysqli_fetch_assoc($result)) {
      for ($i = 0; $i <= count($result); $i++) {
        $correocliente = $row["email"];
      }
      //Agregar=====================================================
      // Ruta del directorio donde están los archivos
      $path = $rutafirma;
      $pathBoleta = '../boletasPDF/';
      // Arreglo con todos los nombres de los archivos
      $files = array_diff(scandir($path), array('.', '..'));
      $filesBoleta = array_diff(scandir($pathBoleta), array('.', '..'));
      //=============================================================
      $boleta = $row['numero_ruc'] . "-" . $row['tipo_documento_06'] . "-" . $row['numeracion_07'];
      //Validar si existe el archivo firmado
      foreach ($files as $file) {
        // Divides en dos el nombre de tu archivo utilizando el . 
        $dataSt = explode(".", $file);
        // Nombre del archivo
        $fileName = $dataSt[0];
        $st = "1";
        // Extensión del archivo 
        $fileExtension = $dataSt[1];
        if ($boleta == $fileName) {
          $archivoBoleta = $fileName;
          // Realizamos un break para que el ciclo se interrumpa
          break;
        }
      }
      //==========================================================================
      //Validar si existe el archivo firmado
      foreach ($filesBoleta as $fileBoleta) {
        // Divides en dos el nombre de tu archivo utilizando el . 
        $dataStBoleta = explode(".", $fileBoleta);
        // Nombre del archivo
        $fileNameBoleta = $dataStBoleta[0];
        // Extensión del archivo 
        $fileExtensionBoleta = $dataStBoleta[1];
        if ($row['numeracion_07'] == $fileNameBoleta) {
          $archivoBoletaPdf = $fileNameBoleta;
          // Realizamos un break para que el ciclo se interrumpa
          break;
        }
      }
      $url = $rutafirma . $archivoBoleta . '.xml';
      $fichero = file_get_contents($url);
      $urlBoleta = '../boletasPDF/' . $archivoBoletaPdf . '.pdf';
      $ficheroBoleta = file_get_contents($urlBoleta);
      // FUNCION PARA ENVIO DE CORREO CON LA FACTURA AL CLIENTE .
      require '../correo/PHPMailer/class.phpmailer.php';
      require '../correo/PHPMailer/class.smtp.php';
      $mail = new PHPMailer;
      $mail->isSMTP(); // Establecer el correo electrónico para utilizar SMTP
      $mail->Host = $correo->host; // Especificar el servidor de correo a utilizar 
      $mail->SMTPAuth = true; // Habilitar la autenticacion con SMTP
      $mail->Username = $correo->username; // Correo electronico saliente ejemplo: tucorreo@gmail.com
      //$clavehash=hash("SHA256",$correo->password);
      $mail->Password = $correo->password; // Tu contraseña de gmail
      $mail->SMTPSecure = $correo->smtpsecure; // Habilitar encriptacion, `ssl` es aceptada
      $mail->Port = $correo->port; // Puerto TCP  para conectarse 
      $mail->setFrom($correo->username, utf8_decode($correo->nombre)); //Introduzca la dirección de la que debe aparecer el correo electrónico. Puede utilizar cualquier dirección que el servidor SMTP acepte como válida. El segundo parámetro opcional para esta función es el nombre que se mostrará como el remitente en lugar de la dirección de correo electrónico en sí.
      $mail->addReplyTo($correo->username, utf8_decode($correo->nombre)); //Introduzca la dirección de la que debe responder. El segundo parámetro opcional para esta función es el nombre que se mostrará para responder
      $mail->addStringAttachment($fichero, $archivoBoleta . '.xml');
      $mail->addStringAttachment($ficheroBoleta, $archivoBoletaPdf . '.pdf');
      $mail->addAddress($ema); // Agregar quien recibe el e-mail enviado
      //$mail->addAttachment();
      $message = file_get_contents('../correo/email_template.html');
      $message = str_replace('{{first_name}}', utf8_decode($correo->nombre), utf8_decode($correo->mensaje));
      $message = str_replace('{{message}}', utf8_decode($correo->mensaje), utf8_decode($correo->mensaje));
      $message = str_replace('{{customer_email}}', $correo->username, utf8_decode($correo->mensaje));
      $mail->isHTML(true); // Establecer el formato de correo electrónico en HTML
      $mail->Subject = $correo->username;
      $mail->msgHTML($message);
      //$mail->send();
      if (!$mail->send()) {
        //echo '<p style="color:red">No se pudo enviar el mensaje..';
        echo $mail->ErrorInfo;
        //echo "</p>";
      } else {
        echo 'Tu mensaje ha sido enviado';
      }
      // FUNCION PARA ENVIO DE CORREO CON LA FACTURA AL CLIENTE .
      $i = $i + 1;
      $con = $con + 1;
    }
    //Guardar en tabla envicorreo =========================================
    $sql = "insert into 
        enviocorreo
         (  
            numero_documento,
            cliente, 
            correo, 
            comprobante, 
            fecha_envio
          )
          values
          (        
          (select numero_documento from boleta b inner join persona p on b.idcliente=p.idpersona where b.idboleta='$idboleta'),
          (select razon_social from boleta b inner join persona p on b.idcliente=p.idpersona where b.idboleta='$idboleta'),
          (select email from boleta b inner join persona p on b.idcliente=p.idpersona where b.idboleta='$idboleta'),
          (select numeracion_07 from boleta b inner join persona p on b.idcliente=p.idpersona where b.idboleta='$idboleta'),
          now()
        )";
    //return ejecutarConsulta($sql);
    $enviarcorreo = ejecutarConsulta($sql);

    //Guardar en tabla envicorreo =========================================

  }







  public function generarxml($idboleta, $idempresa)
  {

    $connect = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

    mysqli_query($connect, 'SET NAMES "' . DB_ENCODE . '"');

    //Si tenemos un posible error en la conexión lo mostramos

    if (mysqli_connect_errno()) {

      printf("Falló conexión a la base de datos: %s\n", mysqli_connect_error());

      exit();

    }



    require_once "../modelos/Factura.php";
    $factura = new Factura();
    $datos = $factura->datosemp($idempresa);
    $configuraciones = $factura->configuraciones($idempresa);
    $configE = $configuraciones->fetch_object();
    $datose = $datos->fetch_object();

    //Inclusion de la tabla RUTAS
    require_once "../modelos/Rutas.php";
    $rutas = new Rutas();
    $Rrutas = $rutas->mostrar2($idempresa);
    $Prutas = $Rrutas->fetch_object();
    $rutadata = $Prutas->rutadata; // ruta de la carpeta DATA
    $rutafirma = $Prutas->rutafirma; // ruta de la carpeta FIRMA
    $rutadatalt = $Prutas->rutadatalt; // ruta de la carpeta DATAALTERNA
    $rutaenvio = $Prutas->rutaenvio; // ruta de la carpeta DATAALTERNA



    $query = "select
     date_format(b.fecha_emision_01, '%Y-%m-%d') as fecha, 
     right(substring_index(b.numeracion_07,'-',1),1) as serie,
     date_format(b.fecha_emision_01, '%H:%i:%s') as hora,
     p.tipo_documento as  tipodocuCliente, 
     p.numero_documento, 
     p.razon_social, 
     b.tipo_moneda_24, 
     b.monto_15_2 as subtotal, 
     b.sumatoria_igv_18_1 as igv, 
     b.importe_total_23 as total, 
     b.tipo_documento_06 as tipocomp, 
     b.numeracion_07 as numerodoc, 
     b.estado, 
     b.tdescuento,
     b.codigo_tributo_18_3 as codigotrib,
     b.nombre_tributo_18_4  as nombretrib,
     b.codigo_internacional_18_5 as codigointtrib,
     b.codigo_tipo_15_1 as opera,
     e.ubigueo,
     b.icbper,

     b.formapago,
     b.montofpago,
     b.monedafpago,
     b.ccuotas,
     b.fechavecredito,
     b.montocuota,
     b.fechavenc
     
     from 
     boleta b inner join persona p on b.idcliente=p.idpersona 
     inner join empresa e on b.idempresa=e.idempresa 
     where
    idboleta='$idboleta' and b.estado in('1','4') order by numerodoc";


    $querycuotas = "select 
     lpad(cu.ncuota,3,'0') as ncuota ,
     cu.montocuota,
     date_format(cu.fechacuota, '%Y-%m-%d') as fechacuota,
     format(b.formapago,2) as formapago,
     b.tipo_moneda_24 as monedaf
     from 
     cuotas cu inner join boleta b on cu.idcomprobante=b.idboleta
     where idcomprobante='$idboleta' and cu.tipocomprobante='03'";



    $querydetbol = "select
       b.tipo_documento_06 as tipocomp, 
       b.numeracion_07 as numerodoc,  
       db.cantidad_item_12 as cantidad, 
       a.codigo, 
       a.nombre as descripcion, 
       um.abre as um,
       replace(format(db.valor_uni_item_31,5),',','') as vui, 
       db.igv_item as igvi, 
       db.precio_uni_item_14_2 as pvi, 
       db.valor_venta_item_32 as vvi,
       db.afectacion_igv_item_monto_27_1 as sutribitem,
       db.numero_orden_item_29 as numorden,

       db.afectacion_igv_3 as aigv,
       db.afectacion_igv_4 codtrib,
       db.afectacion_igv_5 as nomtrib,
       db.afectacion_igv_6 as coditrib,
       a.codigosunat,
       b.tipo_moneda_24 as moneda,
       a.mticbperu,
       b.icbper,
       db.umedida
       from
       boleta b inner join detalle_boleta_producto db on b.idboleta=db.idboleta inner join articulo a on db.idarticulo=a.idarticulo inner join umedida um on a.unidad_medida=um.idunidad

          where 

          b.idboleta='$idboleta' and b.estado in ('1','4') order by b.fecha_emision_01";

    $result = mysqli_query($connect, $query);
    $resultb = mysqli_query($connect, $querydetbol);
    $resultcuotas = mysqli_query($connect, $querycuotas);

    $nombrecomercial = $datose->nombre_comercial;
    $domiciliofiscal = $datose->domicilio_fiscal;
    $codestablecimiento = $datose->ubigueo;
    $codubigueo = $datose->codubigueo;
    $ciudad = $datose->ciudad;
    $distrito = $datose->distrito;
    $interior = $datose->interior;
    $codigopais = $datose->codigopais;

    //Parametros de salida

    $fecha = "";
    $hora = "";
    $serie = "";
    $tipodocu = "";
    $numdocu = "";
    $rasoc = "";
    $moneda = "";
    $codigotrib = "";
    $nombretrib = "";
    $codigointtrib = "";
    $subtotal = "";
    $igv = "";
    $total = "";
    $tdescu = "";
    $opera = "";
    $ubigueo = "";


    $formapago = "";
    $montofpago = "";
    $monedafpago = "";
    $ccuotas = "";
    $fechavecredito = "";
    $montocuota = "";





    $con = 0; //COntador de variable

    $icbper = "";



    while ($row = mysqli_fetch_assoc($result)) {

      //for($i=0; $i <= count($result); $i++){
      $fecha = $row["fecha"]; //Fecha emision
      $serie = $row["serie"];
      $tipodocu = $row["tipodocuCliente"]; //Tipo de documento de cliente ruc o dni
      $numdocu = $row["numero_documento"]; //NUmero de docuemnto de cliente
      $rasoc = $row["razon_social"]; //Nombre de cliente
      $moneda = $row["tipo_moneda_24"];
      $subtotal = $row["subtotal"];
      $igv = $row["igv"];
      $total = $row["total"];
      $tdescu = $row["tdescuento"];
      $hora = $row["hora"];
      $tipocomp = $row["tipocomp"];
      $numerodoc = $row["numerodoc"];
      $ruc = $datose->numero_ruc;
      $ubigueo = $datose->ubigueo;
      $opera = $row["opera"];

      $codigotrib = $row["codigotrib"]; //codigo de tributo de la tabla catalo 5
      $nombretrib = $row["nombretrib"]; //NOmbre de tributo de la tabla catalo 5
      $codigointtrib = $row["codigointtrib"]; //Codigo internacional de la tabla catalo 5

      $formapago = $row["formapago"];
      $montofpago = $row["montofpago"];
      $monedafpago = $row["monedafpago"];
      $ccuotas = $row["ccuotas"];
      $fechavecredito = $row["fechavecredito"];
      $montocuota = $row["montocuota"];



      $icbper = $row["icbper"];


      if ($moneda == 'USD') {
        $Lmoneda = "DOLARES AMERICANOS";
      }

      $Lmoneda = "NUEVOS SOLES";

      require_once "Letras.php";

      $V = new EnLetras();
      $con_letra = strtoupper($V->ValorEnLetras($total, $Lmoneda));

      //======================================== FORMATO XML ========================================================

      //Primera parte

      $boletaXML = '<?xml version="1.0" encoding="utf-8"?>
            <Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"
                     xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
                     xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
                     xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
                     xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2">
                <ext:UBLExtensions>
                    <ext:UBLExtension>
                        <ext:ExtensionContent/>
                    </ext:UBLExtension>
                </ext:UBLExtensions>
                <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
                <cbc:CustomizationID>2.0</cbc:CustomizationID>
                <cbc:ID>' . $numerodoc . '</cbc:ID>
                <cbc:IssueDate>' . $fecha . '</cbc:IssueDate>
                <cbc:IssueTime>' . $hora . '</cbc:IssueTime>



                <cbc:InvoiceTypeCode listID="0101">' . $tipocomp . '</cbc:InvoiceTypeCode>
                <cbc:Note languageLocaleID="1000">' . $con_letra . '</cbc:Note>
              <cbc:Note languageLocaleID="2006">Leyenda: Operación sujeta a detracción</cbc:Note>
              <cbc:DocumentCurrencyCode>' . $moneda . '</cbc:DocumentCurrencyCode>


                <cac:Signature>

                    <cbc:ID>' . $ruc . '</cbc:ID>

                    <cbc:Note>SENCON</cbc:Note>

                    <cac:SignatoryParty>

                        <cac:PartyIdentification>

                            <cbc:ID>' . $ruc . '</cbc:ID>

                        </cac:PartyIdentification>

                        <cac:PartyName>

                            <cbc:Name><![CDATA[' . $nombrecomercial . ']]></cbc:Name>

                        </cac:PartyName>

                    </cac:SignatoryParty>

                    <cac:DigitalSignatureAttachment>

                        <cac:ExternalReference>

                            <cbc:URI>#SIGN-SENCON</cbc:URI>

                        </cac:ExternalReference>

                    </cac:DigitalSignatureAttachment>

                </cac:Signature>



                <cac:AccountingSupplierParty>

                    <cac:Party>

                        <cac:PartyIdentification>

                            <cbc:ID schemeID="6">' . $ruc . '</cbc:ID>

                        </cac:PartyIdentification>

                        <cac:PartyName>

                            <cbc:Name><![CDATA[' . $nombrecomercial . ']]></cbc:Name>

                        </cac:PartyName>

                      <cac:PartyLegalEntity>

                        <cbc:RegistrationName><![CDATA[' . $nombrecomercial . ']]></cbc:RegistrationName>

                           <cac:RegistrationAddress>

                             <cbc:AddressTypeCode>' . $codestablecimiento . '</cbc:AddressTypeCode>

                               <cbc:CitySubdivisionName>' . $interior . '</cbc:CitySubdivisionName>

                                <cbc:CityName>' . $ciudad . '</cbc:CityName>

                                  <cbc:CountrySubentity>' . $ciudad . '</cbc:CountrySubentity>

                                    <cbc:CountrySubentityCode>' . $codubigueo . '</cbc:CountrySubentityCode>

                                      <cbc:District>' . $distrito . '</cbc:District> 

                                      <cac:AddressLine>

                                        <cbc:Line><![CDATA[' . $domiciliofiscal . ']]></cbc:Line>

                                          </cac:AddressLine>    

                                            <cac:Country>

                                              <cbc:IdentificationCode>PE</cbc:IdentificationCode>

                                                </cac:Country>

                            </cac:RegistrationAddress>

                        </cac:PartyLegalEntity>

                    </cac:Party>

                </cac:AccountingSupplierParty>



                <cac:AccountingCustomerParty>

                    <cac:Party>

                        <cac:PartyIdentification>

                            <cbc:ID schemeID="' . $tipodocu . '">' . $numdocu . '</cbc:ID>

                        </cac:PartyIdentification>

                        <cac:PartyLegalEntity>

                            <cbc:RegistrationName><![CDATA[' . $rasoc . ']]></cbc:RegistrationName>

                        </cac:PartyLegalEntity>

                    </cac:Party>

                </cac:AccountingCustomerParty>';

      //    $boletaXML.='<cac:PaymentTerms>
      //   <cbc:ID>FormaPago</cbc:ID>
      // <cbc:PaymentMeansID>Contado</cbc:PaymentMeansID>
      //   </cac:PaymentTerms>';



      if ($formapago == 'Contado') {
        $boletaXML .= '<cac:PaymentTerms>
                <cbc:ID>FormaPago</cbc:ID>
                <cbc:PaymentMeansID>' . $formapago . '</cbc:PaymentMeansID>
                </cac:PaymentTerms>';

      } else { // SI ES AL CREDITO

        $boletaXML .= '<cac:PaymentTerms>
                <cbc:ID>FormaPago</cbc:ID>
                <cbc:PaymentMeansID>' . $formapago . '</cbc:PaymentMeansID>
                <cbc:Amount currencyID="' . $moneda . '">' . $total . '</cbc:Amount>
                </cac:PaymentTerms>';

        $ncuotacredito = array();
        $montocuotacredito = array();
        $fechacuotacredito = array();
        $formapagocre = array();
        $monedaf = array();

        while ($rowb = mysqli_fetch_assoc($resultcuotas)) {
          for ($i = 0; $i < count($resultcuotas); $i++) {
            $ncuotacredito[$i] = $rowb["ncuota"];
            $montocuotacredito[$i] = $rowb["montocuota"];
            $fechacuotacredito[$i] = $rowb["fechacuota"];
            $formapagocre[$i] = $rowb["formapago"];
            $monedaf[$i] = $rowb["monedaf"];

            $boletaXML .= '<cac:PaymentTerms>
                <cbc:ID>FormaPago</cbc:ID>
                <cbc:PaymentMeansID>Cuota' . $ncuotacredito[$i] . '</cbc:PaymentMeansID>
                <cbc:Amount currencyID="' . $monedaf[$i] . '">' . $montocuotacredito[$i] . '</cbc:Amount>
                <cbc:PaymentDueDate>' . $fechacuotacredito[$i] . '</cbc:PaymentDueDate>
                </cac:PaymentTerms>';
          }
          $i = $i + 1;
        }

      }




      $boletaXML .= '

                 <!-- Inicio Tributos cabecera-->  
                <cac:TaxTotal>
                    <cbc:TaxAmount currencyID="' . $moneda . '">' . $igv . '</cbc:TaxAmount>
                        <cac:TaxSubtotal>
                        <cbc:TaxableAmount currencyID="' . $moneda . '">' . $subtotal . '</cbc:TaxableAmount>
                        <cbc:TaxAmount currencyID="' . $moneda . '">' . $igv . '</cbc:TaxAmount>
                        <cac:TaxCategory>
                            <cac:TaxScheme>
                                <cbc:ID>' . $codigotrib . '</cbc:ID>
                                <cbc:Name>' . $nombretrib . '</cbc:Name>
                                <cbc:TaxTypeCode>' . $codigointtrib . '</cbc:TaxTypeCode>
                            </cac:TaxScheme>
                        </cac:TaxCategory>
                    </cac:TaxSubtotal>';





      if ($icbper > 0) {

        $boletaXML .= '
                        <cac:TaxSubtotal>
                  <cbc:TaxAmount currencyID="' . $moneda . '">' . $icbper . '</cbc:TaxAmount>
                         <cac:TaxCategory>
                            <cac:TaxScheme>
                               <cbc:ID>7152</cbc:ID>
                               <cbc:Name>ICBPER</cbc:Name>
                               <cbc:TaxTypeCode>OTH</cbc:TaxTypeCode>
                            </cac:TaxScheme>
                         </cac:TaxCategory>
                      </cac:TaxSubtotal>';

      }



      $boletaXML .= '

            <!-- Fin Tributos  Cabecera-->

              </cac:TaxTotal>



                <cac:LegalMonetaryTotal>

                    <cbc:LineExtensionAmount currencyID="' . $moneda . '">' . $subtotal . '</cbc:LineExtensionAmount>
                    <cbc:TaxInclusiveAmount currencyID="' . $moneda . '">' . $total . '</cbc:TaxInclusiveAmount>
                    <cbc:AllowanceTotalAmount currencyID="' . $moneda . '">0.00</cbc:AllowanceTotalAmount>
                    <cbc:ChargeTotalAmount currencyID="' . $moneda . '">0.00</cbc:ChargeTotalAmount>  
                    <cbc:PrepaidAmount currencyID="' . $moneda . '">0.00</cbc:PrepaidAmount>  
                    <cbc:PayableAmount currencyID="' . $moneda . '">' . $total . '</cbc:PayableAmount>

                </cac:LegalMonetaryTotal>';

      //}//For cabecera

      $i = $i + 1;

      $con = $con + 1;

    } //While cabecera



    $codigo = array();

    $cantidad = array();

    $descripcion = array();

    $um = array();

    $vui = array();

    $igvi = array();

    $pvi = array();

    $vvi = array();

    $sutribitem = array();

    $aigv = array();

    $codtrib = array();

    $nomtrib = array();

    $coditrib = array();

    $codigosunat = array();

    $numorden = array();

    $monedaD = array();

    $mticbperu = array();



    while ($rowb = mysqli_fetch_assoc($resultb)) {
      for ($ib = 0; $ib < count($resultb); $ib++) {
        $codigo[$ib] = $rowb["codigo"];
        $cantidad[$ib] = $rowb["cantidad"];
        $descripcion[$ib] = $rowb["descripcion"];
        $vui[$ib] = $rowb["vui"];
        $sutribitem[$ib] = $rowb["sutribitem"];
        $igvi[$ib] = $rowb["igvi"];
        $pvi[$ib] = $rowb["pvi"];
        $vvi[$ib] = $rowb["vvi"];
        $um[$ib] = $rowb["umedida"];
        $tipocompf = $rowb["tipocomp"];
        $numerodocf = $rowb["numerodoc"];
        $ruc = $datose->numero_ruc;
        $aigv[$ib] = $rowb["aigv"];
        $codtrib[$ib] = $rowb["codtrib"];
        $nomtrib[$ib] = $rowb["nomtrib"];
        $coditrib[$ib] = $rowb["coditrib"];
        $codigosunat[$ib] = $rowb["codigosunat"];
        $numorden[$ib] = $rowb["numorden"];
        $monedaD[$ib] = $rowb["moneda"];
        $mticbperu[$ib] = $rowb["mticbperu"];

        $icbperD = $rowb["icbper"];


        if ($codtrib[$ib] == '9997') {
          $igv_ = "0";

        } else {
          $igv_ = $configE->igv;
        }



        /* Número de orden del Ítem

           Cantidad y Unidad de medida por ítem

           Valor de venta del ítem  */



        $boletaXML .= '

                <cac:InvoiceLine>
                    <cbc:ID>' . $numorden[$ib] . '</cbc:ID>
                    <cbc:InvoicedQuantity unitCode="' . $um[$ib] . '">' . number_format($cantidad[$ib], 2, '.', '') . '</cbc:InvoicedQuantity>
                    <cbc:LineExtensionAmount currencyID="' . $monedaD[$ib] . '">' . number_format($vvi[$ib], 2, '.', '') . '</cbc:LineExtensionAmount>

                    

                    <cac:PricingReference>
                        <cac:AlternativeConditionPrice>
                            <cbc:PriceAmount currencyID="' . $monedaD[$ib] . '">' . number_format($pvi[$ib], 2, '.', '') . '</cbc:PriceAmount>
                            <cbc:PriceTypeCode>01</cbc:PriceTypeCode>
                        </cac:AlternativeConditionPrice>
                    </cac:PricingReference>



                    <cac:TaxTotal>

                        <cbc:TaxAmount currencyID="' . $monedaD[$ib] . '">' . number_format($sutribitem[$ib], 2, '.', '') . '</cbc:TaxAmount>                        

                        <cac:TaxSubtotal>
                            <cbc:TaxableAmount currencyID="' . $monedaD[$ib] . '">' . number_format($vvi[$ib], 2, '.', '') . '</cbc:TaxableAmount>
                            <cbc:TaxAmount currencyID="' . $monedaD[$ib] . '">' . number_format($sutribitem[$ib], 2, '.', '') . '</cbc:TaxAmount>
                            <cac:TaxCategory>
                                <cbc:Percent>' . $igv_ . '</cbc:Percent>
                                <cbc:TaxExemptionReasonCode>' . $aigv[$ib] . '</cbc:TaxExemptionReasonCode>
                                <cac:TaxScheme>
                                    <cbc:ID>' . $codtrib[$ib] . '</cbc:ID>
                                    <cbc:Name>' . $nomtrib[$ib] . '</cbc:Name>
                                    <cbc:TaxTypeCode>' . $coditrib[$ib] . '</cbc:TaxTypeCode>
                                </cac:TaxScheme>
                            </cac:TaxCategory>
                        </cac:TaxSubtotal>';







        if ($codigo[$ib] == "ICBPER") {



          $boletaXML .= '



                <cac:TaxSubtotal>
                    <cbc:TaxAmount currencyID="' . $moneda[$ib] . '">' . $icbperD . '</cbc:TaxAmount>
                    <cbc:BaseUnitMeasure unitCode="' . $um[$ib] . '">' . number_format($cantidad[$ib], 0, '.', '') . '</cbc:BaseUnitMeasure>
                    <cac:TaxCategory>
                    <cbc:PerUnitAmount currencyID="' . $moneda[$ib] . '">' . number_format($mticbperu[$ib], 2, '.', '') . '</cbc:PerUnitAmount>
                       <cac:TaxScheme>
                          <cbc:ID>7152</cbc:ID>
                          <cbc:Name>ICBPER</cbc:Name>
                          <cbc:TaxTypeCode>OTH</cbc:TaxTypeCode>
                       </cac:TaxScheme>
                    </cac:TaxCategory>
                 </cac:TaxSubtotal>';

        }
        ;





        $boletaXML .= '

                     </cac:TaxTotal>
                    <cac:Item>
                        <cbc:Description><![CDATA[' . $descripcion[$ib] . ']]></cbc:Description>
                        <cac:SellersItemIdentification>
                            <cbc:ID>' . $codigo[$ib] . '</cbc:ID>
                        </cac:SellersItemIdentification>
                    </cac:Item>



                    <cac:Price>
                        <cbc:PriceAmount currencyID="' . $monedaD[$ib] . '">' . number_format($vui[$ib], 5, '.', '') . '</cbc:PriceAmount>
                    </cac:Price>
                </cac:InvoiceLine>';



      } //Fin for

    } //Find e while 

    $boletaXML .= '</Invoice>';

    //FIN DE CABECERA ===================================================================





    // Nos aseguramos de que la cadena que contiene el XML esté en UTF-8

    $boletaXML = mb_convert_encoding($boletaXML, "UTF-8");

    // Grabamos el XML en el servidor como un fichero plano, para

    // poder ser leido por otra aplicación.

    $gestor = fopen($rutafirma . $ruc . "-" . $tipocomp . "-" . $numerodoc . ".xml", 'w');
    fwrite($gestor, $boletaXML);
    fclose($gestor);



    $cabextxml = $rutafirma . $ruc . "-" . $tipocomp . "-" . $numerodoc . ".xml";
    $cabxml = $ruc . "-" . $tipocomp . "-" . $numerodoc . ".xml";
    $nomxml = $ruc . "-" . $tipocomp . "-" . $numerodoc;
    $nomxmlruta = $rutafirma . $ruc . "-" . $tipocomp . "-" . $numerodoc;

    require_once("../greemter/Greenter.php");
    $invo = new Greenter();
    $out = $invo->getDatFac($cabextxml);
    $filenaz = $nomxml . ".zip";
    $zip = new ZipArchive();
    if ($zip->open($filenaz, ZIPARCHIVE::CREATE) === true) {
      $zip->addFile($cabextxml, $cabxml);
      $zip->close();

      $imagen = file_get_contents($filenaz);
      $imageData = base64_encode($imagen);
      rename($cabextxml, $rutafirma . $cabxml);
      rename($filenaz, $rutaenvio . $filenaz);

    } else {

      $out = "Error al comprimir archivo";

    }



    $data[0] = "";
    $sxe = new SimpleXMLElement($cabextxml, null, true);
    $urn = $sxe->getNamespaces(true);
    $sxe->registerXPathNamespace('ds', $urn['ds']);
    $data = $sxe->xpath('//ds:DigestValue');

    $rpta = array('cabextxml' => $cabextxml, 'cabxml' => $cabxml, 'rutafirma' => $cabextxml);
    $sqlDetalle = "update boleta set DetalleSunat='XML firmado'  , hashc='$data[0]', 
            estado='4'  where idboleta='$idboleta'";
    ejecutarConsulta($sqlDetalle);



    return $rpta;



  } //Fin de funcion





  public function generarxmlEA($ano, $mes, $dia, $idboleta, $estado, $check, $idempresa)
  {

    $connect = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

    mysqli_query($connect, 'SET NAMES "' . DB_ENCODE . '"');

    //Si tenemos un posible error en la conexión lo mostramos

    if (mysqli_connect_errno()) {

      printf("Falló conexión a la base de datos: %s\n", mysqli_connect_error());

      exit();

    }



    require_once "../modelos/Factura.php";

    $factura = new Factura();

    $datos = $factura->datosemp($idempresa);

    $configuraciones = $factura->configuraciones($idempresa);

    $configE = $configuraciones->fetch_object();

    $datose = $datos->fetch_object();







    //Inclusion de la tabla RUTAS

    require_once "../modelos/Rutas.php";

    $rutas = new Rutas();

    $Rrutas = $rutas->mostrar2($idempresa);

    $Prutas = $Rrutas->fetch_object();

    $rutadata = $Prutas->rutadata; // ruta de la carpeta DATA

    $rutafirma = $Prutas->rutafirma; // ruta de la carpeta FIRMA

    $rutadatalt = $Prutas->rutadatalt; // ruta de la carpeta DATAALTERNA

    $rutaenvio = $Prutas->rutaenvio; // ruta de la carpeta DATAALTERNA



    if ($estado == '1' && $estado == '4' || $check == 'true') {



      $query = "select

     date_format(b.fecha_emision_01, '%Y-%m-%d') as fecha, 

     right(substring_index(b.numeracion_07,'-',1),1) as serie,

     date_format(b.fecha_emision_01, '%H:%i:%s') as hora,

     p.tipo_documento as  tipodocuCliente, 

     p.numero_documento, 

     p.razon_social, 

     b.tipo_moneda_24, 

     b.monto_15_2 as subtotal, 

     b.sumatoria_igv_18_1 as igv, 

     b.importe_total_23 as total, 

     b.tipo_documento_06 as tipocomp, 

     b.numeracion_07 as numerodoc, 

     b.estado, 

     b.tdescuento,

     b.codigo_tributo_18_3 as codigotrib,

     b.nombre_tributo_18_4  as nombretrib,

     b.codigo_internacional_18_5 as codigointtrib,

     b.codigo_tipo_15_1 as opera,

     e.ubigueo,

     b.icbper

     from 

     boleta b inner join persona p on b.idcliente=p.idpersona inner join empresa e on b.idempresa=e.idempresa where 

     year(b.fecha_emision_01)='$ano' and month(b.fecha_emision_01)='$mes' and day(b.fecha_emision_01)='$dia' and b.estado ='$estado' and b.idboleta='$idboleta' order by numerodoc";



      $querydetbol = "select

       b.tipo_documento_06 as tipocomp, 

       b.numeracion_07 as numerodoc,  

       db.cantidad_item_12 as cantidad, 

       a.codigo, 

       a.nombre as descripcion, 

       um.abre as um,

       replace(format(db.valor_uni_item_31,5),',','') as vui, 

       db.igv_item as igvi, 

       db.precio_uni_item_14_2 as pvi, 

       db.valor_venta_item_32 as vvi,

       db.afectacion_igv_item_monto_27_1 as sutribitem,

       db.numero_orden_item_29 as numorden,



       db.afectacion_igv_3 as aigv,

       db.afectacion_igv_4 codtrib,

       db.afectacion_igv_5 as nomtrib,

       db.afectacion_igv_6 as coditrib,

       a.codigosunat,

       b.tipo_moneda_24 as moneda,

       a.mticbperu,

       b.icbper



       from

       boleta b inner join detalle_boleta_producto db on b.idboleta=db.idboleta inner join articulo a on db.idarticulo=a.idarticulo inner join umedida um on a.unidad_medida=um.idunidad

          where

          year(b.fecha_emision_01)='$ano' and month(b.fecha_emision_01)='$mes' and day(b.fecha_emision_01)='$dia' and b.estado ='$estado' and b.idboleta='$idboleta' order by b.fecha_emision_01";





      $result = mysqli_query($connect, $query);

      $resultb = mysqli_query($connect, $querydetbol);



      $nombrecomercial = $datose->nombre_comercial;

      $domiciliofiscal = $datose->domicilio_fiscal;

      $codestablecimiento = $datose->ubigueo;

      $codubigueo = $datose->codubigueo;

      $ciudad = $datose->ciudad;

      $distrito = $datose->distrito;

      $interior = $datose->interior;

      $codigopais = $datose->codigopais;









      //Parametros de salida

      $fecha = array();

      $hora = array();

      $serie = array();

      $tipodocu = array();

      $numdocu = array();

      $rasoc = array();

      $moneda = array();

      $codigotrib = array();

      $nombretrib = array();

      $codigointtrib = array();

      $subtotal = array();

      $igv = array();

      $total = array();

      $tdescu = array();

      $opera = array();

      $ubigueo = array();





      $con = 0; //COntador de variable

      $icbper = "";



      while ($row = mysqli_fetch_assoc($result)) {

        for ($i = 0; $i <= count($result); $i++) {

          $fecha[$i] = $row["fecha"]; //Fecha emision

          $serie[$i] = $row["serie"];

          $tipodocu[$i] = $row["tipodocuCliente"]; //Tipo de documento de cliente ruc o dni

          $numdocu[$i] = $row["numero_documento"]; //NUmero de docuemnto de cliente

          $rasoc[$i] = $row["razon_social"]; //Nombre de cliente

          $moneda[$i] = $row["tipo_moneda_24"];

          $subtotal[$i] = $row["subtotal"];

          $igv[$i] = $row["igv"];

          $total[$i] = $row["total"];

          $tdescu[$i] = $row["tdescuento"];

          $hora[$i] = $row["hora"];

          $tipocomp = $row["tipocomp"];

          $numerodoc = $row["numerodoc"];

          $ruc = $datose->numero_ruc;

          $ubigueo = $datose->ubigueo;

          $opera[$i] = $row["opera"];



          $codigotrib[$i] = $row["codigotrib"]; //codigo de tributo de la tabla catalo 5

          $nombretrib[$i] = $row["nombretrib"]; //NOmbre de tributo de la tabla catalo 5

          $codigointtrib[$i] = $row["codigointtrib"]; //Codigo internacional de la tabla catalo 5



          $icbper = $row["icbper"];





          if ($moneda[$i] == 'USD') {

            $Lmoneda = "DOLARES AMERICANOS";

          }



          $Lmoneda = "NUEVOS SOLES";

          require_once "Letras.php";

          $V = new EnLetras();

          $con_letra = strtoupper($V->ValorEnLetras($total[$i], $Lmoneda));



          //======================================== FORMATO XML ========================================================



          //Primera parte

          $boletaXML = '<?xml version="1.0" encoding="utf-8"?>

            <Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"

                     xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"

                     xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"

                     xmlns:ds="http://www.w3.org/2000/09/xmldsig#"

                     xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2">

                <ext:UBLExtensions>

                    <ext:UBLExtension>

                        <ext:ExtensionContent/>

                    </ext:UBLExtension>

                </ext:UBLExtensions>

                <cbc:UBLVersionID>2.1</cbc:UBLVersionID>

                <cbc:CustomizationID>2.0</cbc:CustomizationID>

                <cbc:ID>' . $numerodoc . '</cbc:ID>

                <cbc:IssueDate>' . $fecha[$i] . '</cbc:IssueDate>

                <cbc:IssueTime>' . $hora[$i] . '</cbc:IssueTime>



                <cbc:InvoiceTypeCode listID="0101">' . $tipocomp . '</cbc:InvoiceTypeCode>



                <cbc:Note languageLocaleID="1000">' . $con_letra . '</cbc:Note>



              <cbc:Note languageLocaleID="2006">Leyenda: Operación sujeta a detracción</cbc:Note>

              <cbc:DocumentCurrencyCode>' . $moneda[$i] . '</cbc:DocumentCurrencyCode>



             



                <cac:Signature>

                    <cbc:ID>' . $ruc . '</cbc:ID>

                    <cbc:Note>SENCON</cbc:Note>

                    <cac:SignatoryParty>

                        <cac:PartyIdentification>

                            <cbc:ID>' . $ruc . '</cbc:ID>

                        </cac:PartyIdentification>

                        <cac:PartyName>

                            <cbc:Name><![CDATA[' . $nombrecomercial . ']]></cbc:Name>

                        </cac:PartyName>

                    </cac:SignatoryParty>

                    <cac:DigitalSignatureAttachment>

                        <cac:ExternalReference>

                            <cbc:URI>#SIGN-SENCON</cbc:URI>

                        </cac:ExternalReference>

                    </cac:DigitalSignatureAttachment>

                </cac:Signature>



                <cac:AccountingSupplierParty>

                    <cac:Party>

                        <cac:PartyIdentification>

                            <cbc:ID schemeID="6">' . $ruc . '</cbc:ID>

                        </cac:PartyIdentification>

                        <cac:PartyName>

                            <cbc:Name><![CDATA[' . $nombrecomercial . ']]></cbc:Name>

                        </cac:PartyName>

                      <cac:PartyLegalEntity>

                        <cbc:RegistrationName><![CDATA[' . $nombrecomercial . ']]></cbc:RegistrationName>

                           <cac:RegistrationAddress>

                             <cbc:AddressTypeCode>' . $codestablecimiento . '</cbc:AddressTypeCode>

                               <cbc:CitySubdivisionName>' . $interior . '</cbc:CitySubdivisionName>

                                <cbc:CityName>' . $ciudad . '</cbc:CityName>

                                  <cbc:CountrySubentity>' . $ciudad . '</cbc:CountrySubentity>

                                    <cbc:CountrySubentityCode>' . $codubigueo . '</cbc:CountrySubentityCode>

                                      <cbc:District>' . $distrito . '</cbc:District> 

                                      <cac:AddressLine>

                                        <cbc:Line><![CDATA[' . $domiciliofiscal . ']]></cbc:Line>

                                          </cac:AddressLine>    

                                            <cac:Country>

                                              <cbc:IdentificationCode>PE</cbc:IdentificationCode>

                                                </cac:Country>

                            </cac:RegistrationAddress>

                        </cac:PartyLegalEntity>

                    </cac:Party>

                </cac:AccountingSupplierParty>



                <cac:AccountingCustomerParty>

                    <cac:Party>

                        <cac:PartyIdentification>

                            <cbc:ID schemeID="' . $tipodocu[$i] . '">' . $numdocu[$i] . '</cbc:ID>

                        </cac:PartyIdentification>

                        <cac:PartyLegalEntity>

                            <cbc:RegistrationName><![CDATA[' . $rasoc[$i] . ']]></cbc:RegistrationName>

                        </cac:PartyLegalEntity>

                    </cac:Party>

                </cac:AccountingCustomerParty>';

          $boletaXML .= '<cac:PaymentTerms>
                <cbc:ID>FormaPago</cbc:ID>
              <cbc:PaymentMeansID>Contado</cbc:PaymentMeansID>
                </cac:PaymentTerms>';



          $boletaXML .= '

                 <!-- Inicio Tributos cabecera-->  

                <cac:TaxTotal>

                    <cbc:TaxAmount currencyID="' . $moneda[$i] . '">' . $igv[$i] . '</cbc:TaxAmount>

                        <cac:TaxSubtotal>

                        <cbc:TaxableAmount currencyID="' . $moneda[$i] . '">' . $subtotal[$i] . '</cbc:TaxableAmount>

                        <cbc:TaxAmount currencyID="' . $moneda[$i] . '">' . $igv[$i] . '</cbc:TaxAmount>

                        <cac:TaxCategory>

                            <cac:TaxScheme>

                                <cbc:ID>' . $codigotrib[$i] . '</cbc:ID>

                                <cbc:Name>' . $nombretrib[$i] . '</cbc:Name>

                                <cbc:TaxTypeCode>' . $codigointtrib[$i] . '</cbc:TaxTypeCode>

                            </cac:TaxScheme>

                        </cac:TaxCategory>

                    </cac:TaxSubtotal>';





          if ($icbper > 0) {

            $boletaXML .= '

                        <cac:TaxSubtotal>

                  <cbc:TaxAmount currencyID="' . $moneda[$i] . '">' . $icbper . '</cbc:TaxAmount>

                         <cac:TaxCategory>

                            <cac:TaxScheme>

                               <cbc:ID>7152</cbc:ID>

                               <cbc:Name>ICBPER</cbc:Name>

                               <cbc:TaxTypeCode>OTH</cbc:TaxTypeCode>

                            </cac:TaxScheme>

                         </cac:TaxCategory>

                      </cac:TaxSubtotal>';

          }



          $boletaXML .= '

            <!-- Fin Tributos  Cabecera-->

              </cac:TaxTotal>



                <cac:LegalMonetaryTotal>

                    <cbc:LineExtensionAmount currencyID="' . $moneda[$i] . '">' . $subtotal[$i] . '</cbc:LineExtensionAmount>

                    <cbc:TaxInclusiveAmount currencyID="' . $moneda[$i] . '">' . $total[$i] . '</cbc:TaxInclusiveAmount>

                    <cbc:AllowanceTotalAmount currencyID="' . $moneda[$i] . '">0.00</cbc:AllowanceTotalAmount>

                    <cbc:ChargeTotalAmount currencyID="' . $moneda[$i] . '">0.00</cbc:ChargeTotalAmount>  

                    <cbc:PrepaidAmount currencyID="' . $moneda[$i] . '">0.00</cbc:PrepaidAmount>  

                    <cbc:PayableAmount currencyID="' . $moneda[$i] . '">' . $total[$i] . '</cbc:PayableAmount>

                </cac:LegalMonetaryTotal>';

        } //For cabecera

        $i = $i + 1;

        $con = $con + 1;

      } //While cabecera



      $codigo = array();

      $cantidad = array();

      $descripcion = array();

      $um = array();

      $vui = array();

      $igvi = array();

      $pvi = array();

      $vvi = array();

      $sutribitem = array();

      $aigv = array();

      $codtrib = array();

      $nomtrib = array();

      $coditrib = array();

      $codigosunat = array();

      $numorden = array();

      $monedaD = array();

      $mticbperu = array();



      while ($rowb = mysqli_fetch_assoc($resultb)) {
        for ($ib = 0; $ib < count($resultb); $ib++) {
          $codigo[$ib] = $rowb["codigo"];
          $cantidad[$ib] = $rowb["cantidad"];
          $descripcion[$ib] = $rowb["descripcion"];
          $vui[$ib] = $rowb["vui"];

          $sutribitem[$ib] = $rowb["sutribitem"];

          $igvi[$ib] = $rowb["igvi"];

          $pvi[$ib] = $rowb["pvi"];

          $vvi[$ib] = $rowb["vvi"];

          $um[$ib] = $rowb["um"];

          $tipocompf = $rowb["tipocomp"];

          $numerodocf = $rowb["numerodoc"];

          $ruc = $datose->numero_ruc;

          $aigv[$ib] = $rowb["aigv"];

          $codtrib[$ib] = $rowb["codtrib"];

          $nomtrib[$ib] = $rowb["nomtrib"];

          $coditrib[$ib] = $rowb["coditrib"];

          $codigosunat[$ib] = $rowb["codigosunat"];

          $numorden[$ib] = $rowb["numorden"];



          $monedaD[$ib] = $rowb["moneda"];

          $mticbperu[$ib] = $rowb["mticbperu"];



          $icbperD = $rowb["icbper"];



          if ($codtrib[$ib] == '9997') {
            $igv_ = "0";

          } else {
            $igv_ = $configE->igv;
          }



          $boletaXML .= '

                <cac:InvoiceLine>

                    <cbc:ID>' . $numorden[$ib] . '</cbc:ID>

                    <cbc:InvoicedQuantity unitCode="' . $um[$ib] . '">' . number_format($cantidad[$ib], 2, '.', '') . '</cbc:InvoicedQuantity>

                    <cbc:LineExtensionAmount currencyID="' . $monedaD[$ib] . '">' . number_format($vvi[$ib], 2, '.', '') . '</cbc:LineExtensionAmount>

                    

                    <cac:PricingReference>

                        <cac:AlternativeConditionPrice>

                            <cbc:PriceAmount currencyID="' . $monedaD[$ib] . '">' . number_format($pvi[$ib], 2, '.', '') . '</cbc:PriceAmount>

                            <cbc:PriceTypeCode>01</cbc:PriceTypeCode>

                        </cac:AlternativeConditionPrice>

                    </cac:PricingReference>



                    <cac:TaxTotal>

                        <cbc:TaxAmount currencyID="' . $monedaD[$ib] . '">' . number_format($sutribitem[$ib], 2, '.', '') . '</cbc:TaxAmount>                        

                        <cac:TaxSubtotal>

                            <cbc:TaxableAmount currencyID="' . $monedaD[$ib] . '">' . number_format($vvi[$ib], 2, '.', '') . '</cbc:TaxableAmount>

                            <cbc:TaxAmount currencyID="' . $monedaD[$ib] . '">' . number_format($sutribitem[$ib], 2, '.', '') . '</cbc:TaxAmount>

                            <cac:TaxCategory>

                                <cbc:Percent>' . $igv_ . '</cbc:Percent>

                                <cbc:TaxExemptionReasonCode>' . $aigv[$ib] . '</cbc:TaxExemptionReasonCode>

                                <cac:TaxScheme>

                                    <cbc:ID>' . $codtrib[$ib] . '</cbc:ID>

                                    <cbc:Name>' . $nomtrib[$ib] . '</cbc:Name>

                                    <cbc:TaxTypeCode>' . $coditrib[$ib] . '</cbc:TaxTypeCode>

                                </cac:TaxScheme>

                            </cac:TaxCategory>

                        </cac:TaxSubtotal>';







          if ($codigo[$ib] == "ICBPER") {



            $boletaXML .= '



                <cac:TaxSubtotal>

                    <cbc:TaxAmount currencyID="' . $moneda[$ib] . '">' . $icbperD . '</cbc:TaxAmount>

                    <cbc:BaseUnitMeasure unitCode="' . $um[$ib] . '">' . number_format($cantidad[$ib], 0, '.', '') . '</cbc:BaseUnitMeasure>

                    <cac:TaxCategory>

                    <cbc:PerUnitAmount currencyID="' . $moneda[$ib] . '">' . number_format($mticbperu[$ib], 2, '.', '') . '</cbc:PerUnitAmount>

                       <cac:TaxScheme>

                          <cbc:ID>7152</cbc:ID>

                          <cbc:Name>ICBPER</cbc:Name>

                          <cbc:TaxTypeCode>OTH</cbc:TaxTypeCode>

                       </cac:TaxScheme>

                    </cac:TaxCategory>

                 </cac:TaxSubtotal>';

          }
          ;





          $boletaXML .= '

                     </cac:TaxTotal>



                    <cac:Item>

                        <cbc:Description><![CDATA[' . $descripcion[$ib] . ']]></cbc:Description>

                        <cac:SellersItemIdentification>

                            <cbc:ID>' . $codigo[$ib] . '</cbc:ID>

                        </cac:SellersItemIdentification>

                    </cac:Item>



                    <cac:Price>

                        <cbc:PriceAmount currencyID="' . $monedaD[$ib] . '">' . number_format($vui[$ib], 5, '.', '') . '</cbc:PriceAmount>

                    </cac:Price>

                </cac:InvoiceLine>';



        } //Fin for

      } //Find e while 

      $boletaXML .= '</Invoice>';

      //FIN DE CABECERA ===================================================================





      // Nos aseguramos de que la cadena que contiene el XML esté en UTF-8

      $boletaXML = mb_convert_encoding($boletaXML, "UTF-8");

      // Grabamos el XML en el servidor como un fichero plano, para

      // poder ser leido por otra aplicación.

      $gestor = fopen($rutafirma . $ruc . "-" . $tipocomp . "-" . $numerodoc . ".xml", 'w');

      fwrite($gestor, $boletaXML);

      fclose($gestor);



      $cabextxml = $rutafirma . $ruc . "-" . $tipocomp . "-" . $numerodoc . ".xml";

      $cabxml = $ruc . "-" . $tipocomp . "-" . $numerodoc . ".xml";

      $nomxml = $ruc . "-" . $tipocomp . "-" . $numerodoc;

      $nomxmlruta = $rutafirma . $ruc . "-" . $tipocomp . "-" . $numerodoc;



      require_once("../greemter/Greenter.php");

      $invo = new Greenter();

      $out = $invo->getDatFac($cabextxml);



      $filenaz = $nomxml . ".zip";

      $zip = new ZipArchive();

      if ($zip->open($filenaz, ZIPARCHIVE::CREATE) === true) {

        //$zip->addEmptyDir("dummy");

        $zip->addFile($cabextxml, $cabxml);

        $zip->close();



        //if(!file_exists($rutaz)){mkdir($rutaz);}

        $imagen = file_get_contents($filenaz);

        $imageData = base64_encode($imagen);

        rename($cabextxml, $rutafirma . $cabxml);

        rename($filenaz, $rutaenvio . $filenaz);

      } else {

        $out = "Error al comprimir archivo";

      }



      $data[0] = "";



      $sxe = new SimpleXMLElement($cabextxml, null, true);

      $urn = $sxe->getNamespaces(true);

      $sxe->registerXPathNamespace('ds', $urn['ds']);

      $data = $sxe->xpath('//ds:DigestValue');



      $rpta = array('cabextxml' => $cabextxml, 'cabxml' => $cabxml, 'rutafirma' => $cabextxml);

      $sqlDetalle = "update boleta set DetalleSunat='XML firmado'  , hashc='$data[0]'  where idboleta='$idboleta'";

      ejecutarConsulta($sqlDetalle);



      //PARA ENVIO A SUNAT ================&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&))))))))))))))))))))))))))))))))))))))))))



      require_once "../modelos/Factura.php";
      $factura = new Factura();
      $datos = $factura->correo();
      $correo = $datos->fetch_object();





      require_once "../modelos/Consultas.php";

      $consultas = new consultas();

      $paramcerti = $consultas->paramscerti();

      $datosc = $paramcerti->fetch_object();



      //Inclusion de la tabla RUTAS

      require_once "../modelos/Rutas.php";

      $rutas = new Rutas();

      $Rrutas = $rutas->mostrar2($idempresa);

      $Prutas = $Rrutas->fetch_object();

      $rutafirma = $Prutas->rutafirma; // ruta de la carpeta FIRMA

      $rutaenvio = $Prutas->rutaenvio; // ruta de la carpeta FIRMA

      $rutarpta = $Prutas->rutarpta; // ruta de la carpeta FIRMA

      $rutaunzip = $Prutas->unziprpta; // ruta de la carpeta rpta xml



      $connect = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

      mysqli_query($connect, 'SET NAMES "' . DB_ENCODE . '"');

      //Si tenemos un posible error en la conexión lo mostramos

      if (mysqli_connect_errno()) {

        printf("Falló conexión a la base de datos: %s\n", mysqli_connect_error());

        exit();

      }



      $sqlsendmail = "select 

        b.idboleta, 

        p.email,  

        p.nombres, 

        p.apellidos, 

        p.nombre_comercial, 

        e.numero_ruc,

        b.tipo_documento_06,

        b.numeracion_07 

        from 

        boleta b inner join persona p on 

        b.idcliente=p.idpersona inner join empresa e on 

        b.idempresa=e.idempresa 

        where 

        year(b.fecha_emision_01)='$ano' and 

        month(b.fecha_emision_01)='$mes' and 

        day(b.fecha_emision_01)='$dia' and 

        b.idboleta='$idboleta' and b.estado='$estado'";



      $result = mysqli_query($connect, $sqlsendmail);



      $con = 0;

      while ($row = mysqli_fetch_assoc($result)) {

        for ($i = 0; $i <= count($result); $i++) {

          $correocliente = $row["email"];

        }



        //Agregar=====================================================

        // Ruta del directorio donde están los archivos

        $path = $rutafirma;

        $files = array_diff(scandir($path), array('.', '..'));

        //=============================================================

        $boleta = $row['numero_ruc'] . "-" . $row['tipo_documento_06'] . "-" . $row['numeracion_07'];



        //Validar si existe el archivo firmado

        foreach ($files as $file) {

          // Divides en dos el nombre de tu archivo utilizando el . 

          $dataSt = explode(".", $file);

          // Nombre del archivo

          $fileName = $dataSt[0];

          $st = "1";

          // Extensión del archivo 

          $fileExtension = $dataSt[1];

          if ($boleta == $fileName) {

            $archivoBoleta = $fileName;

            // Realizamos un break para que el ciclo se interrumpa

            break;

          }

        }

        //$url=$rutafirma.$archivoFactura.'.xml';

        $ZipBoleta = $rutaenvio . $archivoBoleta . '.zip';

        copy($ZipBoleta, $archivoBoleta . '.zip');

        $ZipFinal = $boleta . '.zip';

        //echo $ZipFactura;



        $webservice = $datosc->rutaserviciosunat;

        $usuarioSol = $datosc->usuarioSol;

        $claveSol = $datosc->claveSol;

        $nruc = $datosc->numeroruc;



        //Llamada al WebService=======================================================================

        $service = $webservice;

        $headers = new CustomHeaders($nruc . $usuarioSol, $claveSol);

        $client = new SoapClient(
          $service,
          [

            'cache_wsdl' => WSDL_CACHE_NONE,

            'trace' => TRUE,

            'soap_version' => SOAP_1_1
          ]

        );



        try {

          $client->__setSoapHeaders([$headers]);

          $fcs = $client->__getFunctions();

          $params = array('fileName' => $ZipFinal, 'contentFile' => file_get_contents($ZipFinal));



          //Llamada al WebService=======================================================================

          $status = $client->sendBill($params); // Comando para enviar xml a SUNAT

          $conte = $client->__getLastResponse();

          $texto = trim(strip_tags($conte));





          $zip = new ZipArchive();

          if ($zip->open("R" . $ZipFinal, ZIPARCHIVE::CREATE) === true) {

            $zip->addEmptyDir("dummy");

            $zip->close();
          }





          $rpt = fopen("R" . $ZipFinal, 'w') or die("no se pudo crear archivo");

          fwrite($rpt, base64_decode($texto));

          fclose($rpt);

          rename("R" . $ZipFinal, $rutarpta . "R" . $ZipFinal);

          unlink($ZipFinal);





          $rutarptazip = $rutarpta . "R" . $ZipFinal;

          $zip = new ZipArchive;

          if ($zip->open($rutarptazip) === TRUE) {

            $zip->extractTo($rutaunzip);

            $zip->close();

          }

          $xmlFinal = $rutaunzip . 'R-' . $boleta . '.xml';

          $data[0] = "";

          $rpta[0] = "";

          $sxe = new SimpleXMLElement($xmlFinal, null, true);

          $urn = $sxe->getNamespaces(true);

          $sxe->registerXPathNamespace('cac', $urn['cbc']);

          $data = $sxe->xpath('//cbc:Description');

          $rpta = $sxe->xpath('//cbc:ResponseCode');



          if ($rpta[0] == '0') {

            $msg = "Aceptada por SUNAT";

            $sqlCodigo = "update boleta set CodigoRptaSunat='$rpta[0]', DetalleSunat='$data[0]', estado='5' where idboleta='$idboleta'";

          } else {

            $sqlCodigo = "update boleta set CodigoRptaSunat='$rpta[0]', DetalleSunat='$data[0]', estado='4' where idboleta='$idboleta'";

          }

          ejecutarConsulta($sqlCodigo);

          return $data[0];

          // Llamada al WebService=======================================================================

        } catch (SoapFault $exception) {
          $exception = print_r($client->__getLastResponse());
          $sqlCodigo = "update boleta set CodigoRptaSunat='', DetalleSunat='VERIFICAR ENVIO' where idboleta='$idboleta'";
          ejecutarConsulta($sqlCodigo);

        }



      } //Fin While





    } //Fin de if





  } //Fin de funcion







  public function regenerarxml($idboleta, $idempresa)
  {

    $connect = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

    mysqli_query($connect, 'SET NAMES "' . DB_ENCODE . '"');

    //Si tenemos un posible error en la conexión lo mostramos

    if (mysqli_connect_errno()) {

      printf("Falló conexión a la base de datos: %s\n", mysqli_connect_error());

      exit();

    }



    require_once "../modelos/Factura.php";

    $factura = new Factura();

    $datos = $factura->datosemp($idempresa);

    $configuraciones = $factura->configuraciones($idempresa);

    $configE = $configuraciones->fetch_object();

    $datose = $datos->fetch_object();







    //Inclusion de la tabla RUTAS

    require_once "../modelos/Rutas.php";

    $rutas = new Rutas();

    $Rrutas = $rutas->mostrar2($idempresa);

    $Prutas = $Rrutas->fetch_object();

    $rutadata = $Prutas->rutadata; // ruta de la carpeta DATA

    $rutafirma = $Prutas->rutafirma; // ruta de la carpeta FIRMA

    $rutadatalt = $Prutas->rutadatalt; // ruta de la carpeta DATAALTERNA

    $rutaenvio = $Prutas->rutaenvio; // ruta de la carpeta DATAALTERNA



    $query = "select
     date_format(b.fecha_emision_01, '%Y-%m-%d') as fecha, 
     right(substring_index(b.numeracion_07,'-',1),1) as serie,
     date_format(b.fecha_emision_01, '%H:%i:%s') as hora,
     p.tipo_documento as  tipodocuCliente, 
     p.numero_documento, 
     p.razon_social, 
     b.tipo_moneda_24, 
     b.monto_15_2 as subtotal, 
     b.sumatoria_igv_18_1 as igv, 
     b.importe_total_23 as total, 
     b.tipo_documento_06 as tipocomp, 
     b.numeracion_07 as numerodoc, 
     b.estado, 
     b.tdescuento,
     b.codigo_tributo_18_3 as codigotrib,
     b.nombre_tributo_18_4  as nombretrib,
     b.codigo_internacional_18_5 as codigointtrib,
     b.codigo_tipo_15_1 as opera,
     e.ubigueo,
     b.icbper,

     b.formapago,
     b.montofpago,
     b.monedafpago,
     b.ccuotas,
     b.fechavecredito,
     b.montocuota,
     b.fechavenc
     
     from 
     boleta b inner join persona p on b.idcliente=p.idpersona 
     inner join empresa e on b.idempresa=e.idempresa 
     where
    idboleta='$idboleta' and b.estado in('1','4','3','5') order by numerodoc";


    $querycuotas = "select 
     lpad(cu.ncuota,3,'0') as ncuota ,
     cu.montocuota,
     date_format(cu.fechacuota, '%Y-%m-%d') as fechacuota,
     format(b.formapago,2) as formapago,
     b.tipo_moneda_24 as monedaf
     from 
     cuotas cu inner join boleta b on cu.idcomprobante=b.idboleta
     where idcomprobante='$idboleta' and cu.tipocomprobante='03'";



    $querydetbol = "select
       b.tipo_documento_06 as tipocomp, 
       b.numeracion_07 as numerodoc,  
       db.cantidad_item_12 as cantidad, 
       a.codigo, 
       a.nombre as descripcion, 
       um.abre as um,
       replace(format(db.valor_uni_item_31,5),',','') as vui, 
       db.igv_item as igvi, 
       db.precio_uni_item_14_2 as pvi, 
       db.valor_venta_item_32 as vvi,
       db.afectacion_igv_item_monto_27_1 as sutribitem,
       db.numero_orden_item_29 as numorden,

       db.afectacion_igv_3 as aigv,
       db.afectacion_igv_4 codtrib,
       db.afectacion_igv_5 as nomtrib,
       db.afectacion_igv_6 as coditrib,
       a.codigosunat,
       b.tipo_moneda_24 as moneda,
       a.mticbperu,
       b.icbper,
       db.umedida
       from
       boleta b inner join detalle_boleta_producto db on b.idboleta=db.idboleta inner join articulo a on db.idarticulo=a.idarticulo inner join umedida um on a.unidad_medida=um.idunidad

          where 

          b.idboleta='$idboleta' and b.estado in ('1','4','3','5') order by b.fecha_emision_01";

    $result = mysqli_query($connect, $query);
    $resultb = mysqli_query($connect, $querydetbol);
    $resultcuotas = mysqli_query($connect, $querycuotas);

    $nombrecomercial = $datose->nombre_comercial;
    $domiciliofiscal = $datose->domicilio_fiscal;
    $codestablecimiento = $datose->ubigueo;
    $codubigueo = $datose->codubigueo;
    $ciudad = $datose->ciudad;
    $distrito = $datose->distrito;
    $interior = $datose->interior;
    $codigopais = $datose->codigopais;

    //Parametros de salida

    $fecha = "";
    $hora = "";
    $serie = "";
    $tipodocu = "";
    $numdocu = "";
    $rasoc = "";
    $moneda = "";
    $codigotrib = "";
    $nombretrib = "";
    $codigointtrib = "";
    $subtotal = "";
    $igv = "";
    $total = "";
    $tdescu = "";
    $opera = "";
    $ubigueo = "";


    $formapago = "";
    $montofpago = "";
    $monedafpago = "";
    $ccuotas = "";
    $fechavecredito = "";
    $montocuota = "";





    $con = 0; //COntador de variable

    $icbper = "";



    while ($row = mysqli_fetch_assoc($result)) {

      //for($i=0; $i <= count($result); $i++){
      $fecha = $row["fecha"]; //Fecha emision
      $serie = $row["serie"];
      $tipodocu = $row["tipodocuCliente"]; //Tipo de documento de cliente ruc o dni
      $numdocu = $row["numero_documento"]; //NUmero de docuemnto de cliente
      $rasoc = $row["razon_social"]; //Nombre de cliente
      $moneda = $row["tipo_moneda_24"];
      $subtotal = $row["subtotal"];
      $igv = $row["igv"];
      $total = $row["total"];
      $tdescu = $row["tdescuento"];
      $hora = $row["hora"];
      $tipocomp = $row["tipocomp"];
      $numerodoc = $row["numerodoc"];
      $ruc = $datose->numero_ruc;
      $ubigueo = $datose->ubigueo;
      $opera = $row["opera"];

      $codigotrib = $row["codigotrib"]; //codigo de tributo de la tabla catalo 5
      $nombretrib = $row["nombretrib"]; //NOmbre de tributo de la tabla catalo 5
      $codigointtrib = $row["codigointtrib"]; //Codigo internacional de la tabla catalo 5

      $formapago = $row["formapago"];
      $montofpago = $row["montofpago"];
      $monedafpago = $row["monedafpago"];
      $ccuotas = $row["ccuotas"];
      $fechavecredito = $row["fechavecredito"];
      $montocuota = $row["montocuota"];



      $icbper = $row["icbper"];


      if ($moneda == 'USD') {
        $Lmoneda = "DOLARES AMERICANOS";
      }

      $Lmoneda = "NUEVOS SOLES";

      require_once "Letras.php";

      $V = new EnLetras();
      $con_letra = strtoupper($V->ValorEnLetras($total, $Lmoneda));

      //======================================== FORMATO XML ========================================================

      //Primera parte

      $boletaXML = '<?xml version="1.0" encoding="utf-8"?>
            <Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"
                     xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
                     xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
                     xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
                     xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2">
                <ext:UBLExtensions>
                    <ext:UBLExtension>
                        <ext:ExtensionContent/>
                    </ext:UBLExtension>
                </ext:UBLExtensions>
                <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
                <cbc:CustomizationID>2.0</cbc:CustomizationID>
                <cbc:ID>' . $numerodoc . '</cbc:ID>
                <cbc:IssueDate>' . $fecha . '</cbc:IssueDate>
                <cbc:IssueTime>' . $hora . '</cbc:IssueTime>



                <cbc:InvoiceTypeCode listID="0101">' . $tipocomp . '</cbc:InvoiceTypeCode>
                <cbc:Note languageLocaleID="1000">' . $con_letra . '</cbc:Note>
              <cbc:Note languageLocaleID="2006">Leyenda: Operación sujeta a detracción</cbc:Note>
              <cbc:DocumentCurrencyCode>' . $moneda . '</cbc:DocumentCurrencyCode>


                <cac:Signature>

                    <cbc:ID>' . $ruc . '</cbc:ID>

                    <cbc:Note>SENCON</cbc:Note>

                    <cac:SignatoryParty>

                        <cac:PartyIdentification>

                            <cbc:ID>' . $ruc . '</cbc:ID>

                        </cac:PartyIdentification>

                        <cac:PartyName>

                            <cbc:Name><![CDATA[' . $nombrecomercial . ']]></cbc:Name>

                        </cac:PartyName>

                    </cac:SignatoryParty>

                    <cac:DigitalSignatureAttachment>

                        <cac:ExternalReference>

                            <cbc:URI>#SIGN-SENCON</cbc:URI>

                        </cac:ExternalReference>

                    </cac:DigitalSignatureAttachment>

                </cac:Signature>



                <cac:AccountingSupplierParty>

                    <cac:Party>

                        <cac:PartyIdentification>

                            <cbc:ID schemeID="6">' . $ruc . '</cbc:ID>

                        </cac:PartyIdentification>

                        <cac:PartyName>

                            <cbc:Name><![CDATA[' . $nombrecomercial . ']]></cbc:Name>

                        </cac:PartyName>

                      <cac:PartyLegalEntity>

                        <cbc:RegistrationName><![CDATA[' . $nombrecomercial . ']]></cbc:RegistrationName>

                           <cac:RegistrationAddress>

                             <cbc:AddressTypeCode>' . $codestablecimiento . '</cbc:AddressTypeCode>

                               <cbc:CitySubdivisionName>' . $interior . '</cbc:CitySubdivisionName>

                                <cbc:CityName>' . $ciudad . '</cbc:CityName>

                                  <cbc:CountrySubentity>' . $ciudad . '</cbc:CountrySubentity>

                                    <cbc:CountrySubentityCode>' . $codubigueo . '</cbc:CountrySubentityCode>

                                      <cbc:District>' . $distrito . '</cbc:District> 

                                      <cac:AddressLine>

                                        <cbc:Line><![CDATA[' . $domiciliofiscal . ']]></cbc:Line>

                                          </cac:AddressLine>    

                                            <cac:Country>

                                              <cbc:IdentificationCode>PE</cbc:IdentificationCode>

                                                </cac:Country>

                            </cac:RegistrationAddress>

                        </cac:PartyLegalEntity>

                    </cac:Party>

                </cac:AccountingSupplierParty>



                <cac:AccountingCustomerParty>

                    <cac:Party>

                        <cac:PartyIdentification>

                            <cbc:ID schemeID="' . $tipodocu . '">' . $numdocu . '</cbc:ID>

                        </cac:PartyIdentification>

                        <cac:PartyLegalEntity>

                            <cbc:RegistrationName><![CDATA[' . $rasoc . ']]></cbc:RegistrationName>

                        </cac:PartyLegalEntity>

                    </cac:Party>

                </cac:AccountingCustomerParty>';

      //    $boletaXML.='<cac:PaymentTerms>
      //   <cbc:ID>FormaPago</cbc:ID>
      // <cbc:PaymentMeansID>Contado</cbc:PaymentMeansID>
      //   </cac:PaymentTerms>';



      if ($formapago == 'Contado') {
        $boletaXML .= '<cac:PaymentTerms>
                <cbc:ID>FormaPago</cbc:ID>
                <cbc:PaymentMeansID>' . $formapago . '</cbc:PaymentMeansID>
                </cac:PaymentTerms>';

      } else { // SI ES AL CREDITO

        $boletaXML .= '<cac:PaymentTerms>
                <cbc:ID>FormaPago</cbc:ID>
                <cbc:PaymentMeansID>' . $formapago . '</cbc:PaymentMeansID>
                <cbc:Amount currencyID="' . $moneda . '">' . $total . '</cbc:Amount>
                </cac:PaymentTerms>';

        $ncuotacredito = array();
        $montocuotacredito = array();
        $fechacuotacredito = array();
        $formapagocre = array();
        $monedaf = array();

        while ($rowb = mysqli_fetch_assoc($resultcuotas)) {
          for ($i = 0; $i < count($resultcuotas); $i++) {
            $ncuotacredito[$i] = $rowb["ncuota"];
            $montocuotacredito[$i] = $rowb["montocuota"];
            $fechacuotacredito[$i] = $rowb["fechacuota"];
            $formapagocre[$i] = $rowb["formapago"];
            $monedaf[$i] = $rowb["monedaf"];

            $boletaXML .= '<cac:PaymentTerms>
                <cbc:ID>FormaPago</cbc:ID>
                <cbc:PaymentMeansID>Cuota' . $ncuotacredito[$i] . '</cbc:PaymentMeansID>
                <cbc:Amount currencyID="' . $monedaf[$i] . '">' . $montocuotacredito[$i] . '</cbc:Amount>
                <cbc:PaymentDueDate>' . $fechacuotacredito[$i] . '</cbc:PaymentDueDate>
                </cac:PaymentTerms>';
          }
          $i = $i + 1;
        }

      }




      $boletaXML .= '

                 <!-- Inicio Tributos cabecera-->  
                <cac:TaxTotal>
                    <cbc:TaxAmount currencyID="' . $moneda . '">' . $igv . '</cbc:TaxAmount>
                        <cac:TaxSubtotal>
                        <cbc:TaxableAmount currencyID="' . $moneda . '">' . $subtotal . '</cbc:TaxableAmount>
                        <cbc:TaxAmount currencyID="' . $moneda . '">' . $igv . '</cbc:TaxAmount>
                        <cac:TaxCategory>
                            <cac:TaxScheme>
                                <cbc:ID>' . $codigotrib . '</cbc:ID>
                                <cbc:Name>' . $nombretrib . '</cbc:Name>
                                <cbc:TaxTypeCode>' . $codigointtrib . '</cbc:TaxTypeCode>
                            </cac:TaxScheme>
                        </cac:TaxCategory>
                    </cac:TaxSubtotal>';





      if ($icbper > 0) {

        $boletaXML .= '
                        <cac:TaxSubtotal>
                  <cbc:TaxAmount currencyID="' . $moneda . '">' . $icbper . '</cbc:TaxAmount>
                         <cac:TaxCategory>
                            <cac:TaxScheme>
                               <cbc:ID>7152</cbc:ID>
                               <cbc:Name>ICBPER</cbc:Name>
                               <cbc:TaxTypeCode>OTH</cbc:TaxTypeCode>
                            </cac:TaxScheme>
                         </cac:TaxCategory>
                      </cac:TaxSubtotal>';

      }



      $boletaXML .= '

            <!-- Fin Tributos  Cabecera-->

              </cac:TaxTotal>



                <cac:LegalMonetaryTotal>

                    <cbc:LineExtensionAmount currencyID="' . $moneda . '">' . $subtotal . '</cbc:LineExtensionAmount>
                    <cbc:TaxInclusiveAmount currencyID="' . $moneda . '">' . $total . '</cbc:TaxInclusiveAmount>
                    <cbc:AllowanceTotalAmount currencyID="' . $moneda . '">0.00</cbc:AllowanceTotalAmount>
                    <cbc:ChargeTotalAmount currencyID="' . $moneda . '">0.00</cbc:ChargeTotalAmount>  
                    <cbc:PrepaidAmount currencyID="' . $moneda . '">0.00</cbc:PrepaidAmount>  
                    <cbc:PayableAmount currencyID="' . $moneda . '">' . $total . '</cbc:PayableAmount>

                </cac:LegalMonetaryTotal>';

      //}//For cabecera

      $i = $i + 1;

      $con = $con + 1;

    } //While cabecera



    $codigo = array();

    $cantidad = array();

    $descripcion = array();

    $um = array();

    $vui = array();

    $igvi = array();

    $pvi = array();

    $vvi = array();

    $sutribitem = array();

    $aigv = array();

    $codtrib = array();

    $nomtrib = array();

    $coditrib = array();

    $codigosunat = array();

    $numorden = array();

    $monedaD = array();

    $mticbperu = array();



    while ($rowb = mysqli_fetch_assoc($resultb)) {
      for ($ib = 0; $ib < count($resultb); $ib++) {
        $codigo[$ib] = $rowb["codigo"];
        $cantidad[$ib] = $rowb["cantidad"];
        $descripcion[$ib] = $rowb["descripcion"];
        $vui[$ib] = $rowb["vui"];
        $sutribitem[$ib] = $rowb["sutribitem"];
        $igvi[$ib] = $rowb["igvi"];
        $pvi[$ib] = $rowb["pvi"];
        $vvi[$ib] = $rowb["vvi"];
        $um[$ib] = $rowb["umedida"];
        $tipocompf = $rowb["tipocomp"];
        $numerodocf = $rowb["numerodoc"];
        $ruc = $datose->numero_ruc;
        $aigv[$ib] = $rowb["aigv"];
        $codtrib[$ib] = $rowb["codtrib"];
        $nomtrib[$ib] = $rowb["nomtrib"];
        $coditrib[$ib] = $rowb["coditrib"];
        $codigosunat[$ib] = $rowb["codigosunat"];
        $numorden[$ib] = $rowb["numorden"];
        $monedaD[$ib] = $rowb["moneda"];
        $mticbperu[$ib] = $rowb["mticbperu"];

        $icbperD = $rowb["icbper"];


        if ($codtrib[$ib] == '9997') {
          $igv_ = "0";

        } else {
          $igv_ = $configE->igv;
        }



        /* Número de orden del Ítem

           Cantidad y Unidad de medida por ítem

           Valor de venta del ítem  */



        $boletaXML .= '

                <cac:InvoiceLine>
                    <cbc:ID>' . $numorden[$ib] . '</cbc:ID>
                    <cbc:InvoicedQuantity unitCode="' . $um[$ib] . '">' . number_format($cantidad[$ib], 2, '.', '') . '</cbc:InvoicedQuantity>
                    <cbc:LineExtensionAmount currencyID="' . $monedaD[$ib] . '">' . number_format($vvi[$ib], 2, '.', '') . '</cbc:LineExtensionAmount>

                    

                    <cac:PricingReference>
                        <cac:AlternativeConditionPrice>
                            <cbc:PriceAmount currencyID="' . $monedaD[$ib] . '">' . number_format($pvi[$ib], 2, '.', '') . '</cbc:PriceAmount>
                            <cbc:PriceTypeCode>01</cbc:PriceTypeCode>
                        </cac:AlternativeConditionPrice>
                    </cac:PricingReference>



                    <cac:TaxTotal>

                        <cbc:TaxAmount currencyID="' . $monedaD[$ib] . '">' . number_format($sutribitem[$ib], 2, '.', '') . '</cbc:TaxAmount>                        

                        <cac:TaxSubtotal>
                            <cbc:TaxableAmount currencyID="' . $monedaD[$ib] . '">' . number_format($vvi[$ib], 2, '.', '') . '</cbc:TaxableAmount>
                            <cbc:TaxAmount currencyID="' . $monedaD[$ib] . '">' . number_format($sutribitem[$ib], 2, '.', '') . '</cbc:TaxAmount>
                            <cac:TaxCategory>
                                <cbc:Percent>' . $igv_ . '</cbc:Percent>
                                <cbc:TaxExemptionReasonCode>' . $aigv[$ib] . '</cbc:TaxExemptionReasonCode>
                                <cac:TaxScheme>
                                    <cbc:ID>' . $codtrib[$ib] . '</cbc:ID>
                                    <cbc:Name>' . $nomtrib[$ib] . '</cbc:Name>
                                    <cbc:TaxTypeCode>' . $coditrib[$ib] . '</cbc:TaxTypeCode>
                                </cac:TaxScheme>
                            </cac:TaxCategory>
                        </cac:TaxSubtotal>';







        if ($codigo[$ib] == "ICBPER") {



          $boletaXML .= '



                <cac:TaxSubtotal>
                    <cbc:TaxAmount currencyID="' . $moneda[$ib] . '">' . $icbperD . '</cbc:TaxAmount>
                    <cbc:BaseUnitMeasure unitCode="' . $um[$ib] . '">' . number_format($cantidad[$ib], 0, '.', '') . '</cbc:BaseUnitMeasure>
                    <cac:TaxCategory>
                    <cbc:PerUnitAmount currencyID="' . $moneda[$ib] . '">' . number_format($mticbperu[$ib], 2, '.', '') . '</cbc:PerUnitAmount>
                       <cac:TaxScheme>
                          <cbc:ID>7152</cbc:ID>
                          <cbc:Name>ICBPER</cbc:Name>
                          <cbc:TaxTypeCode>OTH</cbc:TaxTypeCode>
                       </cac:TaxScheme>
                    </cac:TaxCategory>
                 </cac:TaxSubtotal>';

        }
        ;





        $boletaXML .= '

                     </cac:TaxTotal>
                    <cac:Item>
                        <cbc:Description><![CDATA[' . $descripcion[$ib] . ']]></cbc:Description>
                        <cac:SellersItemIdentification>
                            <cbc:ID>' . $codigo[$ib] . '</cbc:ID>
                        </cac:SellersItemIdentification>
                    </cac:Item>



                    <cac:Price>
                        <cbc:PriceAmount currencyID="' . $monedaD[$ib] . '">' . number_format($vui[$ib], 5, '.', '') . '</cbc:PriceAmount>
                    </cac:Price>
                </cac:InvoiceLine>';



      } //Fin for

    } //Find e while 

    $boletaXML .= '</Invoice>';

    //FIN DE CABECERA ===================================================================





    // Nos aseguramos de que la cadena que contiene el XML esté en UTF-8

    $boletaXML = mb_convert_encoding($boletaXML, "UTF-8");

    // Grabamos el XML en el servidor como un fichero plano, para

    // poder ser leido por otra aplicación.

    $gestor = fopen($rutafirma . $ruc . "-" . $tipocomp . "-" . $numerodoc . ".xml", 'w');

    fwrite($gestor, $boletaXML);

    fclose($gestor);



    $cabextxml = $rutafirma . $ruc . "-" . $tipocomp . "-" . $numerodoc . ".xml";

    $cabxml = $ruc . "-" . $tipocomp . "-" . $numerodoc . ".xml";

    $nomxml = $ruc . "-" . $tipocomp . "-" . $numerodoc;

    $nomxmlruta = $rutafirma . $ruc . "-" . $tipocomp . "-" . $numerodoc;



    require_once("../greemter/Greenter.php");

    $invo = new Greenter();

    $out = $invo->getDatFac($cabextxml);



    $filenaz = $nomxml . ".zip";

    $zip = new ZipArchive();

    if ($zip->open($filenaz, ZIPARCHIVE::CREATE) === true) {

      //$zip->addEmptyDir("dummy");

      $zip->addFile($cabextxml, $cabxml);

      $zip->close();



      //if(!file_exists($rutaz)){mkdir($rutaz);}

      $imagen = file_get_contents($filenaz);

      $imageData = base64_encode($imagen);

      rename($cabextxml, $rutafirma . $cabxml);

      rename($filenaz, $rutaenvio . $filenaz);

    } else {

      $out = "Error al comprimir archivo";

    }



    $data[0] = "";



    $sxe = new SimpleXMLElement($cabextxml, null, true);

    $urn = $sxe->getNamespaces(true);

    $sxe->registerXPathNamespace('ds', $urn['ds']);

    $data = $sxe->xpath('//ds:DigestValue');



    $rpta = array('cabextxml' => $cabextxml, 'cabxml' => $cabxml, 'rutafirma' => $cabextxml);

    // $sqlDetalle="update boleta set DetalleSunat='XML firmado', hashc='$data[0]', estado='4' where idboleta='$idboleta'";

    // ejecutarConsulta($sqlDetalle);



    return $rpta;



  } //Fin de funcion







  public function enviarxmlSUNAT($idboleta, $idempresa)
  {

    require_once "../modelos/Factura.php";
    $factura = new Factura();
    $datos = $factura->correo();
    $correo = $datos->fetch_object();





    require_once "../modelos/Consultas.php";

    $consultas = new consultas();

    $paramcerti = $consultas->paramscerti();

    $datosc = $paramcerti->fetch_object();



    //Inclusion de la tabla RUTAS

    require_once "../modelos/Rutas.php";

    $rutas = new Rutas();

    $Rrutas = $rutas->mostrar2($idempresa);

    $Prutas = $Rrutas->fetch_object();

    $rutafirma = $Prutas->rutafirma; // ruta de la carpeta FIRMA

    $rutaenvio = $Prutas->rutaenvio; // ruta de la carpeta FIRMA

    $rutarpta = $Prutas->rutarpta; // ruta de la carpeta FIRMA

    $rutaunzip = $Prutas->unziprpta; // ruta de la carpeta rpta xml



    $connect = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

    mysqli_query($connect, 'SET NAMES "' . DB_ENCODE . '"');

    //Si tenemos un posible error en la conexión lo mostramos

    if (mysqli_connect_errno()) {

      printf("Falló conexión a la base de datos: %s\n", mysqli_connect_error());

      exit();

    }



    $sqlsendmail = "select 

        b.idboleta, 

        p.email,  

        p.nombres, 

        p.apellidos, 

        p.nombre_comercial, 

        e.numero_ruc,

        b.tipo_documento_06,

        b.numeracion_07 

        from 

        boleta b inner join persona p on 

        b.idcliente=p.idpersona inner join empresa e on 

        b.idempresa=e.idempresa 

        where 

        b.idboleta='$idboleta' and e.idempresa='$idempresa' ";



    $result = mysqli_query($connect, $sqlsendmail);



    $con = 0;

    while ($row = mysqli_fetch_assoc($result)) {

      for ($i = 0; $i <= count($result); $i++) {

        $correocliente = $row["email"];

      }



      //Agregar=====================================================

      // Ruta del directorio donde están los archivos

      $path = $rutafirma;

      $files = array_diff(scandir($path), array('.', '..'));

      //=============================================================

      $boleta = $row['numero_ruc'] . "-" . $row['tipo_documento_06'] . "-" . $row['numeracion_07'];



      //Validar si existe el archivo firmado

      foreach ($files as $file) {

        // Divides en dos el nombre de tu archivo utilizando el . 

        $dataSt = explode(".", $file);

        // Nombre del archivo

        $fileName = $dataSt[0];

        $st = "1";

        // Extensión del archivo 

        $fileExtension = $dataSt[1];

        if ($boleta == $fileName) {

          $archivoBoleta = $fileName;

          // Realizamos un break para que el ciclo se interrumpa

          break;

        }

      }

      //$url=$rutafirma.$archivoFactura.'.xml';

      $ZipBoleta = $rutaenvio . $archivoBoleta . '.zip';

      copy($ZipBoleta, $archivoBoleta . '.zip');

      $ZipFinal = $boleta . '.zip';

      //echo $ZipFactura;



      $webservice = $datosc->rutaserviciosunat;

      $usuarioSol = $datosc->usuarioSol;

      $claveSol = $datosc->claveSol;

      $nruc = $datosc->numeroruc;



      //Llamada al WebService=======================================================================

      $service = $webservice;

      $headers = new CustomHeaders($nruc . $usuarioSol, $claveSol);

      $client = new SoapClient(
        $service,
        [

          'cache_wsdl' => WSDL_CACHE_NONE,

          'trace' => TRUE,

          'soap_version' => SOAP_1_1
        ]

      );



      try {

        $client->__setSoapHeaders([$headers]);

        $fcs = $client->__getFunctions();

        $params = array('fileName' => $ZipFinal, 'contentFile' => file_get_contents($ZipFinal));



        //Llamada al WebService=======================================================================

        $status = $client->sendBill($params); // Comando para enviar xml a SUNAT

        $conte = $client->__getLastResponse();

        $texto = trim(strip_tags($conte));





        $zip = new ZipArchive();

        if ($zip->open("R" . $ZipFinal, ZIPARCHIVE::CREATE) === true) {

          $zip->addEmptyDir("dummy");

          $zip->close();
        }





        $rpt = fopen("R" . $ZipFinal, 'w') or die("no se pudo crear archivo");

        fwrite($rpt, base64_decode($texto));

        fclose($rpt);

        rename("R" . $ZipFinal, $rutarpta . "R" . $ZipFinal);

        unlink($ZipFinal);





        $rutarptazip = $rutarpta . "R" . $ZipFinal;

        $zip = new ZipArchive;

        if ($zip->open($rutarptazip) === TRUE) {

          $zip->extractTo($rutaunzip);

          $zip->close();

        }

        $xmlFinal = $rutaunzip . 'R-' . $boleta . '.xml';

        $data[0] = "";

        $rpta[0] = "";

        $sxe = new SimpleXMLElement($xmlFinal, null, true);

        $urn = $sxe->getNamespaces(true);

        $sxe->registerXPathNamespace('cac', $urn['cbc']);

        $data = $sxe->xpath('//cbc:Description');

        $rpta = $sxe->xpath('//cbc:ResponseCode');



        if ($rpta[0] == '0') {

          $msg = "Aceptada por SUNAT";

          $sqlCodigo = "update boleta set CodigoRptaSunat='$rpta[0]', DetalleSunat='$data[0]', estado='5' where idboleta='$idboleta'";

        } else {

          $sqlCodigo = "update boleta set CodigoRptaSunat='$rpta[0]', DetalleSunat='No enviado revizar',
           estado='4' where idboleta='$idboleta'";

        }



        ejecutarConsulta($sqlCodigo);



        return $data[0];





        // Llamada al WebService=======================================================================

      } catch (SoapFault $exception) {



        $exception = print_r($client->__getLastResponse());

      }



    } //Fin While





    //return $exception;



  }







  public function enviarxmlSUNATbajas($idboleta, $idempresa)
  {

    require_once "../modelos/Factura.php";

    $factura = new Factura();

    $datos = $factura->correo();

    $correo = $datos->fetch_object();





    require_once "../modelos/Consultas.php";

    $consultas = new consultas();

    $paramcerti = $consultas->paramscerti();

    $datosc = $paramcerti->fetch_object();



    //Inclusion de la tabla RUTAS

    require_once "../modelos/Rutas.php";

    $rutas = new Rutas();

    $Rrutas = $rutas->mostrar2($idempresa);

    $Prutas = $Rrutas->fetch_object();

    $rutafirma = $Prutas->rutafirma; // ruta de la carpeta FIRMA

    $rutaenvio = $Prutas->rutaenvio; // ruta de la carpeta FIRMA

    $rutarpta = $Prutas->rutarpta; // ruta de la carpeta FIRMA

    $rutaunzip = $Prutas->unziprpta; // ruta de la carpeta rpta xml



    $connect = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

    mysqli_query($connect, 'SET NAMES "' . DB_ENCODE . '"');

    //Si tenemos un posible error en la conexión lo mostramos

    if (mysqli_connect_errno()) {

      printf("Falló conexión a la base de datos: %s\n", mysqli_connect_error());

      exit();

    }



    $sqlsendmail = "select 

        b.idboleta, 

        p.email,  

        p.nombres, 

        p.apellidos, 

        p.nombre_comercial, 

        e.numero_ruc,

        b.tipo_documento_06,

        b.numeracion_07 

        from 

        boleta b inner join persona p on 

        b.idcliente=p.idpersona inner join empresa e on 

        b.idempresa=e.idempresa 

        where 

        b.idboleta='$idboleta' and e.idempresa='$idempresa' ";



    $result = mysqli_query($connect, $sqlsendmail);



    $con = 0;

    while ($row = mysqli_fetch_assoc($result)) {

      for ($i = 0; $i <= count($result); $i++) {

        $correocliente = $row["email"];

      }



      //Agregar=====================================================

      // Ruta del directorio donde están los archivos

      $path = $rutafirma;

      $files = array_diff(scandir($path), array('.', '..'));

      //=============================================================

      $boleta = $row['numero_ruc'] . "-" . $row['tipo_documento_06'] . "-" . $row['numeracion_07'];



      //Validar si existe el archivo firmado

      foreach ($files as $file) {

        // Divides en dos el nombre de tu archivo utilizando el . 

        $dataSt = explode(".", $file);

        // Nombre del archivo

        $fileName = $dataSt[0];

        $st = "1";

        // Extensión del archivo 

        $fileExtension = $dataSt[1];

        if ($boleta == $fileName) {

          $archivoBoleta = $fileName;

          // Realizamos un break para que el ciclo se interrumpa

          break;

        }

      }

      //$url=$rutafirma.$archivoFactura.'.xml';

      $ZipBoleta = $rutaenvio . $archivoBoleta . '.zip';

      copy($ZipBoleta, $archivoBoleta . '.zip');

      $ZipFinal = $boleta . '.zip';

      //echo $ZipFactura;



      $webservice = $datosc->rutaserviciosunat;

      $usuarioSol = $datosc->usuarioSol;

      $claveSol = $datosc->claveSol;

      $nruc = $datosc->numeroruc;



      //Llamada al WebService=======================================================================

      $service = $webservice;

      $headers = new CustomHeaders($nruc . $usuarioSol, $claveSol);

      $client = new SoapClient(
        $service,
        [

          'cache_wsdl' => WSDL_CACHE_NONE,

          'trace' => TRUE,

          'soap_version' => SOAP_1_1
        ]

      );



      try {

        $client->__setSoapHeaders([$headers]);

        $fcs = $client->__getFunctions();

        $params = array('fileName' => $ZipFinal, 'contentFile' => file_get_contents($ZipFinal));



        //Llamada al WebService=======================================================================

        $status = $client->sendBill($params); // Comando para enviar xml a SUNAT

        $conte = $client->__getLastResponse();

        $texto = trim(strip_tags($conte));





        $zip = new ZipArchive();

        if ($zip->open("R" . $ZipFinal, ZIPARCHIVE::CREATE) === true) {

          $zip->addEmptyDir("dummy");

          $zip->close();
        }





        $rpt = fopen("R" . $ZipFinal, 'w') or die("no se pudo crear archivo");

        fwrite($rpt, base64_decode($texto));

        fclose($rpt);

        rename("R" . $ZipFinal, $rutarpta . "R" . $ZipFinal);

        unlink($ZipFinal);





        $rutarptazip = $rutarpta . "R" . $ZipFinal;

        $zip = new ZipArchive;

        if ($zip->open($rutarptazip) === TRUE) {

          $zip->extractTo($rutaunzip);

          $zip->close();

        }

        $xmlFinal = $rutaunzip . 'R-' . $boleta . '.xml';

        $data[0] = "";

        $rpta[0] = "";

        $sxe = new SimpleXMLElement($xmlFinal, null, true);
        $urn = $sxe->getNamespaces(true);
        $sxe->registerXPathNamespace('cac', $urn['cbc']);
        $data = $sxe->xpath('//cbc:Description');
        $rpta = $sxe->xpath('//cbc:ResponseCode');

        $sqlCodigo = "update boleta set CodigoRptaSunat='', DetalleSunat='C/BAJA' where idboleta='$idboleta'";
        ejecutarConsulta($sqlCodigo);


        return $data[0];
        // Llamada al WebService=======================================================================

      } catch (SoapFault $exception) {
        $exception = print_r($client->__getLastResponse());

      }



    } //Fin While

    //return $exception;

  }





  public function mostrarxml($idboleta, $idempresa)
  {

    $connect = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

    mysqli_query($connect, 'SET NAMES "' . DB_ENCODE . '"');

    //Si tenemos un posible error en la conexión lo mostramos

    if (mysqli_connect_errno()) {

      printf("Falló conexión a la base de datos: %s\n", mysqli_connect_error());

      exit();

    }



    require_once "../modelos/Factura.php";

    $factura = new Factura();

    $datos = $factura->datosemp($idempresa);

    $datose = $datos->fetch_object();



    $nombrecomercial = $datose->nombre_comercial;



    //Inclusion de la tabla RUTAS

    require_once "../modelos/Rutas.php";

    $rutas = new Rutas();

    $Rrutas = $rutas->mostrar2($idempresa);

    $Prutas = $Rrutas->fetch_object();

    $rutadata = $Prutas->rutadata; // ruta de la carpeta DATA

    $rutafirma = $Prutas->rutafirma; // ruta de la carpeta FIRMA

    $rutadatalt = $Prutas->rutadatalt; // ruta de la carpeta DATAALTERNA

    $rutaenvio = $Prutas->rutaenvio; // ruta de la carpeta rutaenvio

    $rutaunzipxml = $Prutas->unziprpta; // ruta de la carpeta ruta unziprpta



    $query = "select

     b.tipo_documento_06 as tipocomp, 

     b.numeracion_07 as numerodoc 

     from 

     boleta b inner join persona p on b.idcliente=p.idpersona inner join empresa e on b.idempresa=e.idempresa where idboleta='$idboleta' and b.estado in('1','4','5') order by numerodoc";



    $result = mysqli_query($connect, $query);





    if ($result) {

      while ($row = mysqli_fetch_assoc($result)) {

        for ($i = 0; $i <= count($result); $i++) {

          $tipocomp = $row["tipocomp"];

          $numerodoc = $row["numerodoc"];

          $ruc = $datose->numero_ruc;

        }

      }

      $cabextxml = $rutafirma . $ruc . "-" . $tipocomp . "-" . $numerodoc . ".xml";

      $rpta = array('rutafirma' => $cabextxml);



    } else {



      $rpta = array('rutafirma' => 'Aún no se ha creado el archivo XML.');

    }





    return $rpta;

  }











  public function mostrarrpta($idboleta, $idempresa)
  {

    $connect = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

    mysqli_query($connect, 'SET NAMES "' . DB_ENCODE . '"');

    //Si tenemos un posible error en la conexión lo mostramos

    if (mysqli_connect_errno()) {

      printf("Falló conexión a la base de datos: %s\n", mysqli_connect_error());

      exit();

    }



    require_once "../modelos/Factura.php";

    $factura = new Factura();

    $datos = $factura->datosemp($idempresa);

    $datose = $datos->fetch_object();



    //Inclusion de la tabla RUTAS

    require_once "../modelos/Rutas.php";

    $rutas = new Rutas();

    $Rrutas = $rutas->mostrar2($idempresa);

    $Prutas = $Rrutas->fetch_object();

    $rutarpta = $Prutas->rutarpta; // ruta de la carpeta DATA

    $rutaunzipxml = $Prutas->unziprpta; // ruta de la carpeta ruta unziprpta





    $query = "select

     b.tipo_documento_06 as tipocomp, 

     b.numeracion_07 as numerodoc 

     from 

     boleta b inner join persona p on b.idcliente=p.idpersona inner join empresa e on b.idempresa=e.idempresa where idboleta='$idboleta' and b.estado in('5','4') order by numerodoc";



    $result = mysqli_query($connect, $query);



    $con = 0; //COntador de variable



    while ($row = mysqli_fetch_assoc($result)) {

      for ($i = 0; $i <= count($result); $i++) {

        $tipocomp = $row["tipocomp"];

        $numerodoc = $row["numerodoc"];

        $ruc = $datose->numero_ruc;

      }

    }



    $rutarptazip = $rutarpta . 'R' . $ruc . "-" . $tipocomp . "-" . $numerodoc . ".zip";

    // $zip = new ZipArchive;

    // //en la función open se le pasa la ruta de nuestro archivo (alojada en carpeta temporal)

    // if ($zip->open($rutarptazip) === TRUE) 

    // {

    //   //función para extraer el ZIP, le pasamos la ruta donde queremos que nos descomprima

    //   $zip->extractTo($rutaunzipxml);

    //   $zip->close();

    // }

    $rutaxmlrpta = $rutaunzipxml . 'R-' . $ruc . "-" . $tipocomp . "-" . $numerodoc . ".xml";

    $rpta = array('rpta' => $rutarptazip, 'rutaxmlr' => $rutaxmlrpta);

    return $rpta;

  }




  public function almacenlista()
  {

    $sql = "select * from almacen where estado='1' order by idalmacen";
    return ejecutarConsulta($sql);
  }








































  // $connect = new mysqli(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);

  //     mysqli_query( $connect, 'SET NAMES "'.DB_ENCODE.'"');

  //     //Si tenemos un posible error en la conexión lo mostramos

  //     if (mysqli_connect_errno())

  //     {

  //           printf("Falló conexión a la base de datos: %s\n",mysqli_connect_error());

  //           exit();

  //     }



  //   require_once "../modelos/Factura.php";

  //   $factura = new Factura();

  //   $datos = $factura->datosemp($idempresa);

  //   $datose = $datos->fetch_object();



  //    //Inclusion de la tabla RUTAS

  //   require_once "../modelos/Rutas.php";

  //   $rutas = new Rutas();

  //   $Rrutas = $rutas->mostrar2($idempresa);

  //   $Prutas = $Rrutas->fetch_object();

  //   $rutadata=$Prutas->rutadata; // ruta de la carpeta DATA

  //   $rutadatalt=$Prutas->rutadatalt; // ruta de la carpeta DATA



  // $query = "select 

  // date_format(fecha_emision_01, '%Y-%m-%d') as fecha, 

  // right(substring_index(numeracion_07,'-',1),4) as serie, 

  // date_format(fecha_emision_01, '%H:%i:%s') as hora,

  // p.tipo_documento, 

  // p.numero_documento as rucCliente, 

  // p.razon_social as RazonSocial, 

  // tipo_moneda_24, 

  // monto_15_2 as subtotal, 

  // sumatoria_igv_18_1 as igv, 

  // importe_total_23 as total, 

  // tipo_documento_06 as tipocomp, 

  // numeracion_07 as numerodoc, 

  // b.estado,

  // b.tdescuento ,

  // b.codigo_tributo_18_3 as codigotrib,

  // b.nombre_tributo_18_4  as nombretrib,

  // b.codigo_internacional_18_5 as codigointtrib

  // from

  // boleta b inner join persona p on b.idcliente=p.idpersona 

  // where idboleta='$idBoletaNew' and b.estado='1'  order by numerodoc";  





  // $querydetbol = "select

  //  b.tipo_documento_06 as tipocomp, 

  //  b.numeracion_07 as numerodoc, 

  //  db.cantidad_item_12 as cantidad, 

  //  a.codigo, 

  //  a.nombre as descripcion, 

  //  a.unidad_medida as um,

  //  replace(format(db.valor_uni_item_31, 5),',','') as vui, 

  //  db.afectacion_igv_item_monto_27_1 as igvi, 

  //  db.precio_uni_item_14_2 as pvi,

  //  db.valor_venta_item_32 as vvi,



  //   db.afectacion_igv_item_monto_27_1 as sutribitem,



  //      db.afectacion_igv_3 as aigv,

  //      db.afectacion_igv_4 codtrib,

  //      db.afectacion_igv_5 as nomtrib,

  //      db.afectacion_igv_6 as coditrib,

  //      a.codigosunat

  //  from

  //  boleta b inner join detalle_boleta_producto db on b.idboleta=db.idboleta inner join articulo a on db.idarticulo=a.idarticulo where b.idboleta='$idBoletaNew' and b.estado='1' order by b.fecha_emision_01"; 





  // $result = mysqli_query($connect, $query);  

  // $resultb = mysqli_query($connect, $querydetbol);



  //     $fecha=array();

  //     $serie=array();

  //     $tipodocu=array();

  //     $numdocu=array();

  //     $rasoc=array();

  //     $moneda=array();

  //      $codigotrib=array();

  //     $nombretrib=array();

  //     $codigointtrib=array();

  //     $subtotal=array();

  //     $igv=array();

  //     $total=array();

  //     $tdescu=array();





  //     $con=0;



  //     while($row=mysqli_fetch_assoc($result)){

  //     for($i=0; $i <= count($result); $i++){

  //          $fecha[$i]=$row["fecha"];

  //          $serie[$i]=$row["serie"];

  //          $tipodocu[$i]=$row["tipo_documento"];

  //          $numdocu[$i]=$row["rucCliente"];

  //          $rasoc[$i]=$row["RazonSocial"];

  //          $moneda[$i]=$row["tipo_moneda_24"];

  //          $subtotal[$i]=$row["subtotal"];

  //          $igv[$i]=$row["igv"];

  //          $total[$i]=$row["total"];

  //          $tipocomp=$row["tipocomp"];

  //          $tdescu[$i]=$row["tdescuento"];

  //          $numerodoc=$row["numerodoc"];

  //          $hora=$row["hora"];

  //          $ruc=$datose->numero_ruc;

  //          $ubigueo=$datose->ubigueo;



  //         $codigotrib[$i]=$row["codigotrib"];

  //          $nombretrib[$i]=$row["nombretrib"];

  //          $codigointtrib[$i]=$row["codigointtrib"];









  //         require_once "Letras.php";

  //         $V=new EnLetras(); 

  //         $con_letra=strtoupper($V->ValorEnLetras($total[$i],"NUEVOS SOLES"));

  //         // $path=$rutadata.$ruc."-".$tipocomp."-".$numerodoc.".ley";

  //         // $handle=fopen($path, "w");

  //         // fwrite($handle,"1000|".$con_letra."|"); 

  //         // fclose($handle);



  //         // $path=$rutadata.$ruc."-".$tipocomp."-".$numerodoc.".tri";

  //         // $handle=fopen($path, "w");

  //         // fwrite($handle,"1000|IGV|VAT|".$subtotal[$i]."|".$igv[$i]."|"); 

  //         // //fwrite($handle,"1000|IGV|VAT|S|".$subtotal[$i]."|".$igv[$i]."|");  VERSION 1.1

  //         // fclose($handle);



  //         //  $path=$rutadatalt.$ruc."-".$tipocomp."-".$numerodoc.".cab";

  //         //  $handle=fopen($path, "w");

  //         //  fwrite($handle,"0101|".$fecha[$i]."|".$hora."|-|0000|".$tipodocu[$i]."|".$numdocu[$i]."|".$rasoc[$i]."|".$moneda[$i]."|".$igv[$i]."|".$subtotal[$i]."|".$total[$i]."|".$tdescu[$i]."|0|0|".$total[$i]."|2.1|2.0|"); 

  //         //  fclose($handle);





  //     //FORMATO JSON

  //     $json = array('cabecera' => array('tipOperacion'=>'0101', 'fecEmision'=>$fecha[$i], 'horEmision'=>$hora, 'fecVencimiento'=>"-", 'codLocalEmisor'=>$ubigueo, 'tipDocUsuario'=>$tipodocu[$i], 'numDocUsuario'=>$numdocu[$i], 'rznSocialUsuario'=>$rasoc[$i], 'tipMoneda'=>$moneda[$i], 'sumTotTributos'=>number_format($igv[$i],2,'.',''), 'sumTotValVenta'=>number_format($subtotal[$i],2,'.',''), 'sumPrecioVenta'=>number_format($total[$i],2,'.',''), 'sumDescTotal'=>number_format($tdescu[$i],2,'.',''), 'sumOtrosCargos'=>"0.00", 'sumTotalAnticipos'=>"0.00", 'sumImpVenta'=>number_format($total[$i],2,'.',''), 'ublVersionId'=>"2.1", 'customizationId'=>"2.0"), 'detalle' => array(), 'leyendas' => array(), 'tributos' => array());





  //     //Leyenda JSON

  //     $json['leyendas'][] = array('codLeyenda'=>"1000",'desLeyenda'=>$con_letra);

  //     $json['tributos'][] = array('ideTributo'=>$codigotrib[$i], 'nomTributo'=>$nombretrib[$i], 'codTipTributo'=>$codigointtrib[$i], 'mtoBaseImponible'=>number_format($subtotal[$i],2,'.',''), 'mtoTributo'=>number_format($igv[$i],2,'.',''));

  //     //Leyenda JSON

  //     }

  //          $i=$i+1;

  //          $con=$con+1;           

  //     }







  //     $codigo=array();

  //     $cantidad=array();

  //     $descripcion=array();

  //     $vui=array();

  //     $igvi=array();

  //     $pvi=array();

  //     $vvi=array();

  //     $um=array();



  //     $sutribitem=array();



  //     $aigv=array();

  //     $codtrib=array();

  //     $nomtrib=array();

  //     $coditrib=array();

  //     $codigosunat=array();





  //     while($rowb=mysqli_fetch_assoc($resultb)){

  //     for($if=0; $if < count($resultb); $if++){

  //          $codigo[$if]=$rowb["codigo"];

  //          $cantidad[$if]=$rowb["cantidad"];

  //          $descripcion[$if]=$rowb["descripcion"];

  //          $vui[$if]=$rowb["vui"];

  //          $igvi[$if]=$rowb["igvi"];

  //          $pvi[$if]=$rowb["pvi"];

  //          $vvi[$if]=$rowb["vvi"];

  //          $um[$if]=$rowb["um"];

  //          $tipocompb=$rowb["tipocomp"];

  //          $numerodocb=$rowb["numerodoc"];

  //          $ruc=$datose->numero_ruc;

  //          $sutribitem[$if]=$rowb["sutribitem"];           



  //          $aigv[$if]=$rowb["aigv"];

  //          $codtrib[$if]=$rowb["codtrib"];

  //          $nomtrib[$if]=$rowb["nomtrib"];

  //          $coditrib[$if]=$rowb["coditrib"];

  //          $codigosunat[$if]=$rowb["codigosunat"];



  //       //  $pathb=$rutadata.$ruc."-".$tipocompb."-".$numerodocb.".det";

  //       //  $handleb=fopen($pathb, "a");

  //       // fwrite($handleb, $um[$if]."|".$cantidad[$if]."|".$codigo[$if]."|-|".$descripcion[$if]."|".$vui[$if]."|".$igvi[$if]."|1000|".$igvi[$if]."|".$vvi[$if]."|IGV|VAT|10|18|-|||||||-||||||".$pvi[$if]."|".$vvi[$if]."|0|\r\n"); 

  //       //    fclose($handleb);



  //       //    $pathb=$rutadatalt.$ruc."-".$tipocompb."-".$numerodocb.".det";

  //       //  $handleb=fopen($pathb, "a");

  //       // fwrite($handleb,$um[$if]."|".$cantidad[$if]."|".$codigo[$if]."|-|".$descripcion[$if]."|".$vui[$if]."|".$igvi[$if]."|1000|".$igvi[$if]."|".$vui[$if]."|IGV|VAT|10|18|-|||||||-||||||".$pvi[$if]."|".$vvi[$if]."|0|\r\n"); 

  //       //    fclose($handleb);



  //   //FORMATO JSON

  //   $json['detalle'][] = array('codUnidadMedida'=>$um[$if], 'ctdUnidadItem'=>number_format($cantidad[$if],2,'.',''), 'codProducto'=>$codigo[$if], 'codProductoSUNAT'=>$codigosunat[$if], 'desItem'=>$descripcion[$if], 'mtoValorUnitario'=>number_format($vui[$if],5,'.',''), 'sumTotTributosItem'=>number_format($sutribitem[$if],2,'.',''), 'codTriIGV'=>$codtrib[$if], 'mtoIgvItem'=>number_format($sutribitem[$if],2,'.',''), 'mtoBaseIgvItem'=>number_format($vvi[$if],2,'.',''), 'nomTributoIgvItem'=>$nomtrib[$if], 'codTipTributoIgvItem'=>$coditrib[$if], 'tipAfeIGV'=>$aigv[$if], 'porIgvItem'=>"18.0", 'codTriISC'=>"-", 'mtoIscItem'=>"", 'mtoBaseIscItem'=>"", 'nomTributoIscItem'=>"", 'codTipTributoIscItem'=>"", 'tipSisISC'=>"", 'porIscItem'=>"", 'codTriOtroItem'=>"-", 'mtoTriOtroItem'=>"", 'mtoBaseTriOtroItem'=>"", 'nomTributoIOtroItem'=>"", 'codTipTributoIOtroItem'=>"", 'porTriOtroItem'=>"", 'mtoPrecioVentaUnitario'=>number_format($pvi[$if],2,'.',''), 'mtoValorVentaItem'=>number_format($vvi[$if],2,'.',''), 'mtoValorReferencialUnitario'=>"0");



  //     }

  //     }



  //     $path=$rutadata.$ruc."-".$tipocomp."-".$numerodoc.".json";

  //     $jsonencoded = json_encode($json,JSON_UNESCAPED_UNICODE);

  //     $fh = fopen($path, 'w');

  //     fwrite($fh, $jsonencoded);

  //     fclose($fh);





  //============================================ REPORTE ===================================================

  //Obtenemos los datos de la cabecera de la venta actual

  // require_once "../modelos/Boleta.php";

  // require('../reportes/Boleta.php');

  // $boleta = new Boleta();

  // $rsptav = $boleta->ventacabecera($idBoletaNew, $idempresa);

  // $datos = $boleta->datosemp($idempresa);

  // //Recorremos todos los valores obtenidos

  // $regv = $rsptav->fetch_object();

  // $datose = $datos->fetch_object(); 

  // $logo = "../files/logo/".$datose->logo;

  // $ext_logo = substr($datose->logo, strpos($datose->logo,'.'),-4);

  // //Establecemos la configuración de la factura

  // $pdf = new PDF_Invoice( 'P', 'mm', 'A4' );

  // $pdf->AddPage();

  // #Establecemos los márgenes izquierda, arriba y derecha: 

  // $pdf->SetMargins(10, 10 , 10); 

  // #Establecemos el margen inferior: 

  // $pdf->SetAutoPageBreak(true,10); 

  // //Enviamos los datos de la empresa al método addSociete de la clase Factura

  // $pdf->addSociete(utf8_decode(htmlspecialchars_decode($datose->nombre_comercial)),utf8_decode("Dirección:     ").utf8_decode($datose->domicilio_fiscal)."\n".utf8_decode("Teléfono:     ").$datose->telefono1." - ".$datose->telefono2."\n" ."Email:          ".$datose->correo, $logo, $ext_logo);

  // $pdf->numBoleta("$regv->numeracion_07",  "$datose->numero_ruc" );

  // //Datos de la empresa

  // $pdf->RotatedText($regv->estado, 35,190,'ANULADO - DADO DE BAJA',45);

  // $pdf->temporaire( "" );

  // //Enviamos los datos del cliente al método addClientAdresse de la clase Factura

  // $pdf->addClientAdresse( $regv->fecha."   /  Hora: ".$regv->hora, utf8_decode($regv->cliente),utf8_decode($regv->direccion), $regv->numero_documento,$regv->estado, utf8_decode($regv->vendedorsitio), utf8_decode($regv->guia));

  // if ($regv->nombretrib=="IGV") {

  //         $nombret="PRECIO";

  //     }else{

  //         $nombret="PRECIO";

  //     }

  // //Establecemos las columnas que va a tener la sección donde mostramos los detalles de la venta

  // $cols=array( "CODIGO"=>23,

  //              "DESCRIPCION"=>78,

  //              "CANTIDAD"=>22,

  //              $nombret=>25,

  //              "DSCTO"=>20,

  //              "SUBTOTAL"=>22);

  // $pdf->addCols( $cols);

  // $cols=array( "CODIGO"=>"L",

  //              "DESCRIPCION"=>"L",

  //              "CANTIDAD"=>"C",

  //              $nombret=>"R",

  //              "DSCTO" =>"R",

  //              "SUBTOTAL"=>"C");

  // $pdf->addLineFormat( $cols);

  // $pdf->addLineFormat($cols);

  // //Actualizamos el valor de la coordenada "y", que será la ubicación desde donde empezaremos a mostrar los datos

  // $y= 62;

  // //Obtenemos todos los detalles de la venta actual

  // $rsptad = $boleta->ventadetalle($idBoletaNew);

  // while ($regd = $rsptad->fetch_object()) {

  //     if ($regd->nombretribu=="IGV") {

  //         $pv=$regd->precio_uni_item_14_2;

  //         //$pv=$regd->valor_uni_item_31;

  //         $subt=$regd->subtotal;

  //     }else{

  //         $pv=$regd->precio_uni_item_14_2;

  //         $subt=$regd->subtotal2;

  //     }

  //   $line = array( "CODIGO"=> "$regd->codigo",

  //                 "DESCRIPCION"=>  utf8_decode(htmlspecialchars_decode("$regd->articulo"." - "."$regd->descdet")),

  //                 "CANTIDAD"=> "$regd->cantidad_item_12"." "."$regd->unidad_medida",

  //                 $nombret=> $pv,

  //                 "DSCTO" => "$regd->dcto_item",

  //                 "SUBTOTAL"=> "$regd->subtotal");

  //             $size = $pdf->addLine( $y, $line );

  //             $y   += $size + 2;

  // }

  // //======= PARA EXTRAER EL HASH DEL DOCUMENTO FIRMADO ========================================

  //     require_once "../modelos/Rutas.php";

  //     $rutas = new Rutas();

  //     $Rrutas = $rutas->mostrar2($idempresa);

  //     $Prutas = $Rrutas->fetch_object();

  //     $rutafirma=$Prutas->rutafirma; // ruta de la carpeta FIRMA

  //     $data[0] = "";



  // if ($regv->estado=='5') {

  // $boletaFirm=$regv->numero_ruc."-".$regv->tipo_documento_06."-".$regv->numeracion_07;

  // $sxe = new SimpleXMLElement($rutafirma.$boletaFirm.'.xml', null, true);

  // $urn = $sxe->getNamespaces(true);

  // $sxe->registerXPathNamespace('ds', $urn['ds']);

  // $data = $sxe->xpath('//ds:DigestValue');

  // }

  // else

  // {

  //      $data[0] = "";

  // }

  // //======= PARA EXTRAER EL HASH DEL DOCUMENTO FIRMADO ========================================

  // //Convertimos el total en letras

  // require_once "Letras.php";

  // $V=new EnLetras(); 

  // $con_letra=strtoupper($V->ValorEnLetras($regv->totalLetras,"CON"));

  // $pdf->addCadreTVAs("".$con_letra);

  // $pdf->observSunat($regv->numeracion_07, $regv->estado, $data[0], $datose->webconsul , $datose->nresolucion);

  // //Mostramos el impuesto

  // $pdf->addTVAs($regv->Itotal,"S/ ",  $regv->tdescuento);

  // $pdf->addCadreEurosFrancs();

  // // //==================== PARA IMAGEN DEL CODIGO HASH ================================================

  // // //set it to writable location, a place for temp generated PNG files

  //     $PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'../reportes/generador-qr/temp'.DIRECTORY_SEPARATOR;

  //     //html PNG location prefix

  //     $PNG_WEB_DIR = 'temp/';

  //     include '../reportes/generador-qr/phpqrcode.php';    



  //     //ofcourse we need rights to create temp dir

  //     if (!file_exists($PNG_TEMP_DIR))

  //         mkdir($PNG_TEMP_DIR);

  //     $filename = $PNG_TEMP_DIR.'test.png';

  //     //processing form input

  //     //remember to sanitize user input in real-life solution !!!

  //      $dataTxt=$regv->numero_ruc."|".$regv->tipo_documento_06."|".$regv->serie."|".$regv->numerofac."|0.00|".$regv->Itotal."|".$regv->fecha2."|".$regv->tipo_documento."|".$regv->numero_documento."|";;

  //     $errorCorrectionLevel = 'H';    

  //     $matrixPointSize = '2';

  //     // user data

  //         $filename = $PNG_TEMP_DIR.'test'.md5($dataTxt.'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';

  //         QRcode::png($dataTxt, $filename, $errorCorrectionLevel, $matrixPointSize, 2);    

  //         //default data

  //         //QRcode::png('PHP QR Code :)', $filename, $errorCorrectionLevel, $matrixPointSize, 2);    

  //        //display generated file

  //         $PNG_WEB_DIR.basename($filename);

  // // // //==================== PARA IMAGEN DEL CODIGO HASH ================================================

  // $logoQr = $filename;

  // //$logoQr = "../files/logo/".$datose->logo;

  // $ext_logoQr = substr($filename, strpos($filename,'.'),0);

  // $pdf->ImgQr($logoQr, $ext_logoQr);

  // //===============SEGUNDA COPIA DE BOLETA=========================



  // //Enviamos los datos de la empresa al método addSociete de la clase Factura

  // $pdf->addSociete2(utf8_decode(htmlspecialchars_decode($datose->nombre_comercial)),utf8_decode("Dirección: ").utf8_decode($datose->domicilio_fiscal)."\n".utf8_decode("Teléfono: ").$datose->telefono1." - ".$datose->telefono2."\n"."Email : ".$datose->correo, $logo, $ext_logo);

  //  //Datos de la empresa

  //  $pdf->numBoleta2("$regv->numeracion_07",  "$datose->numero_ruc" );

  //  $pdf->temporaire( "" );

  //  //Enviamos los datos del cliente al método addClientAdresse de la clase Factura

  //  $pdf->addClientAdresse2( $regv->fecha."  /  Hora: ".$regv->hora, utf8_decode($regv->cliente),utf8_decode($regv->direccion), $regv->numero_documento,$regv->estado, utf8_decode($regv->vendedorsitio), utf8_decode($regv->guia));

  //  //Establecemos las columnas que va a tener la sección donde mostramos los detalles de la venta

  // $cols=array( "CODIGO"=>23,

  //              "DESCRIPCION"=>78,

  //              "CANTIDAD"=>22,

  //              $nombret=>25,

  //              "DSCTO"=>20,

  //              "SUBTOTAL"=>22);

  // $pdf->addCols2( $cols);

  // $cols=array( "CODIGO"=>"L",

  //              "DESCRIPCION"=>"L",

  //              "CANTIDAD"=>"C",

  //              $nombret=>"R",

  //              "DSCTO" =>"R",

  //              "SUBTOTAL"=>"C");

  // $pdf->addLineFormat2( $cols);

  // $pdf->addLineFormat2($cols);

  // //Actualizamos el valor de la coordenada "y", que será la ubicación desde donde empezaremos a mostrar los datos

  // $y2= 208;

  // //Obtenemos todos los detalles de la venta actual

  // $rsptad = $boleta->ventadetalle($idBoletaNew);

  // while ($regd = $rsptad->fetch_object()) {

  //   if ($regd->nombretribu=="IGV") {

  //         $pv=$regd->precio_uni_item_14_2;

  //         $subt=$regd->subtotal;

  //     }else{

  //         $pv=$regd->precio_uni_item_14_2;

  //         $subt=$regd->subtotal2;

  //     }

  //   $line = array( "CODIGO"=> "$regd->codigo",

  //                 "DESCRIPCION"=>  utf8_decode(htmlspecialchars_decode("$regd->articulo"." - "."$regd->descdet")),

  //                 "CANTIDAD"=> "$regd->cantidad_item_12"." "."$regd->unidad_medida",

  //                 $nombret=> $pv,

  //                 "DSCTO" => "$regd->dcto_item",

  //                 "SUBTOTAL"=> "$regd->subtotal");

  //             $size2 = $pdf->addLine2( $y2, $line );

  //             $y2   += $size2 + 2;

  // }

  // $V=new EnLetras(); 

  // $con_letra=strtoupper($V->ValorEnLetras($regv->totalLetras,"CON"));

  // $pdf->addCadreTVAs2("".$con_letra);

  // $pdf->observSunat2($regv->numeracion_07,$regv->estado,$data[0], $datose->webconsul , $datose->nresolucion);

  // //Mostramos el impuesto

  // $pdf->addTVAs2( $regv->Itotal,"S/ ",  $regv->tdescuento);

  // $pdf->addCadreEurosFrancs2();

  // //==========================================================================

  // $Factura=$pdf->Output('../boletasPDF/'.$regv->numeracion_07.'.pdf','F');

  //============================================ REPORTE ===================================================



  public function reconsultarcdr($idboleta, $idempresa)
  {
    require_once "../modelos/Factura.php";
    $factura = new Factura();
    $datos = $factura->correo();
    $correo = $datos->fetch_object();


    require_once "../modelos/Consultas.php";
    $consultas = new consultas();
    $paramcerti = $consultas->paramscerti();
    $datosc = $paramcerti->fetch_object();

    //Inclusion de la tabla RUTAS
    require_once "../modelos/Rutas.php";
    $rutas = new Rutas();
    $Rrutas = $rutas->mostrar2($idempresa);
    $Prutas = $Rrutas->fetch_object();
    $rutafirma = $Prutas->rutafirma; // ruta de la carpeta FIRMA
    $rutaenvio = $Prutas->rutaenvio; // ruta de la carpeta FIRMA
    $rutarpta = $Prutas->rutarpta; // ruta de la carpeta FIRMA
    $rutaunzip = $Prutas->unziprpta; // ruta de la carpeta rpta xml

    $connect = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    mysqli_query($connect, 'SET NAMES "' . DB_ENCODE . '"');
    //Si tenemos un posible error en la conexión lo mostramos
    if (mysqli_connect_errno()) {
      printf("Falló conexión a la base de datos: %s\n", mysqli_connect_error());
      exit();
    }

    $sqlsendmail = "select 
        b.idboleta, 
        p.email,  
        p.nombres, 
        p.apellidos, 
        p.nombre_comercial, 
        e.numero_ruc,
        b.tipo_documento_06,
        b.numeracion_07,
        substring(b.numeracion_07,1,4) as serie,
        substring(b.numeracion_07,6) as numero
        from 
        boleta b inner join persona p on 
        b.idcliente=p.idpersona inner join empresa e on 
        b.idempresa=e.idempresa 
        where 
        b.idboleta='$idboleta' and e.idempresa='$idempresa' ";

    $result = mysqli_query($connect, $sqlsendmail);

    $con = 0;
    while ($row = mysqli_fetch_assoc($result)) {
      for ($i = 0; $i <= count($result); $i++) {
        $correocliente = $row["email"];
      }

      //Agregar=====================================================
      // Ruta del directorio donde están los archivos
      $path = $rutafirma;
      $files = array_diff(scandir($path), array('.', '..'));
      //=============================================================
      $boleta = $row['numero_ruc'] . "-" . $row['tipo_documento_06'] . "-" . $row['numeracion_07'];

      //Validar si existe el archivo firmado
      foreach ($files as $file) {
        // Divides en dos el nombre de tu archivo utilizando el . 
        $dataSt = explode(".", $file);
        // Nombre del archivo
        $fileName = $dataSt[0];
        $st = "1";
        // Extensión del archivo 
        $fileExtension = $dataSt[1];
        if ($boleta == $fileName) {
          $archivoBoleta = $fileName;
          // Realizamos un break para que el ciclo se interrumpa
          break;
        }
      }
      //$url=$rutafirma.$archivoFactura.'.xml';
      $ZipBoleta = $rutaenvio . $archivoBoleta . '.zip';
      copy($ZipBoleta, $archivoBoleta . '.zip');
      $ZipFinal = $boleta . '.zip';
      //echo $ZipFactura;

      $webservice = $datosc->rutaserviciosunat;
      $usuarioSol = $datosc->usuarioSol;
      $claveSol = $datosc->claveSol;
      $nruc = $datosc->numeroruc;

      //Llamada al WebService=======================================================================
      //$service = $webservice;
      $service = "https://e-factura.sunat.gob.pe/ol-it-wsconscpegem/billConsultService?wsdl";

      $headers = new CustomHeaders($nruc . $usuarioSol, $claveSol);
      $client = new SoapClient(
        $service,
        [
          'cache_wsdl' => WSDL_CACHE_NONE,
          'trace' => TRUE,
          'soap_version' => SOAP_1_1
        ]
      );
      try {
        $client->__setSoapHeaders([$headers]);
        $fcs = $client->__getFunctions();
        $params = [
          'rucComprobante' => $nruc,
          'tipoComprobante' => $row['tipo_documento_06'],
          'serieComprobante' => $row['serie'],
          'numeroComprobante' => $row['numero'],
        ];

        //Llamada al WebService=======================================================================
        $response = $client->__soapCall('getStatusCdr', ['parameters' => $params]);
        isset($response->statusCdr->content) ? file_put_contents($rutarpta . "R" . $ZipFinal, $response->statusCdr->content) : '';
        $result = (object) [
          'statusCode' => $response->statusCdr->statusCode,
          'statusMessage' => $response->statusCdr->statusMessage,
          'cdr' => $ZipFinal
        ];


        if ($response->statusCdr->statusCode == "0004") {

          $zip = new ZipArchive();
          if ($zip->open("R" . $ZipFinal, ZIPARCHIVE::CREATE) === true) {
            $zip->addEmptyDir("dummy");
            $zip->close();
          }

          $rutarptazip = $rutarpta . "R" . $ZipFinal;
          $zip = new ZipArchive;
          if ($zip->open($rutarptazip) === TRUE) {
            $zip->extractTo($rutaunzip);
            $zip->close();
          }
          $xmlFinal = $rutaunzip . 'R-' . $boleta . '.xml';
          $data[0] = "";
          $rpta[0] = "";
          $sxe = new SimpleXMLElement($xmlFinal, null, true);
          $urn = $sxe->getNamespaces(true);
          $sxe->registerXPathNamespace('cac', $urn['cbc']);
          $data = $sxe->xpath('//cbc:Description');
          $rpta = $sxe->xpath('//cbc:ResponseCode');

          if ($rpta[0] == '0') {
            $msg = "Aceptada por SUNAT";
            $sqlCodigo = "update boleta set CodigoRptaSunat='$rpta[0]', DetalleSunat='$data[0]', estado='5' where idboleta='$idboleta'";
          } else {
            $sqlCodigo = "update boleta set CodigoRptaSunat='$rpta[0]', DetalleSunat='$data[0]', estado='4' where idboleta='$idboleta'";
          }
          ejecutarConsulta($sqlCodigo);
          return $response->statusCdr->statusMessage . " para comprobante: " . $ZipFinal;

        } else {

          return $response->statusCdr->statusCode;
        }
        // Llamada al WebService=======================================================================
      } catch (SoapFault $exception) {
        $exception = print_r($client->__getLastResponse());
      }
    } //Fin While
    //return $cdr->statusCode;;
  }


  public function mostrartipocambio($fecha)
  {

    $sql = "select idtipocambio, date_format(fecha, '%Y-%m-%d') as fecha, compra, venta from tcambio where fecha='$fecha'";
    return ejecutarConsultaSimpleFila($sql);
  }




  public function cambiartarjetadc($idboleta, $opcion)
  {
    // SEGURIDAD: Usar prepared statement para prevenir SQL Injection
    if ($opcion == '1') {
      $sql = "UPDATE boleta SET tarjetadc=? WHERE idboleta=?";
      return ejecutarConsultaPreparada($sql, "si", [$opcion, $idboleta]);
    } else {
      $sql = "UPDATE boleta SET tarjetadc=?, montotarjetadc='0' WHERE idboleta=?";
      return ejecutarConsultaPreparada($sql, "si", [$opcion, $idboleta]);
    }
  }


  public function montotarjetadc($idboleta, $mto)
  {
    // SEGURIDAD: Usar prepared statement para prevenir SQL Injection
    $sql = "UPDATE boleta SET montotarjetadc=? WHERE idboleta=?";
    return ejecutarConsultaPreparada($sql, "di", [$mto, $idboleta]);
  }




  public function cambiartransferencia($idboleta, $opcion)
  {
    // SEGURIDAD: Usar prepared statement para prevenir SQL Injection
    if ($opcion == '1') {
      $sql = "UPDATE boleta SET transferencia=? WHERE idboleta=?";
      return ejecutarConsultaPreparada($sql, "si", [$opcion, $idboleta]);
    } else {
      $sql = "UPDATE boleta SET transferencia=?, montotransferencia='0' WHERE idboleta=?";
      return ejecutarConsultaPreparada($sql, "si", [$opcion, $idboleta]);
    }
  }


  public function montotransferencia($idboleta, $mto)
  {
    // SEGURIDAD: Usar prepared statement para prevenir SQL Injection
    $sql = "UPDATE boleta SET montotransferencia=? WHERE idboleta=?";
    return ejecutarConsultaPreparada($sql, "di", [$mto, $idboleta]);
  }




  public function duplicar($idboleta)
  {
    global $conexion;

    // Iniciar transacción
    mysqli_begin_transaction($conexion);

    try {
      // ============= PASO 1: OBTENER SERIE DE LA BOLETA ORIGINAL =============
      $sql_serie = "SELECT LEFT(numeracion_07, 4) AS serie FROM boleta WHERE idboleta = ?";
      
      $stmt_serie = $conexion->prepare($sql_serie);
      if (!$stmt_serie) {
        error_log("Error preparando SELECT serie en duplicar(): " . $conexion->error);
        mysqli_rollback($conexion);
        return false;
      }
      
      $stmt_serie->bind_param("i", $idboleta);
      
      if (!$stmt_serie->execute()) {
        error_log("Error ejecutando SELECT serie en duplicar(): " . $stmt_serie->error);
        $stmt_serie->close();
        mysqli_rollback($conexion);
        return false;
      }
      
      $result_serie = $stmt_serie->get_result();
      $serie = "";
      
      // Procesar resultado directamente en while (SIN for loop)
      if ($row = $result_serie->fetch_assoc()) {
        $serie = $row["serie"];
      }
      $stmt_serie->close();
      
      if (empty($serie)) {
        error_log("No se encontró serie para boleta $idboleta en duplicar()");
        mysqli_rollback($conexion);
        return false;
      }
      
      // ============= PASO 2: OBTENER SIGUIENTE NÚMERO DE LA SERIE =============
      $sql_numero = "SELECT numero FROM numeracion WHERE serie = ?";
      
      $stmt_numero = $conexion->prepare($sql_numero);
      if (!$stmt_numero) {
        error_log("Error preparando SELECT numero en duplicar(): " . $conexion->error);
        mysqli_rollback($conexion);
        return false;
      }
      
      $stmt_numero->bind_param("s", $serie);
      
      if (!$stmt_numero->execute()) {
        error_log("Error ejecutando SELECT numero en duplicar(): " . $stmt_numero->error);
        $stmt_numero->close();
        mysqli_rollback($conexion);
        return false;
      }
      
      $result_numero = $stmt_numero->get_result();
      $nnumero = 0;
      
      // Procesar resultado directamente en while (SIN for loop)
      if ($row = $result_numero->fetch_assoc()) {
        $nnumero = $row["numero"];
      }
      $stmt_numero->close();
      
      // Incrementar número
      $nnumero = $nnumero + 1;
      $numeracion_nueva = $serie . '-' . $nnumero;
      
      // ============= PASO 3: INSERTAR NUEVA BOLETA (COPIA) =============
      $sql_cabecera = "INSERT INTO boleta (
        idusuario, fecha_emision_01, firma_digital_36, idempresa, tipo_documento_06, numeracion_07,
        idcliente, codigo_tipo_15_1, monto_15_2, sumatoria_igv_18_1, sumatoria_igv_18_2,
        codigo_tributo_18_3, nombre_tributo_18_4, codigo_internacional_18_5, importe_total_23,
        codigo_leyenda_26_1, descripcion_leyenda_26_2, tipo_documento_25_1, guia_remision_25,
        version_ubl_37, version_estructura_38, tipo_moneda_24, tasa_igv,
        tipodocuCliente, rucCliente, RazonSocial, fecha_baja, comentario_baja,
        tdescuento, vendedorsitio, icbper, CodigoRptaSunat, DetalleSunat, tcambio,
        transferencia, ntrans, hashc, montotransferencia, tarjetadc, montotarjetadc,
        formapago, montofpago, monedafpago, ccuotas, montocuota, fechavecredito
      )
      SELECT
        idusuario, fecha_emision_01, firma_digital_36, idempresa, tipo_documento_06, ?,
        idcliente, codigo_tipo_15_1, monto_15_2, sumatoria_igv_18_1, sumatoria_igv_18_2,
        codigo_tributo_18_3, nombre_tributo_18_4, codigo_internacional_18_5, importe_total_23,
        codigo_leyenda_26_1, descripcion_leyenda_26_2, tipo_documento_25_1, guia_remision_25,
        version_ubl_37, version_estructura_38, tipo_moneda_24, tasa_igv,
        tipodocuCliente, rucCliente, RazonSocial, fecha_baja, comentario_baja,
        tdescuento, vendedorsitio, icbper, CodigoRptaSunat, 'EMITIDO', tcambio,
        transferencia, ntrans, hashc, montotransferencia, tarjetadc, montotarjetadc,
        formapago, montofpago, monedafpago, ccuotas, montocuota, fechavecredito
      FROM boleta
      WHERE idboleta = ?";
      
      $stmt_cabecera = $conexion->prepare($sql_cabecera);
      if (!$stmt_cabecera) {
        error_log("Error preparando INSERT boleta en duplicar(): " . $conexion->error);
        mysqli_rollback($conexion);
        return false;
      }
      
      $stmt_cabecera->bind_param("si", $numeracion_nueva, $idboleta);
      
      if (!$stmt_cabecera->execute()) {
        error_log("Error ejecutando INSERT boleta en duplicar(): " . $stmt_cabecera->error);
        $stmt_cabecera->close();
        mysqli_rollback($conexion);
        return false;
      }
      
      $idBoletaNew = $conexion->insert_id;
      $stmt_cabecera->close();
      
      // ============= PASO 4: ACTUALIZAR NUMERACIÓN =============
      $sql_update_num = "UPDATE numeracion SET numero = ? WHERE serie = ?";
      
      $stmt_update_num = $conexion->prepare($sql_update_num);
      if (!$stmt_update_num) {
        error_log("Error preparando UPDATE numeracion en duplicar(): " . $conexion->error);
        mysqli_rollback($conexion);
        return false;
      }
      
      $stmt_update_num->bind_param("is", $nnumero, $serie);
      
      if (!$stmt_update_num->execute()) {
        error_log("Error ejecutando UPDATE numeracion en duplicar(): " . $stmt_update_num->error);
        $stmt_update_num->close();
        mysqli_rollback($conexion);
        return false;
      }
      $stmt_update_num->close();
      
      // ============= PASO 5: COPIAR DETALLES DE LA BOLETA =============
      $sql_select_detalle = "SELECT
        idarticulo, numero_orden_item_29, cantidad_item_12, codigo_precio_14_1,
        precio_uni_item_14_2, afectacion_igv_item_monto_27_1, afectacion_igv_item_monto_27_2,
        afectacion_igv_3, afectacion_igv_4, afectacion_igv_5, afectacion_igv_6,
        igv_item, valor_uni_item_31, valor_venta_item_32, dcto_item, descdet, umedida
      FROM boleta b
      INNER JOIN detalle_boleta_producto db ON b.idboleta = db.idboleta
      WHERE b.idboleta = ?";
      
      $stmt_select_det = $conexion->prepare($sql_select_detalle);
      if (!$stmt_select_det) {
        error_log("Error preparando SELECT detalles en duplicar(): " . $conexion->error);
        mysqli_rollback($conexion);
        return false;
      }
      
      $stmt_select_det->bind_param("i", $idboleta);
      
      if (!$stmt_select_det->execute()) {
        error_log("Error ejecutando SELECT detalles en duplicar(): " . $stmt_select_det->error);
        $stmt_select_det->close();
        mysqli_rollback($conexion);
        return false;
      }
      
      $resultdb = $stmt_select_det->get_result();
      $stmt_select_det->close();
      
      // Preparar statement para INSERT de detalles
      $sql_insert_detalle = "INSERT INTO detalle_boleta_producto (
        idboleta, idarticulo, numero_orden_item_29, cantidad_item_12, codigo_precio_14_1,
        precio_uni_item_14_2, afectacion_igv_item_monto_27_1, afectacion_igv_item_monto_27_2,
        afectacion_igv_3, afectacion_igv_4, afectacion_igv_5, afectacion_igv_6,
        igv_item, valor_uni_item_31, valor_venta_item_32, dcto_item, descdet, umedida
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
      
      $stmt_insert_det = $conexion->prepare($sql_insert_detalle);
      if (!$stmt_insert_det) {
        error_log("Error preparando INSERT detalles en duplicar(): " . $conexion->error);
        mysqli_rollback($conexion);
        return false;
      }
      
      // Procesar cada detalle directamente en while (SIN for loop)
      while ($row = $resultdb->fetch_assoc()) {
        $idarticulo = $row["idarticulo"];
        $numero_orden = $row["numero_orden_item_29"];
        $cantidad = $row["cantidad_item_12"];
        $codigo_precio = $row["codigo_precio_14_1"];
        $precio_uni = $row["precio_uni_item_14_2"];
        $afectacion_1 = $row["afectacion_igv_item_monto_27_1"];
        $afectacion_2 = $row["afectacion_igv_item_monto_27_2"];
        $afectacion_3 = $row["afectacion_igv_3"];
        $afectacion_4 = $row["afectacion_igv_4"];
        $afectacion_5 = $row["afectacion_igv_5"];
        $afectacion_6 = $row["afectacion_igv_6"];
        $igv_item = $row["igv_item"];
        $valor_uni = $row["valor_uni_item_31"];
        $valor_venta = $row["valor_venta_item_32"];
        $dcto = $row["dcto_item"];
        $descdet = $row["descdet"];
        $umedida = $row["umedida"];
        
        $stmt_insert_det->bind_param(
          "iisssddsssssdddss",
          $idBoletaNew, $idarticulo, $numero_orden, $cantidad, $codigo_precio,
          $precio_uni, $afectacion_1, $afectacion_2, $afectacion_3, $afectacion_4,
          $afectacion_5, $afectacion_6, $igv_item, $valor_uni, $valor_venta,
          $dcto, $descdet, $umedida
        );
        
        if (!$stmt_insert_det->execute()) {
          error_log("Error ejecutando INSERT detalle en duplicar(): " . $stmt_insert_det->error);
          $stmt_insert_det->close();
          mysqli_rollback($conexion);
          return false;
        }
      }
      
      $stmt_insert_det->close();

      // Commit de transacción
      mysqli_commit($conexion);
      return $idBoletaNew;

    } catch (Exception $e) {
      error_log("Error en duplicar(): " . $e->getMessage());
      mysqli_rollback($conexion);
      return false;
    }
  }

} // Cierre de la clase Boleta

?>