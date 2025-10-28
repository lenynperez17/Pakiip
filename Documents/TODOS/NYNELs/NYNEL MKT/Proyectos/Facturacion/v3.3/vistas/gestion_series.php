<?php
// Activamos el almacenamiento del Buffer
ob_start();
require_once "../config/Conexion.php";

if (!isset($_SESSION["nombre"])) {
    header("Location: ../vistas/login.php");
} else {
    require 'header.php';

    if ($_SESSION['Configuracion'] == 1) {
?>

<!-- Contenido -->
<div class="content-start transition">
    <div class="container-fluid dashboard">
        <div class="content-header">
            <h1><i class="fa fa-list-alt"></i> Gestión de Series y Numeración</h1>
            <p class="text-muted">Sistema flexible de gestión de series de comprobantes con control multi-empresa</p>
        </div>

        <!-- Tabs de navegación -->
        <ul class="nav nav-tabs mb-3" id="seriesTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-listado" data-bs-toggle="tab" data-bs-target="#panelListado" type="button">
                    <i class="fa fa-list"></i> Listado de Series
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-nueva" data-bs-toggle="tab" data-bs-target="#panelFormulario" type="button">
                    <i class="fa fa-plus"></i> Nueva/Editar Serie
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-asignaciones" data-bs-toggle="tab" data-bs-target="#panelAsignaciones" type="button">
                    <i class="fa fa-users"></i> Asignaciones
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-alertas" data-bs-toggle="tab" data-bs-target="#panelAlertas" type="button">
                    <i class="fa fa-bell"></i> Alertas <span class="badge bg-danger" id="badgeAlertas">0</span>
                </button>
            </li>
        </ul>

        <!-- Contenido de tabs -->
        <div class="tab-content" id="seriesTabContent">

            <!-- TAB 1: LISTADO DE SERIES -->
            <div class="tab-pane fade show active" id="panelListado" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fa fa-table"></i> Series Configuradas</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <button class="btn btn-success" id="btnNuevaSerie">
                                <i class="fa fa-plus"></i> Nueva Serie
                            </button>
                            <button class="btn btn-info" id="btnActualizarListado">
                                <i class="fa fa-refresh"></i> Actualizar
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table id="tblSeries" class="table table-striped table-bordered table-hover" style="width: 100%">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Tipo Doc</th>
                                        <th>Serie</th>
                                        <th>Rango</th>
                                        <th>Número Actual</th>
                                        <th>Disponibles</th>
                                        <th>% Uso</th>
                                        <th>Estado</th>
                                        <th>Electrónica</th>
                                        <th>Usuarios</th>
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

            <!-- TAB 2: FORMULARIO NUEVA/EDITAR SERIE -->
            <div class="tab-pane fade" id="panelFormulario" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fa fa-edit"></i> <span id="tituloFormulario">Nueva Serie de Comprobante</span></h5>
                    </div>
                    <div class="card-body">
                        <form id="formularioSerie" method="POST">
                            <input type="hidden" name="idserie_comprobante" id="idserie_comprobante">

                            <!-- Información básica -->
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Información Básica</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Tipo de Documento <span class="text-danger">*</span></label>
                                            <select class="form-select" name="tipo_documento_sunat" id="tipo_documento_sunat" required>
                                                <option value="">Seleccione...</option>
                                            </select>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Serie <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control text-uppercase" name="serie" id="serie"
                                                   maxlength="4" placeholder="F001, B001, etc." required>
                                            <small class="text-muted">Máximo 4 caracteres. Ej: F001, B002, T001</small>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Descripción</label>
                                            <input type="text" class="form-control" name="descripcion" id="descripcion"
                                                   maxlength="200" placeholder="Descripción de la serie">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Prefijo (Opcional)</label>
                                            <input type="text" class="form-control" name="prefijo" id="prefijo"
                                                   maxlength="10" placeholder="Ej: SUC1-">
                                            <small class="text-muted">Se agrega antes de la serie</small>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Sufijo (Opcional)</label>
                                            <input type="text" class="form-control" name="sufijo" id="sufijo"
                                                   maxlength="10" placeholder="Ej: -2025">
                                            <small class="text-muted">Se agrega después del número</small>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Longitud del Número</label>
                                            <input type="number" class="form-control" name="longitud_numero" id="longitud_numero"
                                                   value="8" min="4" max="12">
                                            <small class="text-muted">Cantidad de dígitos (con ceros a la izquierda)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Configuración de numeración -->
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Configuración de Numeración</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Número Actual</label>
                                            <input type="number" class="form-control" name="numero_actual" id="numero_actual"
                                                   value="0" min="0">
                                            <small class="text-muted">Último número emitido</small>
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Desde <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="numero_desde" id="numero_desde"
                                                   value="1" min="1" required>
                                            <small class="text-muted">Número inicial del rango</small>
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Hasta</label>
                                            <input type="number" class="form-control" name="numero_hasta" id="numero_hasta"
                                                   placeholder="Dejar vacío = ilimitado">
                                            <small class="text-muted">Número final (vacío = sin límite)</small>
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Alerta al (%)</label>
                                            <input type="number" class="form-control" name="alerta_porcentaje" id="alerta_porcentaje"
                                                   value="90" min="50" max="100">
                                            <small class="text-muted">% de uso para generar alerta</small>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="alert alert-info mb-0">
                                                <i class="fa fa-info-circle"></i>
                                                <strong>Ejemplo de numeración:</strong>
                                                <span id="ejemploNumeracion">F001-00000001</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Establecimiento -->
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Establecimiento / Sucursal</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Código Establecimiento</label>
                                            <input type="text" class="form-control" name="codigo_establecimiento" id="codigo_establecimiento"
                                                   value="0000" maxlength="4">
                                            <small class="text-muted">Código de 4 dígitos</small>
                                        </div>

                                        <div class="col-md-8 mb-3">
                                            <label class="form-label">Nombre del Establecimiento</label>
                                            <input type="text" class="form-control" name="establecimiento" id="establecimiento"
                                                   placeholder="Ej: Oficina Principal, Sucursal Lima">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Opciones adicionales -->
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Opciones Adicionales</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="es_electronica" id="es_electronica" value="1" checked>
                                                <label class="form-check-label" for="es_electronica">
                                                    Facturación Electrónica
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="es_contingencia" id="es_contingencia" value="1">
                                                <label class="form-check-label" for="es_contingencia">
                                                    Serie de Contingencia
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="requiere_autorizacion" id="requiere_autorizacion" value="1">
                                                <label class="form-check-label" for="requiere_autorizacion">
                                                    Requiere Autorización
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Fecha Inicio de Uso</label>
                                            <input type="date" class="form-control" name="fecha_inicio_uso" id="fecha_inicio_uso">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Fecha Fin de Uso</label>
                                            <input type="date" class="form-control" name="fecha_fin_uso" id="fecha_fin_uso">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="text-end">
                                <button type="button" class="btn btn-secondary" id="btnCancelarSerie">
                                    <i class="fa fa-times"></i> Cancelar
                                </button>
                                <button type="submit" class="btn btn-primary" id="btnGuardarSerie">
                                    <i class="fa fa-save"></i> Guardar Serie
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- TAB 3: ASIGNACIONES A USUARIOS -->
            <div class="tab-pane fade" id="panelAsignaciones" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fa fa-users"></i> Asignación de Series a Usuarios</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Seleccionar Usuario</label>
                                <select class="form-select" id="selectUsuarioAsignacion">
                                    <option value="">Cargando usuarios...</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <button class="btn btn-primary d-block w-100" id="btnCargarAsignaciones">
                                    <i class="fa fa-search"></i> Ver Series Asignadas
                                </button>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <button class="btn btn-success d-block w-100" id="btnAsignarNuevaSerie">
                                    <i class="fa fa-plus"></i> Asignar Serie
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="tblAsignaciones" class="table table-striped table-bordered">
                                <thead class="table-warning">
                                    <tr>
                                        <th>#</th>
                                        <th>Serie</th>
                                        <th>Tipo Documento</th>
                                        <th>Estado Serie</th>
                                        <th>Predeterminada</th>
                                        <th>Puede Emitir</th>
                                        <th>Puede Modificar</th>
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

            <!-- TAB 4: ALERTAS -->
            <div class="tab-pane fade" id="panelAlertas" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="fa fa-bell"></i> Alertas de Series</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <button class="btn btn-info" id="btnActualizarAlertas">
                                <i class="fa fa-refresh"></i> Actualizar Alertas
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table id="tblAlertas" class="table table-striped table-bordered">
                                <thead class="table-danger">
                                    <tr>
                                        <th>#</th>
                                        <th>Serie</th>
                                        <th>Tipo Alerta</th>
                                        <th>Mensaje</th>
                                        <th>Fecha</th>
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
    </div>
</div>

<!-- MODAL: Asignar Serie a Usuario -->
<div class="modal fade" id="modalAsignarSerie" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Asignar Serie a Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formAsignarSerie">
                    <input type="hidden" id="modal_idusuario" name="idusuario">

                    <div class="mb-3">
                        <label class="form-label">Serie</label>
                        <select class="form-select" id="modal_idserie_comprobante" name="idserie_comprobante" required>
                            <option value="">Seleccione serie...</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="modal_es_predeterminada" name="es_predeterminada" value="1">
                            <label class="form-check-label" for="modal_es_predeterminada">
                                Serie Predeterminada
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="modal_puede_emitir" name="puede_emitir" value="1" checked>
                            <label class="form-check-label" for="modal_puede_emitir">
                                Puede Emitir Comprobantes
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="modal_puede_modificar" name="puede_modificar" value="1">
                            <label class="form-check-label" for="modal_puede_modificar">
                                Puede Modificar Configuración
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnConfirmarAsignacion">
                    <i class="fa fa-check"></i> Asignar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Historial de Serie -->
<div class="modal fade" id="modalHistorial" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Historial de Cambios</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Acción</th>
                                <th>Usuario</th>
                                <th>Detalles</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyHistorial">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Atender Alerta -->
<div class="modal fade" id="modalAtenderAlerta" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">Atender Alerta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formAtenderAlerta">
                    <input type="hidden" id="alerta_idserie_alerta" name="idserie_alerta">

                    <div class="mb-3">
                        <label class="form-label">Comentarios</label>
                        <textarea class="form-control" id="alerta_comentarios" name="comentarios" rows="3"
                                  placeholder="Describa las acciones tomadas..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="btnConfirmarAtenderAlerta">
                    <i class="fa fa-check"></i> Marcar como Atendida
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
<script type="text/javascript" src="scripts/gestion_series.js"></script>
<?php
}
ob_end_flush();
?>
