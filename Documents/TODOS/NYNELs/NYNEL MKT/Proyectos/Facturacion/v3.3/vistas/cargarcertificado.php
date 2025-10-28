<?php
//Activamos el almacenamiento del Buffer
ob_start();
// SEGURIDAD: Usar Conexion.php que ya maneja sesiones de forma segura
require_once "../config/Conexion.php";

if (!isset($_SESSION["nombre"])) {
  header("Location: ../vistas/login.php");
} else {
  require 'header.php';
  if ($_SESSION['Configuracion'] == 1) {
    ?>

    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css"> -->

   
    <div class="container">

      <!-- Start::row-1 -->
      <div class="row mb-5" id="formularioregistros">
        <form name="formulario" id="formulario" method="post">
          <div class="col-xl-12">
            <div class="card custom-card">
              <div class="card-header d-sm-flex d-block">
                <ul class="nav nav-tabs nav-tabs-header mb-0 d-sm-flex d-block" role="tablist">
                  <li class="nav-item m-1">
                    <a class="nav-link active" data-bs-toggle="tab" role="tab" aria-current="page" href="#personal-info"
                      aria-selected="true">Configuración SUNAT</a>
                  </li>

                </ul>
              </div>
              <div class="card-body">
                <div class="tab-content">
                  <div class="tab-pane show active" id="personal-info" role="tabpanel">
                    <div class="p-sm-3 p-0">

                      <div class="row gy-4 mb-4">


                        <div class="col-lg-2">
                          <label for="first-name" class="form-label">N° RUC(*): </label>
                          <input type="text" class="form-control" name="numeroruc" id="numeroruc" maxlength="20" required>
                        </div>

                        <div class="col-lg-4">
                          <input type="hidden" name="idcarga" id="idcarga">
                          <label for="first-name" class="form-label">Razón social(*):</label>
                          <input type="text" class="form-control" name="razon_social" id="razon_social" maxlength="100"
                            required>
                        </div>

                        <div class="col-lg-4">
                          <label for="last-name" class="form-label">Usuario sol secundario(*):</label>
                          <input type="text" class="form-control" name="usuarioSol" id="usuarioSol" maxlength="100"
                            required>
                        </div>

                        <div class="col-lg-2">
                          <label for="last-name" class="form-label">Clave sol secundario(*):</label>
                          <input type="password" class="form-control" name="claveSol" id="claveSol" maxlength="100"
                            required>
                        </div>

                        <div class="col-lg-3">
                          <label for="last-name" class="form-label">Ubicación de certificado(*):</label>
                          <select readonly class="form-control" name="rutacertificado" id="rutacertificado">
                            <option value="../certificado/" selected="true">../certificado/</option>
                            <option value="C:/sfs/certificado/">C:/sfs/certificado/</option>
                          </select>
                        </div>

                        <div class="col-lg-9">
                          <label for="last-name" class="form-label">Ruta del webservice FACTURA(*):</label>
                          <input type="text" class="form-control" name="rutaserviciosunat" id="rutaserviciosunat"
                            placeholder="../wsdl/billService.xml" maxlength="100"
                            value="https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService?wsdl" required>
                        </div>

                        <div hidden class="mb-3 col-lg-6">
                          <label>Ruta del webservice GUIA</label>
                          <input type="text" name="webserviceguia" id="webserviceguia"
                            placeholder="https://e-guiaremision.sunat.gob.pe/ol-ti-itemision-guia-gem/billService?wsdl"
                            class="for" value="https://e-beta.sunat.gob.pe/ol-ti-itemision-guia-gem-beta/billService?wsdl">
                        </div>


                        <div class="col-lg-6">
                          <label for="last-name" class="form-label">Cargar certificado (PFX)(*):</label>
                          <input type="file" class="form-control" name="pfx" id="pfx" value="">
                          <input type="hidden" name="cargarcertificado" id="cargarcertificado">
                        </div>


                        <div class="col-lg-6">
                          <label for="last-name" class="form-label">Clave de certificado PFX(*):</label>
                          <div class="input-group mb-3">
                            <input type="password" class="form-control" name="keypfx" id="keypfx">
                            <button class="btn btn-primary" type="button" id="btncargar" name="btncargar"
                              onclick="validarclave();">Button</button>
                          </div>
                        </div>

                        <div class="col-lg-4">
                          <label for="first-name" class="form-label">Nombre de archivo .pem actual: </label>
                          <input type="text" class="form-control" name="nombrepem" id="nombrepem" maxlength="30" required
                            readonly onkeypress="return NumCheck(event, this)">
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
                  <button hidden onclick="cancelarform()" type="button" class="btn btn-light m-1">
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
  <script type="text/javascript" src="scripts/cargarcertificado.js"></script>
  <?php
}
ob_end_flush();
?>