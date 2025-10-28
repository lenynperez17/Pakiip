
<?php
require_once('../config/global.php');
$errors = array();
if(isset($_POST['submit']))
{
    $db=mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    // Check connection
    if (mysqli_connect_errno())
    {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    // receive all input values from the form
	$clave = mysqli_real_escape_string($db, $_POST['clave']);
    if (empty($clave))
	{
		array_push($errors, '<div class="alert alert-danger" role="alert">Contraseña es requerido</div>');
	}
    $query = mysqli_query($db,"SELECT * FROM usuario WHERE `usuario`.`idusuario` = 1")
    or die(mysqli_error($db)); 

    if (mysqli_num_rows ($query)==1)
    {
        $clave = hash('sha256', $clave);
        $query3 = mysqli_query($db,"UPDATE usuario SET `clave`='$clave' WHERE `usuario`.`idusuario` = 1")
        or die(mysqli_error($db));

        echo  '<div class="text-center"><div class="alert alert-success" role="alert">Contraseña actualizada correctamente </div> </div>';
    }
}

?>
