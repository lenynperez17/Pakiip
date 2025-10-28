<?php

//Activamos el almacenamiento del Buffer

ob_start();

session_start();

if (!isset($_SESSION["nombre"])) {

  header("Location: ../vistas/login.php");

} else {

  require 'header.php';

  if ($_SESSION['Configuracion'] == 1) {

    ?>

    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css"> -->

    <!--Contenido-->

    <!-- Content Wrapper. Contains page content -->



    <div class="content-start transition">
      <section class="container-fluid dashboard">
        <div class="content-header">
          <h1>Empresa <button hidden class="btn btn-primary btn-sm" onclick="mostrarform(true)"> Agregar</button></h1>
        </div>
        <div class="row" style="background:white;">
          <div class="col-md-12">
            <div class="">
              <div class="panel-body table-responsive" id="listadoregistros">
                <table id="tbllistado" class="table table-striped" style="width: 100% !important;">
                  <thead>
                    
                    <th>Nombre comercial</th>
                    <th>Domicilio </th>
                    <th>Ruc</th>
                    <th>Telefono 1</th>
                    <th>Web</th>
                    <th>Logo</th>
                    <th>Opciones</th>
                  </thead>
                  <tbody>
                  </tbody>
                </table>
              </div>
            </div>
          </div><!-- /.row -->
      </section><!-- /.content -->
    </div><!-- /.content-wrapper -->

    <div class="container">
      <!-- Start::row-1 -->
      <div class="panel-body" id="formularioregistros">
        <form name="formulario" id="formulario" method="post">
          <div class="col-xl-12">
            <div class="card custom-card">
              <div class="card-header d-sm-flex d-block">
                <ul class="nav nav-tabs nav-tabs-header mb-0 d-sm-flex d-block" role="tablist">

                  <li class="nav-item m-1">
                    <a class="nav-link active" data-bs-toggle="tab" role="tab" aria-current="page" href="#personal-info"
                      aria-selected="true">Datos Empresa</a>
                  </li>

                  <li class="nav-item m-1">
                    <a class="nav-link" data-bs-toggle="tab" role="tab" aria-current="page" href="#notification-settings"
                      aria-selected="true">Contacto</a>
                  </li>

                  <li class="nav-item m-1">
                    <a class="nav-link" data-bs-toggle="tab" role="tab" aria-current="page" href="#cci"
                      aria-selected="true">Cuentas Bancarias</a>
                  </li>

                  <li class="nav-item m-1">
                    <a class="nav-link" data-bs-toggle="tab" role="tab" aria-current="page" href="#ajustes"
                      aria-selected="true">Ajustes</a>
                  </li>


                </ul>
              </div>






              <div class="card-body">
                <div class="tab-content">
                  <div class="tab-pane show active" id="personal-info" role="tabpanel">
                    <div class="p-sm-3 p-0">
                      <h6 class="fw-semibold mb-3">
                        Logo :
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
                      <h6 class="fw-semibold mb-3"></h6>
                      <div class="row gy-4 mb-4">

                        <div class="col-lg-2">
                          <label for="first-name" class="form-label">Número Ruc(*): <span
                              class="text-muted mb-0 chatpersonstatus">Press Enter</span></label>
                          <input type="text" class="form-control" name="ruc" id="ruc" maxlength="20" required>
                        </div>


                        <div class="col-lg-4">
                          <label for="first-name" class="form-label">Razón Social(*):</label>
                          <input type="hidden" name="idempresa" id="idempresa">
                          <input type="text" class="form-control" name="razonsocial" id="razonsocial" maxlength="100"
                            required>
                        </div>
                        <div class="col-lg-4">
                          <label for="last-name" class="form-label">Domicilio Fiscal(*):</label>
                          <input type="text" class="form-control" name="domicilio" id="domicilio" maxlength="100" required>
                        </div>

                        <div class="col-lg-2">
                          <label for="last-name" class="form-label">Nombre Comercial:</label>
                          <input type="text" class="form-control" name="ncomercial" id="ncomercial" maxlength="100"
                            required>
                        </div>

                        <div class="col-lg-2">
                          <label for="last-name" class="form-label">Ciudad:</label>
                          <input type="text" class="form-control" name="ciudad" id="ciudad" maxlength="100" required>
                        </div>

                        <div class="col-lg-2">
                          <label for="last-name" class="form-label">Distrito:</label>
                          <input type="text" class="form-control" name="distrito" id="distrito" maxlength="100" required>
                        </div>

                        <div class="col-lg-2">
                          <label for="last-name" class="form-label">Departamento:</label>
                          <input type="text" class="form-control" name="interior" id="interior" maxlength="100" required>
                        </div>

                        <div class="col-lg-2">
                          <label for="last-name" class="form-label">Ubigeo Dom Fiscal:</label>
                          <input type="text" class="form-control" name="codubigueo" id="codubigueo" maxlength="100"
                            required>
                        </div>


                        <div class="col-lg-2">
                          <label for="last-name" class="form-label">Código de establecimiento:</label>
                          <input type="text" class="form-control" name="ubigueo" id="ubigueo" maxlength="5" required>
                        </div>


                        <div class="col-lg-2">
                          <label for="last-name" class="form-label">Código PAÍS:</label>
                          <input type="text" class="form-control" name="codigopais" id="codigopais" maxlength="100" required
                            readonly>
                        </div>

                      </div>
                    </div>
                  </div>



                  <div class="tab-pane p-0" id="notification-settings" role="tabpanel">
                    <div class="p-sm-3 p-0">

                      <h6 class="fw-semibold mb-3"></h6>
                      <div class="row gy-4 mb-4">

                        <div class="col-lg-2">
                          <label for="first-name" class="form-label">Celular(*): </label>
                          <input type="text" class="form-control" name="tel2" id="tel2" maxlength="20" required>
                        </div>

                        <div class="col-lg-2">
                          <label for="first-name" class="form-label">Teléfono Fijo(*):</label>
                          <input type="text" class="form-control" name="tel1" id="tel1" maxlength="100" required>
                        </div>

                        <div class="col-lg-2">
                          <label for="last-name" class="form-label">Email contacto(*):</label>
                          <input type="text" class="form-control" name="correo" id="correo" maxlength="100" required>
                        </div>

                        <div class="col-lg-3">
                          <label for="last-name" class="form-label">Página Web:</label>
                          <input type="text" class="form-control" name="web" id="web" maxlength="100" required>
                        </div>


                        <div class="col-lg-3">
                          <label for="last-name" class="form-label">Web consultas:</label>
                          <input type="text" class="form-control" name="webconsul" id="webconsul" maxlength="100" required>
                        </div>

                      </div>
                    </div>
                  </div>




                  <div class="tab-pane p-0" id="cci" role="tabpanel">
                    <div class="p-sm-3 p-0">

                      <h6 class="fw-semibold mb-3"></h6>
                      <div class="row gy-4 mb-4">

                        <div class="col-lg-2">
                          <label for="first-name" class="form-label">Banco 1(*): </label>
                          <input type="text" class="form-control" id="banco1" name="banco1" maxlength="20">
                        </div>

                        <div class="col-lg-5">
                          <label for="first-name" class="form-label">N° Cuenta(*):</label>
                          <input type="text" class="form-control" name="cuenta1" id="cuenta1" maxlength="100">
                        </div>

                        <div class="col-lg-5">
                          <label for="last-name" class="form-label">CCI(*):</label>
                          <input type="text" class="form-control" name="cuentacci1" id="cuentacci1" maxlength="100">
                        </div>


                        <div class="col-lg-2">
                          <label for="first-name" class="form-label">Banco 2(*): </label>
                          <input type="text" class="form-control" id="banco2" name="banco2" maxlength="20">
                        </div>

                        <div class="col-lg-5">
                          <label for="first-name" class="form-label">N° Cuenta(*):</label>
                          <input type="text" class="form-control" name="cuenta2" id="cuenta2" maxlength="100">
                        </div>

                        <div class="col-lg-5">
                          <label for="last-name" class="form-label">CCI(*):</label>
                          <input type="text" class="form-control" name="cuentacci2" id="cuentacci2" maxlength="100">
                        </div>



                        <div class="col-lg-2">
                          <label for="first-name" class="form-label">Banco 3(*): </label>
                          <input type="text" class="form-control" id="banco3" name="banco3" maxlength="20">
                        </div>

                        <div class="col-lg-5">
                          <label for="first-name" class="form-label">N° Cuenta(*):</label>
                          <input type="text" class="form-control" name="cuenta3" id="cuenta3" maxlength="100">
                        </div>

                        <div class="col-lg-5">
                          <label for="last-name" class="form-label">CCI(*):</label>
                          <input type="text" class="form-control" name="cuentacci3" id="cuentacci3" maxlength="100">
                        </div>


                        <div class="col-lg-2">
                          <label for="first-name" class="form-label">Banco 4(*): </label>
                          <input type="text" class="form-control" id="banco4" name="banco4" maxlength="20">
                        </div>

                        <div class="col-lg-5">
                          <label for="first-name" class="form-label">N° Cuenta(*):</label>
                          <input type="text" class="form-control" name="cuenta4" id="cuenta4" maxlength="100">
                        </div>

                        <div class="col-lg-5">
                          <label for="last-name" class="form-label">CCI(*):</label>
                          <input type="text" class="form-control" name="cuentacci4" id="cuentacci4" maxlength="100">
                        </div>

                      </div>
                    </div>

                  </div>



                  <div class="tab-pane p-0" id="ajustes" role="tabpanel">
                    <div class="p-sm-3 p-0">

                      <h6 class="fw-semibold mb-3"></h6>
                      <div class="row gy-4 mb-4">

                        <div class="col-lg-3">
                          <label for="first-name" class="form-label">IVA(*): </label>
                          <input type="text" class="form-control" name="igv" id="igv" maxlength="5" required>
                        </div>

                        <div class="col-lg-3">
                          <label for="first-name" class="form-label">% Descuento maximo(*):</label>
                          <input type="text" class="form-control" name="porDesc" id="porDesc" maxlength="100" required>
                        </div>

                        <div class="col-lg-3">
                          <label for="first-name" class="form-label">Impresión por defecto(*): </label>
                          <select class="form-control" id="tipoimpresion" name="tipoimpresion">
                            <option value="00">TICKET</option>
                            <option value="02">A4 TAMAÑO COMPLETO</option>
                            <option value="01">A4 DOS COPIAS</option>
                          </select>
                        </div>


                        <div class="col-lg-3">
                          <label for="last-name" class="form-label">Texto libre que ira debajo del nombre</label>
                          <input type="text" class="form-control" name="textolibre" id="textolibre" maxlength="100">
                        </div>

                      </div>
                    </div>
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
    <!--Fin-Contenido-->

    <?php

  } else {

    require 'noacceso.php';

  }



  require 'footer.php';

  ?>

  <script type="text/javascript" src="scripts/empresa.js"></script>

  <?php

}

ob_end_flush();

?>