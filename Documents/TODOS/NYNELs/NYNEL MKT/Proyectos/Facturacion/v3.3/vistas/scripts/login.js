// JavaScript para login - v3.3
// CÓDIGO AJAX COMENTADO - Ahora usa POST tradicional
/*
$(function() {
  console.log("Login.js cargado correctamente");

  // Manejador del botón de login
  $("#btnIngresar").on("click", function(e) {
    e.preventDefault();
    console.log("=== Click en botón de login ===");

    var btnIngresar = $("#btnIngresar");
    var logina = $("#logina").val();
    var clavea = $("#clavea").val();
    var empresa = $("#empresa").val();
    var st = $("#estadot").val() || "0";

    console.log("Datos del formulario:", {logina, empresa, st});

    if (!logina || !clavea) {
      Swal.fire({
        icon: "warning",
        title: "Campos vacíos",
        text: "Por favor ingresa usuario y contraseña",
        showConfirmButton: true
      });
      return false;
    }

    btnIngresar.prop("disabled", true).html("Validando datos...");

    $.ajax({
      url: "../ajax/usuario.php?op=verificar",
      type: "POST",
      data: { "logina": logina, "clavea": clavea, "empresa": empresa, "st": st },
      xhrFields: {
        withCredentials: true
      },
      success: function(data) {
        console.log("Respuesta recibida:", data);
        console.log("Tipo de data:", typeof data);

        // Parsear si viene como string
        var resultado = (typeof data === 'string') ? JSON.parse(data) : data;

        console.log("Resultado parseado:", resultado);

        if (resultado && resultado.idusuario) {
          console.log("Login exitoso, redirigiendo a escritorio...");
          window.location.href = "escritorio";
        } else {
          console.log("Login fallido - respuesta null o sin idusuario");
          Swal.fire({
            icon: "error",
            title: "Error",
            text: "Usuario y/o contraseña incorrectos",
            showConfirmButton: true
          }).then(function() {
            btnIngresar.prop("disabled", false).html("Ingresar");
            $("#logina").focus();
          });
        }
      },
      error: function(xhr, status, error) {
        console.error("Error en la petición AJAX:", {xhr, status, error});
        Swal.fire({
          icon: "error",
          title: "Error de conexión",
          text: "No se pudo conectar con el servidor: " + error,
          showConfirmButton: true
        });
        btnIngresar.prop("disabled", false).html("Ingresar");
      }
    });

    return false;
  });

  // Focus automático en el campo de usuario
  $("#logina").focus();

  console.log("Handler de click registrado correctamente");
});
*/

// El login ahora funciona con POST tradicional del formulario HTML
// No se necesita JavaScript para manejar el submit
$(function() {
  console.log("Login listo - usando POST tradicional");
  $("#logina").focus();
});
