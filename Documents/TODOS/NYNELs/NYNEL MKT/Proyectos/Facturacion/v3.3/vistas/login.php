<?php
require_once '../config/global.php';
require_once '../config/Conexion.php';
require_once '../config/security_headers.php';  // Headers de seguridad HTTP
?>
<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="UTF-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />

	<!-- SEO meta tags -->
	<title>Demo Sistema de Facturación Electrónica y Gestión de Inventario</title>
	<meta name="description"
		content="Demo de un sistema robusto de facturación electrónica y gestión de inventario. Mejora la eficiencia de tu negocio con nuestra solución." />
	<meta name="keywords" content="facturación electrónica, gestión de inventario, sistema de facturación, demo">
	<meta name="author" content="SisFact">

	<!-- Open Graph (para redes sociales como Facebook) -->
	<meta property="og:title" content="Demo Sistema de Facturación Electrónica y Gestión de Inventario" />
	<meta property="og:description"
		content="Demo de un sistema robusto de facturación electrónica y gestión de inventario. Mejora la eficiencia de tu negocio con nuestra solución." />
	<meta property="og:image" content="https://facturacion.licoreralaoficina.com/seo/promocion.jpg" />
	<meta property="og:url" content="https://fact.licoreralaoficina.com/vistas/login" />

	<!-- Twitter Card -->
	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:title" content="Demo Sistema de Facturación Electrónica y Gestión de Inventario">
	<meta name="twitter:description"
		content="Demo de un sistema robusto de facturación electrónica y gestión de inventario. Mejora la eficiencia de tu negocio con nuestra solución.">
	<meta name="twitter:image" content="https://facturacion.licoreralaoficina.com/seo/promocion.jpg">

	<!-- JSON-LD para datos estructurados -->
	<script type="application/ld+json">
	{
	  "@context": "http://schema.org",
	  "@type": "SoftwareApplication",
	  "name": "Sistema de Facturación Electrónica y Gestión de Inventario",
	  "description": "Demo de un sistema robusto de facturación electrónica y gestión de inventario.",
	  "applicationCategory": "BusinessApplication",
	  "operatingSystem": "Web",
	  "screenshot": "https://facturacion.licoreralaoficina.com/seo/promocion.jpg",
	  "offers": {
		"@type": "Offer",
		"price": "450.00",
		"priceCurrency": "PEN",
		"availability": "http://schema.org/InStock",
		"url": "https://fact.licoreralaoficina.com/vistas/login"
	  }
	}
	</script>

	<link rel="stylesheet" href="../custom/css/login.css" />
	<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
</head>

<body>
	<div class="wrapper">
		<div class="form-box login">
			<h2 class="animation" style="--i:0;">Bienvenido</h2>
			<p class="subtitle animation" style="--i:1;">Ingresa tus credenciales para continuar</p>
			<form id="frmAcceso" name="frmAcceso" method="POST" action="../ajax/usuario.php?op=verificar">
				<div class="input-box animation" style="--i:2;">
					<input value="" id="logina" name="logina" type="text" required />
					<label>Usuario</label>
					<i class="bx bxs-user"></i>
				</div>
				<div class="input-box animation" style="--i:3;">
					<input value="" id="clavea" name="clavea" type="password" required />
					<label>Contraseña</label>
					<i class="bx bxs-lock-alt"></i>
				</div>
				<input type="hidden" name="empresa" id="empresa" value="1" />
				<input type="hidden" name="st" id="estadot" value="0" />
				<input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo obtenerTokenCSRF(); ?>" />
				<button id="btnIngresar" type="submit" class="btn animation" style="--i:4;">Ingresar</button>
				<div class="logreg-link animation" style="--i:5;">
					<!-- <p>¿Olvidaste tu contraseña? <a href="#" class="register-link">Recuperar</a></p> -->
				</div>
			</form>
		</div>

		<div class="info-text login">
			<div class="logo animation" style="--i:0;">
				<i class="bx bx-receipt"></i>
			</div>
			<h2 class="animation" style="--i:1;">SisFact</h2>
			<p class="animation" style="--i:2;">Sistema integral de facturación electrónica y gestión empresarial</p>
			<div class="features animation" style="--i:3;">
				<div class="feature">
					<i class="bx bx-check-circle"></i>
					<span>Facturación Electrónica SUNAT</span>
				</div>
				<div class="feature">
					<i class="bx bx-check-circle"></i>
					<span>Control de Inventario</span>
				</div>
				<div class="feature">
					<i class="bx bx-check-circle"></i>
					<span>Reportes en Tiempo Real</span>
				</div>
			</div>
		</div>

		<div class="form-box register">
			<h2 class="animation" style="--i:17; --j:0;">Recuperar Contraseña</h2>
			<form action="">
				<div class="input-box animation" style="--i:18; --j:1;">
					<input type="text" required />
					<label>Correo Electrónico</label>
					<i class="bx bxs-user"></i>
				</div>
				<div hidden class="input-box animation" style="--i:19; --j:2;">
					<input type="password" required />
					<label>Password</label>
					<i class="bx bxs-lock-alt"></i>
				</div>
				<button type="submit" class="btn animation" style="--i:20; --j:3;">Cambiar</button>
				<div class="logreg-link animation" style="--i:21; --j:4;">
					<p>
						Ya tienes cuenta? <a href="#" class="login-link">Clic aqui</a>
					</p>
				</div>
			</form>
		</div>

		<div class="info-text register">
			<h2 class="animation" style="--i:17; --j:0;">SisFact</h2>
			<p class="animation" style="--i:18; --j:1;">Facturación Electrónica y<br>Gestión Empresarial</p>
		</div>
	</div>

	<script src="../public/js/jquery-3.1.1.min.js"></script>
	<script src="../custom/js/evento.js"></script>
	<script type="text/javascript" src="scripts/login.js?v=<?php echo time(); ?>"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

	<?php
	// Manejar errores de login
	if (isset($_GET['error'])) {
		$error = $_GET['error'];

		if ($error === 'rate_limit') {
			$tiempo = isset($_GET['tiempo']) ? intval($_GET['tiempo']) : 15;
			echo "<script>
				Swal.fire({
					icon: 'error',
					title: '¡Demasiados intentos fallidos!',
					html: 'Has excedido el límite de intentos de inicio de sesión.<br><br>' +
						  '<strong>Tiempo de espera: $tiempo minutos</strong><br><br>' +
						  'Por seguridad, tu IP ha sido bloqueada temporalmente.<br>' +
						  'Por favor, intenta nuevamente más tarde.',
					showConfirmButton: true,
					confirmButtonText: 'Entendido',
					allowOutsideClick: false
				});
			</script>";
		} elseif ($error === 'csrf') {
			echo "<script>
				Swal.fire({
					icon: 'error',
					title: 'Error de Seguridad',
					text: 'Token de seguridad inválido. Por favor, recarga la página e intenta nuevamente.',
					showConfirmButton: true
				});
			</script>";
		} elseif ($error === '1') {
			echo "<script>
				Swal.fire({
					icon: 'error',
					title: 'Error de Autenticación',
					text: 'Usuario y/o contraseña incorrectos',
					showConfirmButton: true
				});
			</script>";
		}
	}
	?>
</body>

</html>