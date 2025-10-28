<?php
// SEGURIDAD: Usar Conexion.php que ya maneja sesiones de forma segura
require_once "../config/Conexion.php";
ob_start();

if (!isset($_SESSION["nombre"])) {
  header("Location: ../vistas/login.php");
  exit();
} else {
  require 'header.php';

  // Verificar permiso de acceso (ajustar según el sistema de permisos)
  if ($_SESSION['Ventas'] == 1 || $_SESSION['Compras'] == 1) {
?>

<!-- Contenido Principal -->
<div class="content-start transition">
  <div class="container-fluid dashboard">

    <!-- Header con título -->
    <div class="content-header mb-4">
      <div class="row align-items-center">
        <div class="col-md-8">
          <h1 class="mb-0"><i class="fa fa-file-text-o"></i> SIRE - Sistema Integrado de Registros Electrónicos</h1>
          <p class="text-muted">Generación de archivos RVIE y RCE para SUNAT</p>
        </div>
        <div class="col-md-4 text-end">
          <button class="btn btn-primary" id="btnConfiguracionSIRE">
            <i class="fa fa-cog"></i> Configuración
          </button>
        </div>
      </div>
    </div>

    <!-- Tabs principales -->
    <ul class="nav nav-tabs mb-4" id="tabsSIRE" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-generar" data-bs-toggle="tab" data-bs-target="#panelGenerar" type="button">
          <i class="fa fa-file-text"></i> Generar Archivos
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-historial" data-bs-toggle="tab" data-bs-target="#panelHistorial" type="button">
          <i class="fa fa-history"></i> Historial de Exportaciones
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-ayuda" data-bs-toggle="tab" data-bs-target="#panelAyuda" type="button">
          <i class="fa fa-question-circle"></i> Ayuda
        </button>
      </li>
    </ul>

    <div class="tab-content" id="tabsSIREContent">

      <!-- ===================================================================== -->
      <!-- TAB: GENERAR ARCHIVOS -->
      <!-- ===================================================================== -->
      <div class="tab-pane fade show active" id="panelGenerar">

        <!-- Card: Generar RVIE -->
        <div class="card shadow-sm mb-4">
          <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fa fa-arrow-up"></i> RVIE - Registro de Ventas e Ingresos Electrónico</h5>
          </div>
          <div class="card-body">
            <form id="formGenerarRVIE">
              <div class="row">
                <div class="col-md-3">
                  <label class="form-label">Año *</label>
                  <select class="form-select" id="rvie_anio" name="periodo_anio" required>
                    <!-- Opciones generadas dinámicamente -->
                  </select>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Mes *</label>
                  <select class="form-select" id="rvie_mes" name="periodo_mes" required>
                    <option value="01">Enero</option>
                    <option value="02">Febrero</option>
                    <option value="03">Marzo</option>
                    <option value="04">Abril</option>
                    <option value="05">Mayo</option>
                    <option value="06">Junio</option>
                    <option value="07">Julio</option>
                    <option value="08">Agosto</option>
                    <option value="09">Septiembre</option>
                    <option value="10">Octubre</option>
                    <option value="11">Noviembre</option>
                    <option value="12">Diciembre</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Oportunidad *</label>
                  <select class="form-select" id="rvie_oportunidad" name="cod_oportunidad" required>
                    <option value="01">01 - A la fecha de vencimiento o que determine la ley</option>
                    <option value="02">02 - Presentación fuera del plazo</option>
                    <option value="03">03 - Modificación por el deudor tributario</option>
                    <option value="04">04 - Modificación por orden SUNAT</option>
                    <option value="05">05 - Modificación por orden judicial</option>
                    <option value="06">06 - Otros casos</option>
                  </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                  <button type="submit" class="btn btn-success w-100">
                    <i class="fa fa-file-text-o"></i> Generar RVIE
                  </button>
                </div>
              </div>

              <div class="row mt-3">
                <div class="col-md-12">
                  <div class="alert alert-info mb-0">
                    <i class="fa fa-info-circle"></i>
                    <strong>Nota:</strong> Este proceso generará el archivo TXT con el formato SUNAT de 35 caracteres.
                    Se incluirán todas las facturas y boletas emitidas en el período seleccionado.
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Card: Generar RCE -->
        <div class="card shadow-sm mb-4">
          <div class="card-header bg-danger text-white">
            <h5 class="mb-0"><i class="fa fa-arrow-down"></i> RCE - Registro de Compras Electrónico</h5>
          </div>
          <div class="card-body">
            <form id="formGenerarRCE">
              <div class="row">
                <div class="col-md-3">
                  <label class="form-label">Año *</label>
                  <select class="form-select" id="rce_anio" name="periodo_anio" required>
                    <!-- Opciones generadas dinámicamente -->
                  </select>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Mes *</label>
                  <select class="form-select" id="rce_mes" name="periodo_mes" required>
                    <option value="01">Enero</option>
                    <option value="02">Febrero</option>
                    <option value="03">Marzo</option>
                    <option value="04">Abril</option>
                    <option value="05">Mayo</option>
                    <option value="06">Junio</option>
                    <option value="07">Julio</option>
                    <option value="08">Agosto</option>
                    <option value="09">Septiembre</option>
                    <option value="10">Octubre</option>
                    <option value="11">Noviembre</option>
                    <option value="12">Diciembre</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Oportunidad *</label>
                  <select class="form-select" id="rce_oportunidad" name="cod_oportunidad" required>
                    <option value="01">01 - A la fecha de vencimiento o que determine la ley</option>
                    <option value="02">02 - Presentación fuera del plazo</option>
                    <option value="03">03 - Modificación por el deudor tributario</option>
                    <option value="04">04 - Modificación por orden SUNAT</option>
                    <option value="05">05 - Modificación por orden judicial</option>
                    <option value="06">06 - Otros casos</option>
                  </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                  <button type="submit" class="btn btn-danger w-100">
                    <i class="fa fa-file-text-o"></i> Generar RCE
                  </button>
                </div>
              </div>

              <div class="row mt-3">
                <div class="col-md-12">
                  <div class="alert alert-info mb-0">
                    <i class="fa fa-info-circle"></i>
                    <strong>Nota:</strong> Este proceso generará el archivo TXT con todas las compras registradas en el período seleccionado.
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Card: Vista previa del último archivo generado -->
        <div id="cardVistaPrevia" class="card shadow-sm" style="display: none;">
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fa fa-eye"></i> Último Archivo Generado</h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <p class="mb-1"><strong>Nombre del Archivo:</strong></p>
                <p id="previewNombreArchivo" class="text-monospace"></p>
              </div>
              <div class="col-md-3">
                <p class="mb-1"><strong>Total de Registros:</strong></p>
                <p id="previewTotalRegistros" class="fs-4 fw-bold text-primary"></p>
              </div>
              <div class="col-md-3">
                <p class="mb-1"><strong>Estado:</strong></p>
                <span id="previewEstado" class="badge bg-success fs-6">GENERADO</span>
              </div>
            </div>
            <div class="row mt-3">
              <div class="col-md-12 text-end">
                <button class="btn btn-primary" id="btnDescargarUltimo">
                  <i class="fa fa-download"></i> Descargar Archivo
                </button>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- ===================================================================== -->
      <!-- TAB: HISTORIAL DE EXPORTACIONES -->
      <!-- ===================================================================== -->
      <div class="tab-pane fade" id="panelHistorial">

        <!-- Filtros de búsqueda -->
        <div class="card shadow-sm mb-4">
          <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fa fa-filter"></i> Filtros de Búsqueda</h5>
          </div>
          <div class="card-body">
            <form id="formFiltrosHistorial">
              <div class="row">
                <div class="col-md-3">
                  <label class="form-label">Tipo de Registro</label>
                  <select class="form-select" id="filtroTipo">
                    <option value="">Todos</option>
                    <option value="RVIE">RVIE - Ventas</option>
                    <option value="RCE">RCE - Compras</option>
                  </select>
                </div>
                <div class="col-md-2">
                  <label class="form-label">Año</label>
                  <select class="form-select" id="filtroAnio">
                    <option value="">Todos</option>
                    <!-- Generado dinámicamente -->
                  </select>
                </div>
                <div class="col-md-2">
                  <label class="form-label">Mes</label>
                  <select class="form-select" id="filtroMes">
                    <option value="">Todos</option>
                    <option value="01">Enero</option>
                    <option value="02">Febrero</option>
                    <option value="03">Marzo</option>
                    <option value="04">Abril</option>
                    <option value="05">Mayo</option>
                    <option value="06">Junio</option>
                    <option value="07">Julio</option>
                    <option value="08">Agosto</option>
                    <option value="09">Septiembre</option>
                    <option value="10">Octubre</option>
                    <option value="11">Noviembre</option>
                    <option value="12">Diciembre</option>
                  </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                  <button type="submit" class="btn btn-primary w-100">
                    <i class="fa fa-search"></i> Buscar
                  </button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                  <button type="button" class="btn btn-secondary w-100" id="btnLimpiarFiltros">
                    <i class="fa fa-refresh"></i> Limpiar
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Tabla de historial -->
        <div class="card shadow-sm">
          <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fa fa-list"></i> Exportaciones Generadas</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="tblHistorialExportaciones" class="table table-striped table-hover" style="width: 100%;">
                <thead class="table-dark">
                  <tr>
                    <th>ID</th>
                    <th>Tipo</th>
                    <th>Período</th>
                    <th>Oportunidad</th>
                    <th>Nombre Archivo</th>
                    <th>Registros</th>
                    <th>Estado</th>
                    <th>Fecha Generación</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Cargado dinámicamente -->
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>

      <!-- ===================================================================== -->
      <!-- TAB: AYUDA -->
      <!-- ===================================================================== -->
      <div class="tab-pane fade" id="panelAyuda">

        <div class="row">
          <!-- ¿Qué es SIRE? -->
          <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
              <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fa fa-question-circle"></i> ¿Qué es SIRE?</h5>
              </div>
              <div class="card-body">
                <p>
                  El <strong>Sistema Integrado de Registros Electrónicos (SIRE)</strong> es una plataforma de SUNAT
                  que permite a los contribuyentes llevar sus libros y registros electrónicos de forma obligatoria.
                </p>
                <p>Los principales registros son:</p>
                <ul>
                  <li><strong>RVIE:</strong> Registro de Ventas e Ingresos Electrónico</li>
                  <li><strong>RCE:</strong> Registro de Compras Electrónico</li>
                </ul>
                <p class="mb-0">
                  <small class="text-muted">
                    El sistema genera archivos TXT con nomenclatura de 35 caracteres según especificaciones de SUNAT.
                  </small>
                </p>
              </div>
            </div>
          </div>

          <!-- Formato del Archivo -->
          <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
              <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fa fa-file-code-o"></i> Formato del Archivo</h5>
              </div>
              <div class="card-body">
                <p><strong>Nomenclatura del archivo (35 caracteres):</strong></p>
                <pre class="bg-light p-2 rounded text-monospace" style="font-size: 11px;">LE + RUC(11) + AAAA + MM + 00 + LIBRO(6) + OPO(2) + IND(6)</pre>
                <p><strong>Ejemplo:</strong></p>
                <pre class="bg-light p-2 rounded text-monospace" style="font-size: 11px;">LE20123456789202501001RVIE0111210.txt</pre>
                <p class="mb-0"><strong>Estructura interna:</strong></p>
                <ul class="small mb-0">
                  <li>Separador: <code>|</code> (pipe)</li>
                  <li>Sin cabeceras de columnas</li>
                  <li>RVIE: 37 campos (Anexo 3)</li>
                  <li>RCE: 40 campos (Anexo 11)</li>
                </ul>
              </div>
            </div>
          </div>

          <!-- Código de Oportunidad -->
          <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
              <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fa fa-calendar"></i> Código de Oportunidad</h5>
              </div>
              <div class="card-body">
                <table class="table table-sm table-bordered">
                  <thead>
                    <tr>
                      <th>Código</th>
                      <th>Descripción</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr><td>01</td><td>A la fecha de vencimiento o que determine la ley</td></tr>
                    <tr><td>02</td><td>Presentación fuera del plazo</td></tr>
                    <tr><td>03</td><td>Modificación por el deudor tributario</td></tr>
                    <tr><td>04</td><td>Modificación por orden SUNAT</td></tr>
                    <tr><td>05</td><td>Modificación por orden judicial</td></tr>
                    <tr><td>06</td><td>Otros casos</td></tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Indicadores -->
          <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
              <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fa fa-cogs"></i> Indicadores (6 dígitos)</h5>
              </div>
              <div class="card-body">
                <p><strong>Posiciones de los indicadores:</strong></p>
                <ol class="small">
                  <li><strong>Posición 1:</strong> Reemplaza (1) o Acepta propuesta SUNAT (0)</li>
                  <li><strong>Posición 2:</strong> Estado operaciones (1=Activo, 0=Cierre)</li>
                  <li><strong>Posición 3:</strong> Moneda (1=Soles, 2=Dólares)</li>
                  <li><strong>Posición 4:</strong> Libro simplificado (1=Sí, 2=No)</li>
                  <li><strong>Posición 5:</strong> Identidad de terceros (1=Sí, 0=No)</li>
                  <li><strong>Posición 6:</strong> Genera sin movimiento (1=Sí, 0=No)</li>
                </ol>
                <p class="mb-0"><strong>Ejemplo típico:</strong> <code>111210</code></p>
              </div>
            </div>
          </div>

          <!-- Pasos para usar el sistema -->
          <div class="col-md-12">
            <div class="card shadow-sm">
              <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fa fa-list-ol"></i> Pasos para Generar Archivos SIRE</h5>
              </div>
              <div class="card-body">
                <ol>
                  <li><strong>Configurar datos iniciales:</strong> Haga clic en "Configuración" para establecer el RUC y parámetros de su empresa.</li>
                  <li><strong>Seleccionar período:</strong> Elija el año y mes del cual desea generar el archivo.</li>
                  <li><strong>Seleccionar oportunidad:</strong> Usualmente es "01" para presentación en plazo normal.</li>
                  <li><strong>Generar archivo:</strong> Haga clic en "Generar RVIE" o "Generar RCE" según corresponda.</li>
                  <li><strong>Descargar TXT:</strong> Una vez generado, descargue el archivo desde el botón de descarga.</li>
                  <li><strong>Subir a SUNAT:</strong> Ingrese a <strong>SUNAT Operaciones en Línea</strong> → <strong>SIRE</strong> y cargue el archivo generado.</li>
                  <li><strong>Verificar estado:</strong> SUNAT validará el archivo. Revise el estado en el portal para confirmar aceptación.</li>
                </ol>
                <div class="alert alert-warning">
                  <i class="fa fa-exclamation-triangle"></i>
                  <strong>Importante:</strong> Antes de subir a SUNAT, verifique que todos sus comprobantes estén correctamente registrados en el sistema.
                  No se incluyen documentos con estados inválidos.
                </div>
              </div>
            </div>
          </div>

        </div>

      </div>

    </div>

  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: CONFIGURACIÓN SIRE -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalConfiguracionSIRE" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">
          <i class="fa fa-cog"></i> Configuración SIRE
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formConfiguracionSIRE">

          <!-- Datos básicos -->
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">RUC de la Empresa *</label>
              <input type="text" class="form-control" id="config_ruc" name="ruc" maxlength="11" pattern="[0-9]{11}" required>
              <small class="text-muted">11 dígitos numéricos</small>
            </div>
            <div class="col-md-6">
              <label class="form-label">Período Obligado Desde *</label>
              <input type="date" class="form-control" id="config_periodo_desde" name="periodo_obligado_desde" required>
              <small class="text-muted">Fecha desde la cual está obligado a llevar SIRE</small>
            </div>
          </div>

          <!-- Indicadores (6 dígitos) -->
          <h6 class="border-bottom pb-2 mb-3">Indicadores del Archivo</h6>

          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">¿Generar con propuesta o reemplazar?</label>
              <select class="form-select" id="config_propuesta" name="generar_propuesta_aceptar">
                <option value="0">0 - Acepta propuesta SUNAT</option>
                <option value="1" selected>1 - Reemplaza propuesta</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Moneda Principal</label>
              <select class="form-select" id="config_moneda" name="moneda_principal">
                <option value="1" selected>1 - Soles (PEN)</option>
                <option value="2">2 - Dólares (USD)</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">¿Incluir comprobantes anulados?</label>
              <select class="form-select" id="config_anulados" name="incluir_anulados">
                <option value="0" selected>No</option>
                <option value="1">Sí</option>
              </select>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-3">
              <label class="form-label">Reemplaza (Pos. 1)</label>
              <select class="form-select" id="config_ind_reemplaza" name="indicador_reemplaza">
                <option value="1" selected>1</option>
                <option value="0">0</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Estado (Pos. 2)</label>
              <select class="form-select" id="config_ind_estado" name="indicador_estado">
                <option value="1" selected>1</option>
                <option value="0">0</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Moneda (Pos. 3)</label>
              <select class="form-select" id="config_ind_moneda" name="indicador_moneda">
                <option value="1" selected>1 - Soles</option>
                <option value="2">2 - Dólares</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Libro Simplif. (Pos. 4)</label>
              <select class="form-select" id="config_ind_libro" name="indicador_libro_simplif">
                <option value="2" selected>2 - No</option>
                <option value="1">1 - Sí</option>
              </select>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Identidad Terceros (Pos. 5)</label>
              <select class="form-select" id="config_ind_entidad" name="indicador_entidad">
                <option value="1" selected>1 - Sí</option>
                <option value="0">0 - No</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Genera sin Mov. (Pos. 6)</label>
              <select class="form-select" id="config_ind_sin_mov" name="indicador_genera_sin_mov">
                <option value="0" selected>0 - No</option>
                <option value="1">1 - Sí</option>
              </select>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="alert alert-info">
                <i class="fa fa-info-circle"></i>
                <strong>Vista previa de indicadores:</strong>
                <span id="vistaIndicadores" class="fs-5 fw-bold ms-2">111210</span>
              </div>
            </div>
          </div>

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btnGuardarConfiguracion">
          <i class="fa fa-save"></i> Guardar Configuración
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
<script type="text/javascript" src="scripts/sire.js"></script>
<?php
}
ob_end_flush();
?>
