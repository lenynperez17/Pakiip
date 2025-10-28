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
        <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css"> -->
        <!--Contenido-->

      
                <div class="content-header">
                    <h1>Proveedores <button class="btn btn-success btn-sm" onclick="mostrarform(true)" data-bs-toggle="modal"
                            data-bs-target="#agregarProveedores">Agregar</button></h1>
                </div>

                <div class="row">

                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="tbllistado" class="table table-striped" style="width: 100% !important;">
                                        <thead>
                                            <th>Razón Social</th>
                                            <th>Ruc</th>
                                            <th>Teléfono</th>
                                            <th>Correo</th>
                                            <th>Estado</th>
                                            <th>Opciones</th>
                                        </thead>
                                        <tbody>
                                        </tbody>

                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div><!-- /.row -->



        <div class="modal fade text-left" id="agregarProveedores" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="myModalLabel1">Añade nuevo proveedor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form name="formulario" id="formulario" method="POST">
                            <div class="row">
                                <div class="mb-3 col-lg-4">
                                    <label for="recipient-name" class="col-form-label">Nombres:</label>
                                    <input type="hidden" name="idpersona" id="idpersona">
                                    <input type="hidden" name="tipo_persona" id="tipo_persona" value="proveedor">
                                    <input type="text" class="form-control" name="nombres" id="nombres" maxlength="100" required
                                        onkeyup="mayus(this);">
                                </div>

                                <div class="mb-3 col-lg-4">
                                    <label for="recipient-name" class="col-form-label">Apellidos:</label>
                                    <input type="text" class="form-control" name="apellidos" id="apellidos" maxlength="100"
                                        required onkeyup="mayus(this);">
                                </div>

                                <div class="mb-3 col-lg-4">
                                    <label for="recipient-name" class="col-form-label">Tipo Documento:</label>
                                    <select class="form-control" name="tipo_documento" id="tipo_documento" required>
                                        <option value="0"> S/D </option>
                                        <option value="1"> DNI </option>
                                        <option value="4"> CE </option>
                                        <option value="6"> RUC </option>
                                        <option value="7"> PASAPORTE </option>
                                        <option value="A"> CED </option>
                                    </select>
                                </div>

                                <div class="mb-3 col-lg-4">
                                    <label for="recipient-name" class="col-form-label" id="l_tipo_documento">N°
                                        Documento:</label>
                                    <input type="text" class="form-control" name="numero_documento" id="numero_documento"
                                        maxlength="11" onblur="validarProveedor();" onkeypress="return NumCheck(event, this)">
                                </div>

                                <div class="mb-3 col-lg-4">
                                    <label for="recipient-name" class="col-form-label">Razón Social:</label>
                                    <input type="text" class="form-control" name="razon_social" id="razon_social"
                                        maxlength="100" required onkeyup="mayus(this);">
                                </div>

                                <div class="mb-3 col-lg-4">
                                    <label for="recipient-name" class="col-form-label">Nombre comercial:</label>
                                    <input type="text" class="form-control" name="nombre_comercial" id="nombre_comercial"
                                        maxlength="100" required onkeyup="mayus(this);">
                                </div>

                                <div class="mb-3 col-lg-4">
                                    <label for="recipient-name" class="col-form-label">Domicilio fiscal:</label>
                                    <input type="text" class="form-control" name="domicilio_fiscal" id="domicilio_fiscal"
                                        maxlength="100" required onkeyup="mayus(this);">
                                </div>

                                <div class="mb-3 col-lg-4">
                                    <label for="recipient-name" class="col-form-label">Departamento:</label>
                                    <!-- <select  class="form-control" name="iddepartamento" id="iddepartamento" onchange="llenarCiudad()">
                           </select> -->
                                    <input type="text" class="form-control" name="iddepartamento" id="iddepartamento">
                                </div>


                                <div class="mb-3 col-lg-4">
                                    <label for="recipient-name" class="col-form-label">Ciudad:</label>
                                    <!-- <select  class="form-control" name="idciudad" id="idciudad"  onchange="llenarDistrito()">
                            </select> -->
                                    <input type="text" class="form-control" name="idciudad" id="idciudad">
                                </div>

                                <div class="mb-3 col-lg-4">
                                    <label for="recipient-name" class="col-form-label">Distrito:</label>
                                    <!-- <select  class="form-control" name="iddistrito" id="iddistrito">
                            </select> -->
                                    <input type="text" class="form-control" name="iddistrito" id="iddistrito">
                                </div>


                                <div class="mb-3 col-lg-4">
                                    <label for="recipient-name" class="col-form-label">Teléfono:</label>
                                    <input type="text" class="form-control" name="telefono1" id="telefono1" maxlength="15"
                                        onkeypress="return NumCheck(event, this)">
                                    </select>
                                </div>

                                <div class="mb-3 col-lg-4">
                                    <label for="recipient-name" class="col-form-label">Celular:</label>
                                    <input type="text" class="form-control" name="telefono2" id="telefono2" maxlength="15"
                                        onkeypress="return NumCheck(event, this)">
                                </div>

                                <div class="mb-3 col-lg-12">
                                    <label for="recipient-name" class="col-form-label">Email:</label>
                                    <input type="text" class="form-control" name="email" id="email" maxlength="50"
                                        placeholder="Email">
                                    </select>
                                </div>

                            </div>

                    </div>
                    <div class="modal-footer">
                        <button onclick="cancelarform()" type="button" class="btn btn-danger" data-bs-dismiss="modal">
                            <i class="bx bx-x d-block d-sm-none"></i>
                            <span class="d-none d-sm-block">Cancelar</span>
                        </button>
                        <button id="btnGuardar" type="submit" class="btn btn-primary ml-1">
                            <i class="bx bx-check d-block d-sm-none"></i>
                            <span class="d-none d-sm-block">Agregar</span>
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
    <script type="text/javascript" src="scripts/proveedor.js"></script>
    <?php
}
ob_end_flush();
?>