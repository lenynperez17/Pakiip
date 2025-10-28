<?php
//Activamos el almacenamiento del Buffer
ob_start();
// SEGURIDAD: Usar Conexion.php que ya maneja sesiones de forma segura
require_once "../config/Conexion.php";

if (!isset($_SESSION["nombre"])) {
  header("Location: ../vistas/login.php");
} else {
  require 'header.php';

  if ($_SESSION['Logistica'] == 1) {

    ?>
            <!--Contenido-->

                <div class="content-header">
                  <h1>
                    <i class="fa fa-balance-scale"></i> Unidades de Medida SUNAT (Catálogo 03)
                    <button class="btn btn-primary btn-sm" onclick="mostrarform(true)" data-bs-toggle="modal"
                      data-bs-target="#modalUmedidaSunat">
                      <i class="fa fa-plus"></i> Agregar
                    </button>
                  </h1>
                  <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
                    <li class="breadcrumb-item active">Unidades SUNAT</li>
                  </ol>
                </div>

                <div class="row">
                  <div class="col-md-12">
                    <div class="card">
                      <div class="card-header">
                        <h3 class="card-title">
                          <i class="fa fa-list"></i> Catálogo Oficial SUNAT
                          <span class="badge bg-info" id="total-registros">0 registros</span>
                        </h3>
                      </div>
                      <div class="card-body">
                        <div class="alert alert-info">
                          <i class="fa fa-info-circle"></i>
                          <strong>Información:</strong> Catálogo oficial de unidades de medida según SUNAT (Catálogo 03).
                          Total de unidades: <strong>447</strong>
                        </div>
                        <div class="table-responsive">
                          <table id="tbllistado" class="table table-striped table-bordered table-hover" style="width: 100% !important;">
                            <thead class="table-dark">
                              <tr>
                                <th style="width: 80px;">Código</th>
                                <th>Descripción</th>
                                <th style="width: 150px;">Símbolo</th>
                                <th style="width: 120px;">Estado</th>
                                <th style="width: 100px;">Opciones</th>
                              </tr>
                            </thead>
                            <tbody>
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                  </div>
                </div><!-- /.row -->


            <!-- Modal Agregar/Editar UM SUNAT -->
            <div class="modal fade" id="modalUmedidaSunat" tabindex="-1" aria-labelledby="modalUmedidaSunatLabel" aria-hidden="true">
              <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                  <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalUmedidaSunatLabel">
                      <i class="fa fa-edit"></i> <span id="modal-title-text">Agregar Unidad de Medida SUNAT</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <form name="formulario" id="formulario" method="POST">
                      <input type="hidden" name="idsunat_um" id="idsunat_um">

                      <div class="row">
                        <!-- Código SUNAT -->
                        <div class="mb-3 col-lg-4">
                          <label for="codigo" class="form-label">
                            <i class="fa fa-barcode"></i> Código SUNAT: <span class="text-danger">*</span>
                          </label>
                          <input type="text" class="form-control" name="codigo" id="codigo"
                                 maxlength="3" required placeholder="Ej: NIU, ZZ"
                                 onkeyup="mayus(this);" pattern="[A-Z0-9]{1,3}">
                          <small class="text-muted">Código único de 1-3 caracteres</small>
                        </div>

                        <!-- Símbolo -->
                        <div class="mb-3 col-lg-4">
                          <label for="simbolo" class="form-label">
                            <i class="fa fa-tag"></i> Símbolo:
                          </label>
                          <input type="text" class="form-control" name="simbolo" id="simbolo"
                                 maxlength="10" placeholder="Ej: UND, KG, M">
                          <small class="text-muted">Representación corta (opcional)</small>
                        </div>

                        <!-- Estado -->
                        <div class="mb-3 col-lg-4">
                          <label for="estado" class="form-label">
                            <i class="fa fa-toggle-on"></i> Estado: <span class="text-danger">*</span>
                          </label>
                          <select class="form-select" name="estado" id="estado" required>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                          </select>
                        </div>

                        <!-- Descripción -->
                        <div class="mb-3 col-lg-12">
                          <label for="descripcion" class="form-label">
                            <i class="fa fa-align-left"></i> Descripción: <span class="text-danger">*</span>
                          </label>
                          <input type="text" class="form-control" name="descripcion" id="descripcion"
                                 maxlength="100" required placeholder="Nombre completo de la unidad"
                                 onkeyup="mayus(this);">
                          <small class="text-muted">Máximo 100 caracteres</small>
                        </div>

                        <!-- Notas adicionales -->
                        <div class="mb-3 col-lg-12">
                          <label for="notas" class="form-label">
                            <i class="fa fa-sticky-note"></i> Notas / Observaciones:
                          </label>
                          <textarea class="form-control" name="notas" id="notas" rows="3"
                                    maxlength="500" placeholder="Información adicional (opcional)"></textarea>
                          <small class="text-muted">Máximo 500 caracteres</small>
                        </div>
                      </div>

                      <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle"></i>
                        <strong>Importante:</strong> Asegúrese de que el código SUNAT coincida con el
                        <a href="https://cpe.sunat.gob.pe/sites/default/files/inline-files/CATALOGO_03-REV%20setiembre%2023.xls"
                           target="_blank">Catálogo 03 oficial de SUNAT</a>.
                      </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="cancelarform()">
                      <i class="fa fa-times"></i> Cancelar
                    </button>
                    <button id="btnGuardar" type="submit" class="btn btn-primary">
                      <i class="fa fa-save"></i> Guardar
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
      <script type="text/javascript" src="scripts/umedida_sunat.js"></script>
    <?php
}
ob_end_flush();
?>
