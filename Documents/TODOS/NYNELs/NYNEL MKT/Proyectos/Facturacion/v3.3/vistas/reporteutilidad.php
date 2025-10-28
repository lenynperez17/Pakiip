<?php
// SEGURIDAD: Usar Conexion.php que ya maneja sesiones de forma segura
require_once "../config/Conexion.php";
ob_start();

if (!isset($_SESSION["nombre"])) {
  header("Location: ../vistas/login.php");
  exit();
} else {
  require 'header.php';

  if ($_SESSION['Ventas'] == 1) {
?>

<!-- Contenido Principal -->
<div class="content-start transition">
  <div class="container-fluid dashboard">

    <!-- Header con título -->
    <div class="content-header mb-4">
      <div class="row align-items-center">
        <div class="col-md-8">
          <h1 class="mb-0"><i class="fa fa-line-chart"></i> Reporte de Utilidad Mejorado</h1>
          <p class="text-muted">Análisis detallado de ingresos, egresos y utilidad por período</p>
        </div>
        <div class="col-md-4 text-end">
          <button class="btn btn-primary" id="btnNuevoAnalisis">
            <i class="fa fa-plus"></i> Nuevo Análisis
          </button>
        </div>
      </div>
    </div>

    <!-- Filtros de Búsqueda -->
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-gradient-primary text-white">
        <h5 class="mb-0"><i class="fa fa-filter"></i> Filtros de Análisis</h5>
      </div>
      <div class="card-body">
        <form id="formFiltros">
          <div class="row">
            <!-- Rango de Fechas -->
            <div class="col-md-3">
              <label class="form-label">Fecha Inicio</label>
              <input type="date" class="form-control" id="fechaInicio" name="fecha_inicio" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Fecha Fin</label>
              <input type="date" class="form-control" id="fechaFin" name="fecha_fin" required>
            </div>

            <!-- Filtro por Categoría -->
            <div class="col-md-3">
              <label class="form-label">Categoría</label>
              <select class="form-select" id="filtroCategoria" name="categoria">
                <option value="">Todas las categorías</option>
              </select>
            </div>

            <!-- Tipo de Reporte -->
            <div class="col-md-3">
              <label class="form-label">Tipo de Vista</label>
              <select class="form-select" id="tipoVista" name="tipo_vista">
                <option value="diario">Por Día</option>
                <option value="semanal">Por Semana</option>
                <option value="mensual">Por Mes</option>
                <option value="resumen">Resumen Total</option>
              </select>
            </div>
          </div>

          <div class="row mt-3">
            <div class="col-md-12 text-end">
              <button type="submit" class="btn btn-success">
                <i class="fa fa-search"></i> Generar Reporte
              </button>
              <button type="button" class="btn btn-secondary" id="btnLimpiarFiltros">
                <i class="fa fa-refresh"></i> Limpiar
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Cards de Resumen -->
    <div id="resumenCards" class="row mb-4" style="display: none;">
      <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-start border-success border-4 shadow h-100">
          <div class="card-body">
            <div class="text-xs fw-bold text-success text-uppercase mb-1">Total Ingresos</div>
            <div class="h4 mb-0 fw-bold text-gray-800" id="cardTotalIngresos">S/ 0.00</div>
            <small class="text-muted" id="cardCantIngresos">0 transacciones</small>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-start border-danger border-4 shadow h-100">
          <div class="card-body">
            <div class="text-xs fw-bold text-danger text-uppercase mb-1">Total Egresos</div>
            <div class="h4 mb-0 fw-bold text-gray-800" id="cardTotalEgresos">S/ 0.00</div>
            <small class="text-muted" id="cardCantEgresos">0 transacciones</small>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-start border-primary border-4 shadow h-100">
          <div class="card-body">
            <div class="text-xs fw-bold text-primary text-uppercase mb-1">Utilidad Neta</div>
            <div class="h4 mb-0 fw-bold" id="cardUtilidad">S/ 0.00</div>
            <small class="text-muted" id="cardDiferencia">---</small>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-start border-warning border-4 shadow h-100">
          <div class="card-body">
            <div class="text-xs fw-bold text-warning text-uppercase mb-1">Margen</div>
            <div class="h4 mb-0 fw-bold text-gray-800" id="cardMargen">0%</div>
            <small class="text-muted">Sobre ingresos totales</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Gráfico de Tendencia -->
    <div id="seccionGrafico" class="card shadow-sm mb-4" style="display: none;">
      <div class="card-header bg-light">
        <h5 class="mb-0"><i class="fa fa-bar-chart"></i> Tendencia de Utilidad</h5>
      </div>
      <div class="card-body">
        <canvas id="chartUtilidad" height="80"></canvas>
      </div>
    </div>

    <!-- Tabla de Detalle por Período -->
    <div id="seccionDetalle" class="card shadow-sm mb-4" style="display: none;">
      <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fa fa-table"></i> Detalle por Período</h5>
        <div>
          <button class="btn btn-sm btn-success" id="btnExportarExcel">
            <i class="fa fa-file-excel-o"></i> Excel
          </button>
          <button class="btn btn-sm btn-danger" id="btnExportarPDF">
            <i class="fa fa-file-pdf-o"></i> PDF
          </button>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table id="tblDetallePeriodo" class="table table-striped table-hover" style="width: 100%;">
            <thead class="table-dark">
              <tr>
                <th>Período</th>
                <th>Ingresos</th>
                <th>Egresos</th>
                <th>Utilidad</th>
                <th>Margen %</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
              <tr class="table-secondary fw-bold">
                <td>TOTAL</td>
                <td id="footerIngresos">S/ 0.00</td>
                <td id="footerEgresos">S/ 0.00</td>
                <td id="footerUtilidad">S/ 0.00</td>
                <td id="footerMargen">0%</td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>

    <!-- Tabla de Detalle por Categoría -->
    <div id="seccionCategorias" class="card shadow-sm mb-4" style="display: none;">
      <div class="card-header bg-light">
        <h5 class="mb-0"><i class="fa fa-tags"></i> Análisis por Categoría</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table id="tblDetalleCategoria" class="table table-striped table-hover" style="width: 100%;">
            <thead class="table-dark">
              <tr>
                <th>Categoría</th>
                <th>Tipo</th>
                <th>Cantidad</th>
                <th>Monto Total</th>
                <th>Promedio</th>
                <th>% del Total</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Historial de Análisis Guardados -->
    <div class="card shadow-sm">
      <div class="card-header bg-light">
        <h5 class="mb-0"><i class="fa fa-history"></i> Historial de Análisis</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table id="tblHistorialAnalisis" class="table table-striped table-hover" style="width: 100%;">
            <thead class="table-dark">
              <tr>
                <th>ID</th>
                <th>Fecha Inicio</th>
                <th>Fecha Fin</th>
                <th>Ingresos</th>
                <th>Egresos</th>
                <th>Utilidad</th>
                <th>Margen %</th>
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
</div>

<!-- Modal: Detalle de Transacciones por Período -->
<div class="modal fade" id="modalDetalleTransacciones" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">
          <i class="fa fa-list"></i> Detalle de Transacciones - <span id="tituloPeriodo"></span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <ul class="nav nav-tabs mb-3" id="tabsDetalle" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-ingresos" data-bs-toggle="tab" data-bs-target="#panelIngresos" type="button">
              <i class="fa fa-arrow-up text-success"></i> Ingresos
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-egresos" data-bs-toggle="tab" data-bs-target="#panelEgresos" type="button">
              <i class="fa fa-arrow-down text-danger"></i> Egresos
            </button>
          </li>
        </ul>

        <div class="tab-content" id="tabsDetalleContent">
          <!-- Tab Ingresos -->
          <div class="tab-pane fade show active" id="panelIngresos">
            <table id="tblIngresosDetalle" class="table table-sm table-striped">
              <thead>
                <tr>
                  <th>Fecha</th>
                  <th>Categoría</th>
                  <th>Descripción</th>
                  <th>Acreedor</th>
                  <th>Monto</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>

          <!-- Tab Egresos -->
          <div class="tab-pane fade" id="panelEgresos">
            <table id="tblEgresosDetalle" class="table table-sm table-striped">
              <thead>
                <tr>
                  <th>Fecha</th>
                  <th>Categoría</th>
                  <th>Descripción</th>
                  <th>Acreedor</th>
                  <th>Monto</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script type="text/javascript" src="scripts/reporteutilidad.js"></script>
<?php
}
ob_end_flush();
?>
