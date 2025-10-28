<?php
// SEGURIDAD: Usar Conexion.php que ya maneja sesiones de forma segura
require_once "../config/Conexion.php";
ob_start();

if (!isset($_SESSION["nombre"])) {
  header("Location: ../vistas/login.php");
  exit();
} else {
  require 'header.php';

  // Verificar permiso de acceso a Compras
  if ($_SESSION['Compras'] == 1) {
?>

<!-- Contenido Principal -->
<div class="content-start transition">
  <div class="container-fluid dashboard">

    <!-- Header con título -->
    <div class="content-header mb-4">
      <div class="row align-items-center">
        <div class="col-md-8">
          <h1 class="mb-0"><i class="fa fa-ship"></i> Importaciones - DUA e Invoice</h1>
          <p class="text-muted">Gestión de importaciones de mercancía con Declaración Única de Aduanas</p>
        </div>
        <div class="col-md-4 text-end">
          <button class="btn btn-primary" id="btnNuevaImportacion">
            <i class="fa fa-plus"></i> Nueva Importación
          </button>
        </div>
      </div>
    </div>

    <!-- Tabs principales -->
    <ul class="nav nav-tabs mb-4" id="tabsImportacion" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-listado" data-bs-toggle="tab" data-bs-target="#panelListado" type="button">
          <i class="fa fa-list"></i> Listado de Importaciones
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-nueva" data-bs-toggle="tab" data-bs-target="#panelNueva" type="button" style="display: none;">
          <i class="fa fa-plus-circle"></i> Nueva/Editar Importación
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-estadisticas" data-bs-toggle="tab" data-bs-target="#panelEstadisticas" type="button">
          <i class="fa fa-bar-chart"></i> Estadísticas
        </button>
      </li>
    </ul>

    <div class="tab-content" id="tabsImportacionContent">

      <!-- ===================================================================== -->
      <!-- TAB: LISTADO DE IMPORTACIONES -->
      <!-- ===================================================================== -->
      <div class="tab-pane fade show active" id="panelListado">

        <!-- Card con filtros -->
        <div class="card shadow-sm mb-4">
          <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fa fa-filter"></i> Filtros de Búsqueda</h5>
          </div>
          <div class="card-body">
            <form id="formFiltros">
              <div class="row">
                <div class="col-md-3">
                  <label class="form-label">Fecha Inicio</label>
                  <input type="date" class="form-control" id="filtroFechaInicio">
                </div>
                <div class="col-md-3">
                  <label class="form-label">Fecha Fin</label>
                  <input type="date" class="form-control" id="filtroFechaFin">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Proveedor</label>
                  <input type="text" class="form-control" id="filtroProveedor" placeholder="Nombre del proveedor">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                  <button type="submit" class="btn btn-primary w-100">
                    <i class="fa fa-search"></i> Buscar
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Tabla de importaciones -->
        <div class="card shadow-sm">
          <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fa fa-table"></i> Importaciones Registradas</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="tblImportaciones" class="table table-striped table-hover" style="width: 100%;">
                <thead class="table-dark">
                  <tr>
                    <th>Fecha DUA</th>
                    <th>Nº DUA</th>
                    <th>Nº Invoice</th>
                    <th>Proveedor</th>
                    <th>País</th>
                    <th>Valor FOB</th>
                    <th>Valor CIF</th>
                    <th>Costo Total S/</th>
                    <th>Items</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>

      </div>

      <!-- ===================================================================== -->
      <!-- TAB: NUEVA/EDITAR IMPORTACIÓN -->
      <!-- ===================================================================== -->
      <div class="tab-pane fade" id="panelNueva">

        <form id="formImportacion">
          <input type="hidden" name="idimportacion" id="idimportacion">

          <!-- Card: Datos del DUA -->
          <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
              <h5 class="mb-0"><i class="fa fa-file-text"></i> Declaración Única de Aduanas (DUA)</h5>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-3">
                  <label class="form-label">Número DUA *</label>
                  <input type="text" class="form-control" name="numero_dua" id="numero_dua" required>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Fecha DUA *</label>
                  <input type="date" class="form-control" name="fecha_dua" id="fecha_dua" required>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Fecha Llegada</label>
                  <input type="date" class="form-control" name="fecha_llegada" id="fecha_llegada">
                </div>
                <div class="col-md-3">
                  <label class="form-label">Aduana</label>
                  <select class="form-select" name="aduana" id="aduana">
                    <option value="Callao">Callao</option>
                    <option value="Jorge Chávez">Jorge Chávez</option>
                    <option value="Paita">Paita</option>
                    <option value="Ilo">Ilo</option>
                    <option value="Tacna">Tacna</option>
                    <option value="Puno">Puno</option>
                    <option value="Otro">Otro</option>
                  </select>
                </div>
              </div>

              <div class="row mt-3">
                <div class="col-md-6">
                  <label class="form-label">Agente Aduanero</label>
                  <input type="text" class="form-control" name="agente_aduanero" id="agente_aduanero">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Régimen Aduanero</label>
                  <select class="form-select" name="regimen_aduanero" id="regimen_aduanero">
                    <option value="10">10 - Importación Definitiva</option>
                    <option value="40">40 - Admisión Temporal</option>
                    <option value="50">50 - Depósito de Aduanas</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <!-- Card: Datos del Invoice -->
          <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
              <h5 class="mb-0"><i class="fa fa-file-text-o"></i> Factura del Proveedor Extranjero (Invoice)</h5>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-3">
                  <label class="form-label">Número Invoice *</label>
                  <input type="text" class="form-control" name="numero_invoice" id="numero_invoice" required>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Fecha Invoice *</label>
                  <input type="date" class="form-control" name="fecha_invoice" id="fecha_invoice" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Proveedor Extranjero *</label>
                  <input type="text" class="form-control" name="proveedor_extranjero" id="proveedor_extranjero" required>
                </div>
                <div class="col-md-2">
                  <label class="form-label">País Origen</label>
                  <select class="form-select" name="pais_origen" id="pais_origen">
                    <option value="CN">China (CN)</option>
                    <option value="US">Estados Unidos (US)</option>
                    <option value="DE">Alemania (DE)</option>
                    <option value="JP">Japón (JP)</option>
                    <option value="KR">Corea del Sur (KR)</option>
                    <option value="BR">Brasil (BR)</option>
                    <option value="MX">México (MX)</option>
                    <option value="IT">Italia (IT)</option>
                    <option value="ES">España (ES)</option>
                    <option value="IN">India (IN)</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <!-- Card: Valores FOB, Flete, Seguro -->
          <div class="card shadow-sm mb-4">
            <div class="card-header bg-info text-white">
              <h5 class="mb-0"><i class="fa fa-dollar"></i> Valores y Costos de Importación</h5>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-2">
                  <label class="form-label">Incoterm</label>
                  <select class="form-select" name="incoterm" id="incoterm">
                    <option value="FOB">FOB</option>
                    <option value="CIF">CIF</option>
                    <option value="EXW">EXW</option>
                    <option value="DDP">DDP</option>
                    <option value="CFR">CFR</option>
                  </select>
                </div>
                <div class="col-md-2">
                  <label class="form-label">Moneda Invoice</label>
                  <select class="form-select" name="moneda_invoice" id="moneda_invoice">
                    <option value="USD">USD - Dólar</option>
                    <option value="EUR">EUR - Euro</option>
                    <option value="CNY">CNY - Yuan</option>
                  </select>
                </div>
                <div class="col-md-2">
                  <label class="form-label">Valor FOB *</label>
                  <input type="number" step="0.01" class="form-control" name="valor_fob" id="valor_fob" value="0.00" required>
                </div>
                <div class="col-md-2">
                  <label class="form-label">Flete</label>
                  <input type="number" step="0.01" class="form-control" name="valor_flete" id="valor_flete" value="0.00">
                </div>
                <div class="col-md-2">
                  <label class="form-label">Seguro</label>
                  <input type="number" step="0.01" class="form-control" name="valor_seguro" id="valor_seguro" value="0.00">
                </div>
                <div class="col-md-2">
                  <label class="form-label">Valor CIF</label>
                  <input type="number" step="0.01" class="form-control" id="valor_cif_calculado" value="0.00" readonly>
                </div>
              </div>

              <div class="row mt-3">
                <div class="col-md-6">
                  <label class="form-label">Tipo de Cambio *</label>
                  <input type="number" step="0.0001" class="form-control" name="tipo_cambio" id="tipo_cambio" value="3.7500" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Moneda Local</label>
                  <select class="form-select" name="moneda_local" id="moneda_local">
                    <option value="PEN">PEN - Soles</option>
                    <option value="USD">USD - Dólares</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <!-- Card: Tributos Aduaneros -->
          <div class="card shadow-sm mb-4">
            <div class="card-header bg-warning text-dark">
              <h5 class="mb-0"><i class="fa fa-money"></i> Derechos y Tributos Aduaneros</h5>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-3">
                  <label class="form-label">Derechos Aduaneros (Ad Valorem)</label>
                  <input type="number" step="0.01" class="form-control" name="derechos_aduaneros" id="derechos_aduaneros" value="0.00">
                </div>
                <div class="col-md-3">
                  <label class="form-label">IGV Importación</label>
                  <input type="number" step="0.01" class="form-control" name="igv_importacion" id="igv_importacion" value="0.00">
                </div>
                <div class="col-md-2">
                  <label class="form-label">IPM</label>
                  <input type="number" step="0.01" class="form-control" name="ipm" id="ipm" value="0.00">
                </div>
                <div class="col-md-2">
                  <label class="form-label">Percepción IGV</label>
                  <input type="number" step="0.01" class="form-control" name="percepcion_igv" id="percepcion_igv" value="0.00">
                </div>
                <div class="col-md-2">
                  <label class="form-label">Otros Tributos</label>
                  <input type="number" step="0.01" class="form-control" name="otros_tributos" id="otros_tributos" value="0.00">
                </div>
              </div>
            </div>
          </div>

          <!-- Card: Gastos Adicionales -->
          <div class="card shadow-sm mb-4">
            <div class="card-header bg-secondary text-white">
              <h5 class="mb-0"><i class="fa fa-truck"></i> Gastos Adicionales (en Soles)</h5>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-3">
                  <label class="form-label">Gastos de Despacho</label>
                  <input type="number" step="0.01" class="form-control" name="gastos_despacho" id="gastos_despacho" value="0.00">
                </div>
                <div class="col-md-3">
                  <label class="form-label">Transporte Local</label>
                  <input type="number" step="0.01" class="form-control" name="gastos_transporte_local" id="gastos_transporte_local" value="0.00">
                </div>
                <div class="col-md-3">
                  <label class="form-label">Almacenaje</label>
                  <input type="number" step="0.01" class="form-control" name="gastos_almacenaje" id="gastos_almacenaje" value="0.00">
                </div>
                <div class="col-md-3">
                  <label class="form-label">Otros Gastos</label>
                  <input type="number" step="0.01" class="form-control" name="otros_gastos" id="otros_gastos" value="0.00">
                </div>
              </div>
            </div>
          </div>

          <!-- Card: Detalle de Productos -->
          <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
              <h5 class="mb-0"><i class="fa fa-cube"></i> Detalle de Productos Importados</h5>
              <button type="button" class="btn btn-sm btn-light" id="btnAgregarProducto">
                <i class="fa fa-plus"></i> Agregar Producto
              </button>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table id="tblDetalleProductos" class="table table-sm table-bordered">
                  <thead class="table-light">
                    <tr>
                      <th>Descripción</th>
                      <th>Partida Arancelaria</th>
                      <th>Cantidad</th>
                      <th>UM</th>
                      <th>Precio Unit. FOB</th>
                      <th>Total FOB</th>
                      <th>Costo Unit. Final S/</th>
                      <th>Acciones</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Card: Observaciones -->
          <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
              <h5 class="mb-0"><i class="fa fa-sticky-note"></i> Observaciones</h5>
            </div>
            <div class="card-body">
              <textarea class="form-control" name="observaciones" id="observaciones" rows="3"></textarea>
            </div>
          </div>

          <!-- Botones de acción -->
          <div class="card shadow-sm">
            <div class="card-body">
              <div class="row">
                <div class="col-md-12 text-end">
                  <button type="button" class="btn btn-secondary" id="btnCancelar">
                    <i class="fa fa-times"></i> Cancelar
                  </button>
                  <button type="button" class="btn btn-info" id="btnDistribuirCostos">
                    <i class="fa fa-calculator"></i> Distribuir Costos
                  </button>
                  <button type="submit" class="btn btn-success">
                    <i class="fa fa-save"></i> Guardar Importación
                  </button>
                </div>
              </div>
            </div>
          </div>

        </form>

      </div>

      <!-- ===================================================================== -->
      <!-- TAB: ESTADÍSTICAS -->
      <!-- ===================================================================== -->
      <div class="tab-pane fade" id="panelEstadisticas">

        <!-- Filtros de período -->
        <div class="card shadow-sm mb-4">
          <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fa fa-calendar"></i> Período de Análisis</h5>
          </div>
          <div class="card-body">
            <form id="formEstadisticas">
              <div class="row">
                <div class="col-md-4">
                  <label class="form-label">Fecha Inicio</label>
                  <input type="date" class="form-control" id="estadFechaInicio">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Fecha Fin</label>
                  <input type="date" class="form-control" id="estadFechaFin">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                  <button type="submit" class="btn btn-primary w-100">
                    <i class="fa fa-line-chart"></i> Generar Estadísticas
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Cards de resumen -->
        <div id="estadResumen" class="row mb-4" style="display: none;">
          <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-start border-primary border-4 shadow h-100">
              <div class="card-body">
                <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total Importaciones</div>
                <div class="h4 mb-0 fw-bold" id="estadTotalImportaciones">0</div>
              </div>
            </div>
          </div>
          <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-start border-success border-4 shadow h-100">
              <div class="card-body">
                <div class="text-xs fw-bold text-success text-uppercase mb-1">Total FOB</div>
                <div class="h4 mb-0 fw-bold" id="estadTotalFOB">USD 0.00</div>
              </div>
            </div>
          </div>
          <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-start border-info border-4 shadow h-100">
              <div class="card-body">
                <div class="text-xs fw-bold text-info text-uppercase mb-1">Total CIF</div>
                <div class="h4 mb-0 fw-bold" id="estadTotalCIF">USD 0.00</div>
              </div>
            </div>
          </div>
          <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-start border-warning border-4 shadow h-100">
              <div class="card-body">
                <div class="text-xs fw-bold text-warning text-uppercase mb-1">Costo Total S/</div>
                <div class="h4 mb-0 fw-bold" id="estadCostoTotalSoles">S/ 0.00</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Top Proveedores -->
        <div class="card shadow-sm mb-4">
          <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fa fa-trophy"></i> Top 10 Proveedores Extranjeros</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="tblTopProveedores" class="table table-striped">
                <thead class="table-dark">
                  <tr>
                    <th>#</th>
                    <th>Proveedor</th>
                    <th>País</th>
                    <th>Total Importaciones</th>
                    <th>Total FOB</th>
                    <th>Total Costo S/</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Productos más importados -->
        <div class="card shadow-sm">
          <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fa fa-cubes"></i> Top 20 Productos Más Importados</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="tblProductosMasImportados" class="table table-striped">
                <thead class="table-dark">
                  <tr>
                    <th>#</th>
                    <th>Partida</th>
                    <th>Descripción</th>
                    <th>Veces Importado</th>
                    <th>Cantidad Total</th>
                    <th>Precio Prom. FOB</th>
                    <th>Costo Acumulado S/</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>

      </div>

    </div>

  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: AGREGAR PRODUCTO -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalAgregarProducto" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">
          <i class="fa fa-cube"></i> Agregar Producto a la Importación
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formAgregarProducto">

          <div class="row mb-3">
            <div class="col-md-12">
              <label class="form-label">Descripción del Producto *</label>
              <input type="text" class="form-control" id="producto_descripcion" required>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">Marca</label>
              <input type="text" class="form-control" id="producto_marca">
            </div>
            <div class="col-md-4">
              <label class="form-label">Modelo</label>
              <input type="text" class="form-control" id="producto_modelo">
            </div>
            <div class="col-md-4">
              <label class="form-label">Partida Arancelaria *</label>
              <input type="text" class="form-control" id="producto_partida" maxlength="12" required>
              <small class="text-muted">10 dígitos</small>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-3">
              <label class="form-label">Cantidad *</label>
              <input type="number" step="0.01" class="form-control" id="producto_cantidad" value="1" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Unidad Medida</label>
              <select class="form-select" id="producto_unidad">
                <option value="UND">Unidad</option>
                <option value="KG">Kilogramo</option>
                <option value="M">Metro</option>
                <option value="L">Litro</option>
                <option value="CJA">Caja</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Peso Bruto (kg)</label>
              <input type="number" step="0.001" class="form-control" id="producto_peso_bruto" value="0">
            </div>
            <div class="col-md-3">
              <label class="form-label">Peso Neto (kg)</label>
              <input type="number" step="0.001" class="form-control" id="producto_peso_neto" value="0">
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-12">
              <label class="form-label">Precio Unitario FOB *</label>
              <input type="number" step="0.0001" class="form-control" id="producto_precio_fob" required>
            </div>
          </div>

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btnGuardarProducto">
          <i class="fa fa-save"></i> Agregar Producto
        </button>
      </div>
    </div>
  </div>
</div>

<?php
  } else {
    require 'noacceso.php';
  }

  require 'footer.php';
?>
<script type="text/javascript" src="scripts/importacion.js"></script>
<?php
}
ob_end_flush();
?>
