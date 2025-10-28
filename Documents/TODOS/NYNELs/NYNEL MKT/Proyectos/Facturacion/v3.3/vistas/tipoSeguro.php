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

        <div class="content-start transition">
        <div class="container-fluid dashboard">
              <div class="content-header">
                <h1>Tipo de seguro</h1>
                <p>Añade el seguro correspondiente</p>
              </div>
            
                    <div class="row">

                        <div class="col-12 col-md-3 col-lg-3">
                          <div class="card">
                          <form name="formulario" id="formulario" method="POST">
      
                              <div class="card-body">
                                <div class="mb-3">
                                  <label>Tipo de seguro</label>
                                  <select  class="form-control" name="tipoSeguro" id="tipoSeguro">
                                    <option value='SNP'>SNP</option>
                                    <option value='AFP'>AFP</option>
                                  </select>
                                </div>
                                <div class="mb-3">
                                  <label>Nombre de seguro</label>
                                  <input class="form-control" type="text" name="nombreSeguro" id="nombreSeguro" onkeyup="mayus(this)"  onkeypress="foco1(event)" required="true">
                                </div>
                                <div class="mb-3">
                                  <label>% SNP</label>
                                  <input type="text" class="form-control" name="snp" id="snp" placeholder="0.00"  
                                    onkeypress="return NumCheck(event, this)" required="true"> 
                                </div>
                                <div class="form-group mb-0">
                                  <label>Aporte obligatorio AFP</label>
                                  <input type="text" class="form-control" name="aoafp" id="aoafp" placeholder="0.00"  
                                    onkeypress="return NumCheck(event, this)" required="true"> 
                                </div>
                                <div class="mb-3">
                                  <label>Invalidez y sobrev.</label>
                                  <input type="text" class="form-control" name="invsob" id="invsob" placeholder="0.00"  
                                    onkeypress="return NumCheck(event, this)" required="true">
                                </div>
                                <div class="mb-3">
                                  <label>Comisión AFP</label>
                                  <input type="text" class="form-control" name="comiafp" id="comiafp" placeholder="0.00"  
                                    onkeypress="return NumCheck(event, this)" required="true">
                                </div>
                              </div>
                              <div class="card-footer text-right">
                                <button type="submit" id="btnGuardar" class="btn btn-primary">Guardar</button>
                                <button type="reset" class="btn btn-info">Limpiar</button>
                              </div>
                   
                        </div>
                    </div>

                    <div class="col-12 col-md-9 col-lg-9">
                        <div class="card">
                        <div class="card-body">
                        <div class="table-responsive">
                          <table id="tbllistado" class="table table-striped" style="width: 100% !important;">
                            <thead>
                              <tr>
                                    <th>Tipo de seguro</th>
                                    <th>Nombre seguro</th>
                                    <th>% SNP</th>
                                    <th>Apor. obli. AFP</th>
                                    <th>Inval. y sobr.</th>
                                    <th>Comisión AFP</th>
                                    <th>Eliminar</th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr>

                              </tr>
                            </tbody>
                          </table>

                        </div>
                    
                         </div>   
                          </form>
                        </div>

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
    <script type="text/javascript" src="scripts/tipoSeguro.js"></script>
    <?php
}
ob_end_flush();
?>