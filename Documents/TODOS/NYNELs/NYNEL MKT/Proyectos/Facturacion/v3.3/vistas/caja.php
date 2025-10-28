<?php
// SEGURIDAD: Usar Conexion.php que ya maneja sesiones de forma segura
require_once "../config/Conexion.php";
//Activamos el almacenamiento del Buffer
ob_start();

if (!isset($_SESSION["nombre"])) {
  header("Location: ../vistas/login.php");
} else {
  require 'header.php';

  if ($_SESSION['almacen'] == 1) {
    $idusuario = $_SESSION["idusuario"];
    $nombreusuario = $_SESSION["nombre"];
?>

<!-- Contenido Principal -->
<div class="content-start transition">
  <div class="container-fluid dashboard">

    <!-- Header con resumen -->
    <div class="content-header">
      <div class="row align-items-center">
        <div class="col-md-8">
          <h1><i class="fa fa-cash-register"></i> Gestión de Caja</h1>
        </div>
        <div class="col-md-4 text-end">
          <button id="btnAperturarCaja" class="btn btn-success btn-lg">
            <i class="fa fa-lock-open"></i> Aperturar Caja
          </button>
        </div>
      </div>
    </div>

    <!-- Tarjetas de Resumen (solo visible si hay caja abierta) -->
    <div id="resumenCaja" class="row mb-4" style="display: none;">
      <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-start border-success border-4 shadow h-100">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col">
                <div class="text-xs fw-bold text-success text-uppercase mb-1">Monto Inicial</div>
                <div class="h5 mb-0 fw-bold text-gray-800" id="cajaMontoInicial">S/ 0.00</div>
              </div>
              <div class="col-auto">
                <i class="fa fa-coins fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-start border-primary border-4 shadow h-100">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col">
                <div class="text-xs fw-bold text-primary text-uppercase mb-1">Ingresos</div>
                <div class="h5 mb-0 fw-bold text-gray-800" id="cajaTotalIngresos">S/ 0.00</div>
              </div>
              <div class="col-auto">
                <i class="fa fa-arrow-up fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-start border-danger border-4 shadow h-100">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col">
                <div class="text-xs fw-bold text-danger text-uppercase mb-1">Egresos</div>
                <div class="h5 mb-0 fw-bold text-gray-800" id="cajaTotalEgresos">S/ 0.00</div>
              </div>
              <div class="col-auto">
                <i class="fa fa-arrow-down fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-start border-info border-4 shadow h-100">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col">
                <div class="text-xs fw-bold text-info text-uppercase mb-1">Saldo Actual</div>
                <div class="h5 mb-0 fw-bold text-gray-800" id="cajaSaldoActual">S/ 0.00</div>
              </div>
              <div class="col-auto">
                <i class="fa fa-wallet fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Panel de información de caja abierta -->
    <div id="panelCajaAbierta" class="row mb-4" style="display: none;">
      <div class="col-md-12">
        <div class="card shadow">
          <div class="card-header bg-gradient-primary text-white">
            <div class="row align-items-center">
              <div class="col-md-8">
                <h5 class="mb-0"><i class="fa fa-info-circle"></i> Caja Abierta</h5>
              </div>
              <div class="col-md-4 text-end">
                <button id="btnRegistrarMovimiento" class="btn btn-light btn-sm me-2">
                  <i class="fa fa-plus"></i> Movimiento
                </button>
                <button id="btnCerrarCaja" class="btn btn-warning btn-sm">
                  <i class="fa fa-lock"></i> Cerrar Caja
                </button>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-3">
                <strong>Usuario:</strong>
                <p id="cajaUsuario">-</p>
              </div>
              <div class="col-md-3">
                <strong>Turno:</strong>
                <p id="cajaTurno">-</p>
              </div>
              <div class="col-md-3">
                <strong>Fecha Apertura:</strong>
                <p id="cajaFechaApertura">-</p>
              </div>
              <div class="col-md-3">
                <strong>ID Caja:</strong>
                <p id="cajaIdCaja">-</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabla de Movimientos de Caja Actual -->
    <div id="tablaMovimientos" class="row mb-4" style="display: none;">
      <div class="col-md-12">
        <div class="card shadow">
          <div class="card-header bg-gradient-secondary">
            <h5 class="mb-0"><i class="fa fa-list"></i> Movimientos de Caja</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="tblMovimientos" class="table table-bordered table-striped table-hover" style="width: 100%;">
                <thead class="table-dark">
                  <tr>
                    <th>Fecha/Hora</th>
                    <th>Tipo</th>
                    <th>Concepto</th>
                    <th>Monto</th>
                    <th>Tipo Pago</th>
                    <th>Referencia</th>
                    <th>Usuario</th>
                  </tr>
                </thead>
                <tbody>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Historial de Cajas -->
    <div class="row">
      <div class="col-md-12">
        <div class="card shadow">
          <div class="card-header bg-gradient-dark text-white">
            <h5 class="mb-0"><i class="fa fa-history"></i> Historial de Cajas</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="tblHistorialCajas" class="table table-bordered table-striped table-hover" style="width: 100%;">
                <thead class="table-dark">
                  <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Turno</th>
                    <th>Usuario</th>
                    <th>Monto Inicial</th>
                    <th>Sistema</th>
                    <th>Final</th>
                    <th>Diferencia</th>
                    <th>Estado</th>
                    <th>Opciones</th>
                  </tr>
                </thead>
                <tbody>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div><!-- End Container -->
</div><!-- End Content -->

<!-- MODAL: Aperturar Caja -->
<div class="modal fade" id="modalAperturarCaja" tabindex="-1" aria-labelledby="modalAperturarCajaLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="modalAperturarCajaLabel">
          <i class="fa fa-lock-open"></i> Aperturar Caja
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="formAperturarCaja">
        <div class="modal-body">
          <div class="mb-3">
            <label for="turnoApertura" class="form-label">Turno <span class="text-danger">*</span></label>
            <select class="form-select" id="turnoApertura" name="turno" required>
              <option value="COMPLETO">Completo (Todo el día)</option>
              <option value="MAÑANA">Mañana</option>
              <option value="TARDE">Tarde</option>
              <option value="NOCHE">Noche</option>
            </select>
          </div>

          <div class="mb-3">
            <label for="montoInicialApertura" class="form-label">Monto Inicial <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text">S/</span>
              <input type="number" class="form-control" id="montoInicialApertura" name="monto_inicial"
                     step="0.01" min="0" value="0.00" required>
            </div>
            <small class="form-text text-muted">Ingrese el monto con el que inicia la caja</small>
          </div>

          <div class="alert alert-info">
            <i class="fa fa-info-circle"></i> <strong>Importante:</strong> Solo puede tener una caja abierta a la vez.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fa fa-times"></i> Cancelar
          </button>
          <button type="submit" class="btn btn-success">
            <i class="fa fa-lock-open"></i> Aperturar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL: Registrar Movimiento -->
<div class="modal fade" id="modalRegistrarMovimiento" tabindex="-1" aria-labelledby="modalRegistrarMovimientoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalRegistrarMovimientoLabel">
          <i class="fa fa-exchange-alt"></i> Registrar Movimiento
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="formRegistrarMovimiento">
        <input type="hidden" id="idcajaMovimiento" name="idcaja">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="tipoMovimiento" class="form-label">Tipo de Movimiento <span class="text-danger">*</span></label>
              <select class="form-select" id="tipoMovimiento" name="tipo_movimiento" required>
                <option value="">Seleccione...</option>
                <option value="INGRESO">Ingreso</option>
                <option value="EGRESO">Egreso</option>
                <option value="AJUSTE">Ajuste</option>
              </select>
            </div>

            <div class="col-md-6 mb-3">
              <label for="tipoPagoMovimiento" class="form-label">Tipo de Pago <span class="text-danger">*</span></label>
              <select class="form-select" id="tipoPagoMovimiento" name="tipo_pago" required>
                <option value="EFECTIVO">Efectivo</option>
                <option value="TARJETA">Tarjeta</option>
                <option value="TRANSFERENCIA">Transferencia</option>
                <option value="YAPE">Yape</option>
                <option value="PLIN">Plin</option>
                <option value="OTRO">Otro</option>
              </select>
            </div>
          </div>

          <div class="mb-3">
            <label for="conceptoMovimiento" class="form-label">Concepto <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="conceptoMovimiento" name="concepto"
                   placeholder="Describa el motivo del movimiento" required>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="montoMovimiento" class="form-label">Monto <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">S/</span>
                <input type="number" class="form-control" id="montoMovimiento" name="monto"
                       step="0.01" min="0.01" required>
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label for="referenciaMovimiento" class="form-label">Referencia/Documento</label>
              <input type="text" class="form-control" id="referenciaMovimiento" name="referencia"
                     placeholder="Número de operación, ticket, etc.">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fa fa-times"></i> Cancelar
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-save"></i> Registrar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL: Cerrar Caja -->
<div class="modal fade" id="modalCerrarCaja" tabindex="-1" aria-labelledby="modalCerrarCajaLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title" id="modalCerrarCajaLabel">
          <i class="fa fa-lock"></i> Cerrar Caja
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="formCerrarCaja">
        <input type="hidden" id="idcajaCierre" name="idcaja">
        <div class="modal-body">

          <!-- Resumen de Sistema -->
          <div class="row mb-4">
            <div class="col-md-12">
              <h6 class="border-bottom pb-2">Resumen del Sistema</h6>
            </div>
            <div class="col-md-3">
              <div class="card bg-light">
                <div class="card-body text-center">
                  <small class="text-muted">Monto Inicial</small>
                  <h5 id="cierreMontoInicial">S/ 0.00</h5>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card bg-light">
                <div class="card-body text-center">
                  <small class="text-muted">Total Ingresos</small>
                  <h5 class="text-success" id="cierreTotalIngresos">S/ 0.00</h5>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card bg-light">
                <div class="card-body text-center">
                  <small class="text-muted">Total Egresos</small>
                  <h5 class="text-danger" id="cierreTotalEgresos">S/ 0.00</h5>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card bg-primary text-white">
                <div class="card-body text-center">
                  <small>Saldo Sistema</small>
                  <h5 id="cierreSaldoSistema">S/ 0.00</h5>
                </div>
              </div>
            </div>
          </div>

          <!-- Arqueo de Caja (Conteo Físico) -->
          <div class="row mb-4">
            <div class="col-md-12">
              <h6 class="border-bottom pb-2">Arqueo de Caja - Conteo Físico</h6>
            </div>

            <!-- Billetes -->
            <div class="col-md-6">
              <h6 class="text-muted mb-3">Billetes</h6>
              <table class="table table-sm table-bordered">
                <thead>
                  <tr>
                    <th>Denominación</th>
                    <th style="width: 100px;">Cantidad</th>
                    <th style="width: 120px;">Subtotal</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>S/ 200.00</td>
                    <td><input type="number" class="form-control form-control-sm denominacion" id="billetes_200" name="billetes_200" value="0" min="0" data-valor="200"></td>
                    <td class="text-end subtotal">S/ 0.00</td>
                  </tr>
                  <tr>
                    <td>S/ 100.00</td>
                    <td><input type="number" class="form-control form-control-sm denominacion" id="billetes_100" name="billetes_100" value="0" min="0" data-valor="100"></td>
                    <td class="text-end subtotal">S/ 0.00</td>
                  </tr>
                  <tr>
                    <td>S/ 50.00</td>
                    <td><input type="number" class="form-control form-control-sm denominacion" id="billetes_50" name="billetes_50" value="0" min="0" data-valor="50"></td>
                    <td class="text-end subtotal">S/ 0.00</td>
                  </tr>
                  <tr>
                    <td>S/ 20.00</td>
                    <td><input type="number" class="form-control form-control-sm denominacion" id="billetes_20" name="billetes_20" value="0" min="0" data-valor="20"></td>
                    <td class="text-end subtotal">S/ 0.00</td>
                  </tr>
                  <tr>
                    <td>S/ 10.00</td>
                    <td><input type="number" class="form-control form-control-sm denominacion" id="billetes_10" name="billetes_10" value="0" min="0" data-valor="10"></td>
                    <td class="text-end subtotal">S/ 0.00</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Monedas -->
            <div class="col-md-6">
              <h6 class="text-muted mb-3">Monedas</h6>
              <table class="table table-sm table-bordered">
                <thead>
                  <tr>
                    <th>Denominación</th>
                    <th style="width: 100px;">Cantidad</th>
                    <th style="width: 120px;">Subtotal</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>S/ 5.00</td>
                    <td><input type="number" class="form-control form-control-sm denominacion" id="monedas_5" name="monedas_5" value="0" min="0" data-valor="5"></td>
                    <td class="text-end subtotal">S/ 0.00</td>
                  </tr>
                  <tr>
                    <td>S/ 2.00</td>
                    <td><input type="number" class="form-control form-control-sm denominacion" id="monedas_2" name="monedas_2" value="0" min="0" data-valor="2"></td>
                    <td class="text-end subtotal">S/ 0.00</td>
                  </tr>
                  <tr>
                    <td>S/ 1.00</td>
                    <td><input type="number" class="form-control form-control-sm denominacion" id="monedas_1" name="monedas_1" value="0" min="0" data-valor="1"></td>
                    <td class="text-end subtotal">S/ 0.00</td>
                  </tr>
                  <tr>
                    <td>S/ 0.50</td>
                    <td><input type="number" class="form-control form-control-sm denominacion" id="monedas_050" name="monedas_050" value="0" min="0" data-valor="0.50"></td>
                    <td class="text-end subtotal">S/ 0.00</td>
                  </tr>
                  <tr>
                    <td>S/ 0.20</td>
                    <td><input type="number" class="form-control form-control-sm denominacion" id="monedas_020" name="monedas_020" value="0" min="0" data-valor="0.20"></td>
                    <td class="text-end subtotal">S/ 0.00</td>
                  </tr>
                  <tr>
                    <td>S/ 0.10</td>
                    <td><input type="number" class="form-control form-control-sm denominacion" id="monedas_010" name="monedas_010" value="0" min="0" data-valor="0.10"></td>
                    <td class="text-end subtotal">S/ 0.00</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Total Efectivo -->
          <div class="row mb-3">
            <div class="col-md-12">
              <div class="card bg-success text-white">
                <div class="card-body text-center">
                  <h6>Total Efectivo Contado</h6>
                  <h3 id="totalEfectivo">S/ 0.00</h3>
                </div>
              </div>
            </div>
          </div>

          <!-- Otros Medios de Pago -->
          <div class="row mb-4">
            <div class="col-md-12">
              <h6 class="border-bottom pb-2">Otros Medios de Pago</h6>
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label">Total Tarjetas</label>
              <div class="input-group input-group-sm">
                <span class="input-group-text">S/</span>
                <input type="number" class="form-control otros-pagos" id="total_tarjetas" name="total_tarjetas" value="0" step="0.01" min="0">
              </div>
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label">Total Transferencias</label>
              <div class="input-group input-group-sm">
                <span class="input-group-text">S/</span>
                <input type="number" class="form-control otros-pagos" id="total_transferencias" name="total_transferencias" value="0" step="0.01" min="0">
              </div>
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label">Total Yape</label>
              <div class="input-group input-group-sm">
                <span class="input-group-text">S/</span>
                <input type="number" class="form-control otros-pagos" id="total_yape" name="total_yape" value="0" step="0.01" min="0">
              </div>
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label">Total Plin</label>
              <div class="input-group input-group-sm">
                <span class="input-group-text">S/</span>
                <input type="number" class="form-control otros-pagos" id="total_plin" name="total_plin" value="0" step="0.01" min="0">
              </div>
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label">Otros</label>
              <div class="input-group input-group-sm">
                <span class="input-group-text">S/</span>
                <input type="number" class="form-control otros-pagos" id="total_otros" name="total_otros" value="0" step="0.01" min="0">
              </div>
            </div>
          </div>

          <!-- Total General Declarado -->
          <div class="row mb-4">
            <div class="col-md-12">
              <div class="card bg-info text-white">
                <div class="card-body text-center">
                  <h6>Total General Declarado</h6>
                  <h2 id="totalGeneral">S/ 0.00</h2>
                </div>
              </div>
            </div>
          </div>

          <!-- Diferencia -->
          <div class="row mb-3">
            <div class="col-md-12">
              <div class="card" id="cardDiferencia">
                <div class="card-body text-center">
                  <h6>Diferencia (Declarado - Sistema)</h6>
                  <h3 id="diferenciaCierre">S/ 0.00</h3>
                  <small class="text-muted" id="textoDiferencia"></small>
                </div>
              </div>
            </div>
          </div>

          <!-- Observaciones -->
          <div class="mb-3">
            <label for="observacionesCierre" class="form-label">Observaciones</label>
            <textarea class="form-control" id="observacionesCierre" name="observaciones" rows="3"
                      placeholder="Indique cualquier observación sobre el cierre de caja..."></textarea>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fa fa-times"></i> Cancelar
          </button>
          <button type="submit" class="btn btn-warning">
            <i class="fa fa-lock"></i> Cerrar Caja
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php
  } else {
    require 'noacceso.php';
  }

  require 'footer.php';
?>
<script type="text/javascript" src="scripts/caja.js"></script>
<?php
}
ob_end_flush();
?>
