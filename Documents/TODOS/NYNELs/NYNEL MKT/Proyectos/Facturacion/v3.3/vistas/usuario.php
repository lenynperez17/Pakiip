<?php
//Activamos el almacenamiento del Buffer
ob_start();
// SEGURIDAD: Usar Conexion.php que ya maneja sesiones de forma segura
require_once "../config/Conexion.php";

if (!isset($_SESSION["nombre"])) {
  header("Location: ../vistas/login.php");
} else {
  require 'header.php';
  if ($_SESSION['RRHH'] == 1) {

    ?>
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css"> -->
    <!--Contenido-->
    <!-- Content Wrapper. Contains page content -->

    <div class="content-start transition">
      <div class="container-fluid dashboard">
        <div class="content-header">
          <h1>Usuarios <button class="btn btn-primary btn-sm" onclick="mostrarform(true)" id="btnagregar"> Agregar</button>
          </h1>
        </div>

        <div class="row">

          <div class="col-md-12">
            <div class="card">
              <div class="">

                <div class="table-responsive" id="listadoregistros">
                  <table id="tbllistado" class="table table-striped" style="width: 98% !important;">
                    <thead>
                      <th>Opciones</th>
                      <th>Usuario</th>
                      <th>Nombre</th>
                      <th>Apellidos</th>
                      <th>Cargo</th>
                      <th>Documento</th>
                      <th>Número</th>
                      <th>Teléfono</th>
                      <th>Email</th>
                      <th>Foto</th>
                      <th>Estado</th>

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


      </div><!-- End Container-->
    </div><!-- End Content-->

    <div class="container">

      <!-- Start::row-1 -->
      <div class="row mb-5" id="formularioregistros">
        <form name="formulario" id="formulario" method="POST">
          <div class="col-xl-12">
            <div class="card custom-card">
              <div class="card-header d-sm-flex d-block">
                <ul class="nav nav-tabs nav-tabs-header mb-0 d-sm-flex d-block" role="tablist">
                  <li class="nav-item m-1">
                    <a class="nav-link active" data-bs-toggle="tab" role="tab" aria-current="page" href="#personal-info"
                      aria-selected="true">Datos del usuario</a>
                  </li>
                  <li class="nav-item m-1">
                    <a class="nav-link" data-bs-toggle="tab" role="tab" aria-current="page" href="#notification-settings"
                      aria-selected="true">Roles de acceso</a>
                  </li>

                </ul>
              </div>
              <div class="card-body">
                <div class="tab-content">
                  <div class="tab-pane show active" id="personal-info" role="tabpanel">
                    <div class="p-sm-3 p-0">
                      <h6 class="fw-semibold mb-3">
                        Imagen :
                      </h6>
                      <div class="mb-4 d-sm-flex align-items-center">
                        <div class="mb-0 me-5">
                          <span class="avatar avatar-xxl avatar-rounded">
                            <img src="../assets/images/faces/9.jpg" alt="" id="imagenmuestra">
                            <a href="javascript:void(0);" class="badge rounded-pill bg-primary avatar-badge">
                              <input type="file" class="position-absolute w-100 h-100 op-0" name="imagen" id="imagen">
                              <input type="hidden" name="imagenactual" id="imagenactual">
                              <i class="fe fe-camera"></i>
                            </a>
                          </span>
                        </div>
                        <div class="btn-group">
                          <a class="btn btn-primary" onclick="cambiarImagen()">Subir</a>
                          <a class="btn btn-light" onclick="removerImagen()">Remover</a>
                        </div>

                      </div>
                      <h6 class="fw-semibold mb-3">
                        Datos :
                      </h6>
                      <div class="row gy-4 mb-4">

                        <div class="col-lg-2">
                          <label for="first-name" class="form-label">Tipo documento(*): </label>
                          <select class="form-control" data-trigger name="tipo_documento" id="tipo_documento">
                            <option value="DNI">DNI</option>
                            <option value="RUC">RUC</option>
                            <option value="CEDULA">CEDULA</option>
                          </select>
                        </div>

                        <div class="col-lg-2">
                          <label for="first-name" class="form-label">Número(*): <span class="text-muted mb-0 chatpersonstatus">Press Enter</span></label>
                          <input type="text" class="form-control" name="num_documento" id="num_documento" maxlength="20"
                            required onkeypress="return NumCheck(event, this)">
                        </div>

                        <div class="col-lg-4">
                          <label for="first-name" class="form-label">Nombres(*):</label>
                          <input type="hidden" name="idusuario" id="idusuario">
                          <input type="text" class="form-control" name="nombre" id="nombre" maxlength="100" required
                            onkeyup="mayus(this)">
                        </div>
                        <div class="col-lg-4">
                          <label for="last-name" class="form-label">Apellidos(*):</label>
                          <input type="text" class="form-control" name="apellidos" id="apellidos" maxlength="100" required
                            onkeyup="mayus(this)">
                        </div>
                      </div>
                      <h6 class="fw-semibold mb-3">
                        Contacto :
                      </h6>
                      <div class="row gy-4 mb-4">

                        <div class="col-lg-4">
                          <label for="Contact-Details" class="form-label">Dirección :</label>
                          <input type="text" class="form-control" name="direccion" id="direccion" maxlength="70"
                            onkeyup="mayus(this)">
                        </div>

                        <div class="col-lg-4">
                          <label for="email-address" class="form-label">Correo :</label>
                          <input type="email" class="form-control" name="email" id="email" maxlength="50">
                        </div>

                        <div class="col-lg-4">
                          <label for="email-address" class="form-label">Celular :</label>
                          <input type="tel" class="form-control" name="telefono" id="telefono" maxlength="9"
                            onkeypress="return NumCheck(event, this)">
                        </div>

                        <h6 class="fw-semibold mb-3">
                          Credenciales :
                        </h6>

                        <div class="col-lg-4">
                          <label for="Contact-Details" class="form-label">Cargo :</label>
                          <select class="form-control" data-trigger name="cargo" id="cargo">
                            <option value="0">Administrador</option>
                            <option value="1">Ventas</option>
                            <option value="2">Logistica</option>
                            <option value="3">Contabilidad</option>
                          </select>
                        </div>

                        <div class="col-lg-4">
                          <label for="email-address" class="form-label">Usuario :</label>
                          <input type="text" class="form-control" name="login" id="login" maxlength="20" placeholder="Login"
                            required onkeyup="mayus(this)">
                        </div>

                        <div class="col-lg-4">
                          <label for="email-address" class="form-label">Contraseña :</label>
                          <input type="password" class="form-control" name="clave" id="clave" maxlength="20">
                        </div>

                      </div>
                    </div>
                  </div>


                  <div class="tab-pane p-0" id="notification-settings" role="tabpanel">
                    <ul class="list-group list-group-flush list-unstyled rounded-3">
                      <li class="list-group-item">
                        <div class="row gx-5 gy-3">

                          <div class="col-xl-7">
                            <div class="d-flex align-items-top justify-content-between mt-sm-0 mt-3">
                              <div class="mail-notification-settings">
                                <p class="fs-14 mb-1 fw-semibold">Roles de Módulo</p>
                                <p class="fs-12 mb-0 text-muted">Escoje los módulos correspondientes al menú</p>
                              </div>
                              <div>
                                <div class="custom-toggle-switch float-sm-end" , id="permisos">

                                </div>
                              </div>
                            </div>
                            <div class="d-flex align-items-top justify-content-between mt-3">
                              <div class="mail-notification-settings">
                                <p class="fs-14 mb-1 fw-semibold">Rol Correlativos</p>
                                <p class="fs-12 mb-0 text-muted">Selecciona el correlativo de los comprobantes.</p>
                              </div>
                              <div>
                                <div class="custom-toggle-switch float-sm-end" , id="series">

                                </div>
                              </div>
                            </div>
                            <div class="d-flex align-items-top justify-content-between mt-3">
                              <div class="mail-notification-settings">
                                <p class="fs-14 mb-1 fw-semibold">Rol para empresa</p>
                                <p class="fs-12 mb-0 text-muted">Asigna al usuario a la empresa correcta</p>
                              </div>
                              <div>
                                <div class="custom-toggle-switch float-sm-end" , id="empresas">

                                </div>
                              </div>
                            </div>


                          </div>
                        </div>
                      </li>

                    </ul>
                  </div>

                </div>
              </div>
              <div class="card-footer">
                <div class="float-end">
                  <button type="submit" id="btnGuardar" class="btn btn-primary m-1">
                    Guardar
                  </button>
                  <button onclick="cancelarform()" type="button" class="btn btn-light m-1">
                    Cancelar
                  </button>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
      <!--End::row-1 -->

    </div>



    <?php
  } else {
    require 'noacceso.php';
  }

  require 'footer.php';
  ?>

  <script type="text/javascript" src="scripts/usuario.js"></script>
  <?php
}
ob_end_flush();
?>