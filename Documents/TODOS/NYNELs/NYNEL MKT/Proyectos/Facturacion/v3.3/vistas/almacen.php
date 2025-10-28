<?php
// SEGURIDAD: Usar Conexion.php que ya maneja sesiones de forma segura
require_once "../config/Conexion.php";
//Activamos el almacenamiento del Buffer
ob_start();


if (!isset($_SESSION["nombre"])) {
  header("Location: ../vistas/login.php");
} else {
  require 'header.php';

  if ($_SESSION['Logistica'] == 1) {

    ?>


            <div class="content-header">
              <h1>Gestión de Almacenes <button class="btn btn-primary btn-sm" onclick="mostrarform(true)" data-bs-toggle="modal"
                  data-bs-target="#agregarsucursal"><i class="ri-add-line"></i> Agregar</button></h1>
            </div>

            <!-- Tarjetas de Estadísticas -->
            <div class="row" id="estadisticas-cards">
              <div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
                <div class="card overflow-hidden">
                  <div class="card-body">
                    <div class="d-flex">
                      <div class="flex-fill">
                        <h6 class="mb-2">Total Almacenes</h6>
                        <h3 class="fw-semibold mb-0" id="total_almacenes">0</h3>
                        <small class="text-muted" id="almacenes_estado">0 activos / 0 inactivos</small>
                      </div>
                      <div class="flex-shrink-0 ms-3">
                        <div class="avatar avatar-md bg-primary-transparent">
                          <div class="avatar-icon">
                            <i class="ri-building-line fs-24"></i>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
                <div class="card overflow-hidden">
                  <div class="card-body">
                    <div class="d-flex">
                      <div class="flex-fill">
                        <h6 class="mb-2">Productos Totales</h6>
                        <h3 class="fw-semibold mb-0" id="total_productos">0</h3>
                        <small class="text-muted">En todos los almacenes</small>
                      </div>
                      <div class="flex-shrink-0 ms-3">
                        <div class="avatar avatar-md bg-success-transparent">
                          <div class="avatar-icon">
                            <i class="ri-archive-line fs-24"></i>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
                <div class="card overflow-hidden">
                  <div class="card-body">
                    <div class="d-flex">
                      <div class="flex-fill">
                        <h6 class="mb-2">Valor Inventario</h6>
                        <h3 class="fw-semibold mb-0" id="valor_inventario">S/ 0.00</h3>
                        <small class="text-muted">Valor total del stock</small>
                      </div>
                      <div class="flex-shrink-0 ms-3">
                        <div class="avatar avatar-md bg-warning-transparent">
                          <div class="avatar-icon">
                            <i class="ri-money-dollar-circle-line fs-24"></i>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
                <div class="card overflow-hidden">
                  <div class="card-body">
                    <div class="d-flex">
                      <div class="flex-fill">
                        <h6 class="mb-2">Distribución</h6>
                        <h3 class="fw-semibold mb-0" id="tipo_distribucion">-</h3>
                        <small class="text-muted" id="tipo_detalle">Principal / Secundario / Temporal</small>
                      </div>
                      <div class="flex-shrink-0 ms-3">
                        <div class="avatar avatar-md bg-info-transparent">
                          <div class="avatar-icon">
                            <i class="ri-pie-chart-line fs-24"></i>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">

              <div class="col-md-12">
                <div class="card">
                  <div class="card-body">

                    <!-- Buscador Avanzado -->
                    <div class="mb-3 p-3 bg-light rounded">
                      <div class="row g-2">
                        <div class="col-md-3">
                          <label class="form-label small">Buscar por nombre:</label>
                          <input type="text" id="filtro_nombre" class="form-control form-control-sm" placeholder="Nombre del almacén...">
                        </div>
                        <div class="col-md-2">
                          <label class="form-label small">Tipo:</label>
                          <select id="filtro_tipo" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            <option value="PRINCIPAL">Principal</option>
                            <option value="SECUNDARIO">Secundario</option>
                            <option value="TEMPORAL">Temporal</option>
                          </select>
                        </div>
                        <div class="col-md-2">
                          <label class="form-label small">Estado:</label>
                          <select id="filtro_estado" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            <option value="1">Activos</option>
                            <option value="0">Inactivos</option>
                          </select>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label small">Responsable:</label>
                          <select id="filtro_responsable" class="form-select form-select-sm">
                            <option value="">Todos</option>
                          </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                          <button class="btn btn-secondary btn-sm w-100" onclick="limpiarFiltros()">
                            <i class="ri-refresh-line"></i> Limpiar
                          </button>
                        </div>
                      </div>
                    </div>

                    <div class="table-responsive">
                      <table id="tbllistado" class="table table-striped table-hover" style="width: 100% !important;">
                        <thead>
                          <tr>
                            <th scope="col">Nombre</th>
                            <th scope="col">Dirección</th>
                            <th scope="col">Tipo</th>
                            <th scope="col">Responsable</th>
                            <th scope="col">Productos</th>
                            <th scope="col">Valor</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Opciones</th>
                            <th scope="col">Ver Productos</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>

                          </tr>
                        </tbody>
                      </table>

                    </div>
                  </div>
                </div>
              </div>



            </div><!-- /.row -->



        <div class="modal fade text-left" id="agregarsucursal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1"
          aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel1">Gestión de Almacén</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">

                <form name="formulario" id="formulario" method="POST">
                  <input type="hidden" name="idalmacen" id="idalmacen">

                  <!-- Información Básica -->
                  <h6 class="mb-3 text-primary"><i class="ri-information-line"></i> Información Básica</h6>
                  <div class="row">
                    <div class="mb-3 col-lg-6">
                      <label for="nombrea" class="col-form-label">Nombre: <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" name="nombrea" id="nombrea" placeholder="Nombre del almacén" required
                        onkeyup="mayus(this);">
                    </div>
                    <div class="mb-3 col-lg-6">
                      <label for="tipo_almacen" class="col-form-label">Tipo de Almacén: <span class="text-danger">*</span></label>
                      <select class="form-select" name="tipo_almacen" id="tipo_almacen" required>
                        <option value="SECUNDARIO">Secundario</option>
                        <option value="PRINCIPAL">Principal</option>
                        <option value="TEMPORAL">Temporal</option>
                      </select>
                    </div>
                  </div>

                  <div class="row">
                    <div class="mb-3 col-lg-12">
                      <label for="direccion" class="col-form-label">Dirección: <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" name="direccion" id="direccion" placeholder="Dirección completa" required
                        onkeyup="mayus(this);">
                    </div>
                  </div>

                  <!-- Información de Contacto -->
                  <h6 class="mb-3 text-primary mt-3"><i class="ri-contacts-line"></i> Información de Contacto</h6>
                  <div class="row">
                    <div class="mb-3 col-lg-6">
                      <label for="telefono" class="col-form-label">Teléfono:</label>
                      <input type="text" class="form-control" name="telefono" id="telefono" placeholder="Ej: 987654321">
                    </div>
                    <div class="mb-3 col-lg-6">
                      <label for="email" class="col-form-label">Email:</label>
                      <input type="email" class="form-control" name="email" id="email" placeholder="ejemplo@correo.com">
                    </div>
                  </div>

                  <!-- Responsable y Capacidad -->
                  <h6 class="mb-3 text-primary mt-3"><i class="ri-user-settings-line"></i> Administración</h6>
                  <div class="row">
                    <div class="mb-3 col-lg-6">
                      <label for="idusuario_responsable" class="col-form-label">Responsable:</label>
                      <select class="form-select" name="idusuario_responsable" id="idusuario_responsable">
                        <option value="">Sin asignar</option>
                      </select>
                    </div>
                    <div class="mb-3 col-lg-6">
                      <label for="capacidad_max" class="col-form-label">Capacidad Máxima (unidades):</label>
                      <input type="number" class="form-control" name="capacidad_max" id="capacidad_max" placeholder="Ej: 10000" min="0">
                    </div>
                  </div>

                  <!-- Notas Adicionales -->
                  <div class="row">
                    <div class="mb-3 col-lg-12">
                      <label for="notas" class="col-form-label">Notas / Observaciones:</label>
                      <textarea class="form-control" name="notas" id="notas" rows="3" placeholder="Información adicional sobre el almacén..."></textarea>
                    </div>
                  </div>

              </div>
              <div class="modal-footer">
                <button onclick="cancelarform()" type="button" class="btn btn-danger" data-bs-dismiss="modal">
                  <i class="ri-close-line"></i> Cancelar
                </button>
                <button id="btnGuardar" type="submit" class="btn btn-primary ml-1">
                  <i class="ri-save-line"></i> Guardar
                </button>
              </div>
              </form>

            </div>
          </div>
        </div>

        <!-- Modal Ver Productos del Almacén -->
        <div class="modal fade" id="modalProductosAlmacen" tabindex="-1" aria-labelledby="modalProductosAlmacenLabel" aria-hidden="true">
          <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
              <div class="modal-header bg-primary-gradient">
                <h5 class="modal-title text-white" id="modalProductosAlmacenLabel">
                  <i class="ri-archive-line"></i> Productos del Almacén: <span id="nombre_almacen_modal"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">

                <!-- Resumen del almacén -->
                <div class="row mb-3">
                  <div class="col-md-3">
                    <div class="card bg-primary-transparent">
                      <div class="card-body text-center">
                        <h6 class="mb-1">Total Productos</h6>
                        <h3 class="mb-0" id="resumen_total_productos">0</h3>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="card bg-success-transparent">
                      <div class="card-body text-center">
                        <h6 class="mb-1">Total Unidades</h6>
                        <h3 class="mb-0" id="resumen_total_unidades">0</h3>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="card bg-warning-transparent">
                      <div class="card-body text-center">
                        <h6 class="mb-1">Valor Total</h6>
                        <h3 class="mb-0" id="resumen_valor_total">S/ 0.00</h3>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="card bg-info-transparent">
                      <div class="card-body text-center">
                        <h6 class="mb-1">Stock Promedio</h6>
                        <h3 class="mb-0" id="resumen_stock_promedio">0</h3>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Tabla de productos -->
                <div class="table-responsive">
                  <table id="tblProductosAlmacen" class="table table-striped table-hover table-sm" style="width: 100% !important;">
                    <thead class="table-primary">
                      <tr>
                        <th>Código</th>
                        <th>Nombre del Producto</th>
                        <th>Stock</th>
                        <th>Precio Venta</th>
                        <th>Precio Compra</th>
                        <th>Valor Total</th>
                        <th>Sede/Almacén</th>
                      </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot class="table-light">
                      <tr>
                        <th colspan="5" class="text-end">TOTAL:</th>
                        <th id="total_valor_productos">S/ 0.00</th>
                        <th></th>
                      </tr>
                    </tfoot>
                  </table>
                </div>

              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                  <i class="ri-close-line"></i> Cerrar
                </button>
                <button type="button" class="btn btn-success" onclick="exportarProductosExcel()">
                  <i class="ri-file-excel-line"></i> Exportar Excel
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
    <script type="text/javascript" src="scripts/almacen.js"></script>
    <?php
}
ob_end_flush();
?>