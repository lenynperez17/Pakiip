<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verificar Sesión - Debug NC/ND</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
        }
        .section {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-left: 4px solid #007bff;
        }
        .success {
            color: #28a745;
            font-weight: bold;
        }
        .error {
            color: #dc3545;
            font-weight: bold;
        }
        .warning {
            color: #ffc107;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #007bff;
            color: white;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-success { background: #28a745; color: white; }
        .badge-error { background: #dc3545; color: white; }
        .badge-warning { background: #ffc107; color: black; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Verificación de Sesión - Sistema NC/ND</h1>

        <?php
        // Cargar Conexion.php para inicializar sesión
        require_once "../config/Conexion.php";

        echo "<div class='section'>";
        echo "<h2>1. Estado de la Sesión</h2>";

        if (session_status() === PHP_SESSION_ACTIVE) {
            echo "<p><span class='badge badge-success'>✓</span> Sesión PHP ACTIVA</p>";
            echo "<p>Session ID: <code>" . session_id() . "</code></p>";
        } else {
            echo "<p><span class='badge badge-error'>✗</span> Sesión PHP NO ACTIVA</p>";
        }
        echo "</div>";

        echo "<div class='section'>";
        echo "<h2>2. Variables de Sesión Críticas</h2>";
        echo "<table>";
        echo "<tr><th>Variable</th><th>Estado</th><th>Valor</th></tr>";

        $critical_vars = ['idusuario', 'idempresa', 'nombre', 'login'];
        foreach ($critical_vars as $var) {
            echo "<tr>";
            echo "<td><strong>\$_SESSION['$var']</strong></td>";

            if (isset($_SESSION[$var]) && !empty($_SESSION[$var])) {
                echo "<td><span class='badge badge-success'>DEFINIDA</span></td>";
                echo "<td>" . htmlspecialchars($_SESSION[$var]) . "</td>";
            } else {
                echo "<td><span class='badge badge-error'>NO DEFINIDA</span></td>";
                echo "<td>-</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";

        echo "<div class='section'>";
        echo "<h2>3. Diagnóstico del Problema NC/ND</h2>";

        $problemas = [];
        $advertencias = [];

        // Verificar si está logueado
        if (!isset($_SESSION['idusuario']) || empty($_SESSION['idusuario'])) {
            $problemas[] = "❌ No hay usuario logueado (\$_SESSION['idusuario'] no está definida)";
        }

        // Verificar idempresa (CRÍTICO para NC/ND)
        if (!isset($_SESSION['idempresa']) || empty($_SESSION['idempresa'])) {
            $problemas[] = "❌ CRÍTICO: \$_SESSION['idempresa'] NO ESTÁ DEFINIDA - esto causa el error 500";
            $problemas[] = "→ Los endpoints NC/ND necesitan esta variable para filtrar comprobantes por empresa";
        } else {
            echo "<p class='success'>✓ \$_SESSION['idempresa'] está definida correctamente: " . $_SESSION['idempresa'] . "</p>";
        }

        // Mostrar problemas
        if (!empty($problemas)) {
            echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #dc3545; margin: 10px 0;'>";
            echo "<h3 style='color: #dc3545; margin-top: 0;'>⚠️ PROBLEMAS ENCONTRADOS:</h3>";
            foreach ($problemas as $problema) {
                echo "<p>" . $problema . "</p>";
            }
            echo "</div>";
        }

        // Mostrar advertencias
        if (!empty($advertencias)) {
            echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 10px 0;'>";
            echo "<h3 style='color: #856404; margin-top: 0;'>⚠️ ADVERTENCIAS:</h3>";
            foreach ($advertencias as $advertencia) {
                echo "<p>" . $advertencia . "</p>";
            }
            echo "</div>";
        }

        // Si todo está bien
        if (empty($problemas) && empty($advertencias)) {
            echo "<div style='background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 10px 0;'>";
            echo "<h3 style='color: #155724; margin-top: 0;'>✅ TODO ESTÁ CORRECTO</h3>";
            echo "<p>Tu sesión está configurada correctamente para usar NC/ND.</p>";
            echo "</div>";
        }

        echo "</div>";

        echo "<div class='section'>";
        echo "<h2>4. Todas las Variables de Sesión</h2>";
        echo "<table>";
        echo "<tr><th>Clave</th><th>Valor</th></tr>";

        if (!empty($_SESSION)) {
            foreach ($_SESSION as $key => $value) {
                echo "<tr>";
                echo "<td><strong>" . htmlspecialchars($key) . "</strong></td>";
                echo "<td>" . htmlspecialchars(is_array($value) ? json_encode($value) : $value) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='2' class='error'>No hay variables de sesión</td></tr>";
        }

        echo "</table>";
        echo "</div>";

        echo "<div class='section'>";
        echo "<h2>5. Solución Recomendada</h2>";

        if (!isset($_SESSION['idempresa'])) {
            echo "<div style='background: #e7f3ff; padding: 15px; border-left: 4px solid #007bff;'>";
            echo "<h3 style='color: #004085;'>🔧 PASOS PARA SOLUCIONAR:</h3>";
            echo "<ol>";
            echo "<li>Cierra sesión completamente</li>";
            echo "<li>Vuelve a iniciar sesión en el sistema</li>";
            echo "<li>Verifica que selecciones una empresa al entrar</li>";
            echo "<li>Recarga esta página para verificar que \$_SESSION['idempresa'] esté definida</li>";
            echo "<li>Intenta usar NC/ND nuevamente</li>";
            echo "</ol>";
            echo "</div>";
        } else {
            echo "<div style='background: #d4edda; padding: 15px; border-left: 4px solid #28a745;'>";
            echo "<h3 style='color: #155724;'>✅ Sesión OK</h3>";
            echo "<p>Tu sesión está lista. Los endpoints NC/ND deberían funcionar correctamente.</p>";
            echo "<p><strong>Si aún ves el error 500:</strong></p>";
            echo "<ul>";
            echo "<li>Revisa la consola del navegador para ver el mensaje de error específico</li>";
            echo "<li>Verifica que las tablas 'factura' y 'boleta' existan en la base de datos</li>";
            echo "<li>Verifica que tengas comprobantes (facturas o boletas) registrados</li>";
            echo "</ul>";
            echo "</div>";
        }

        echo "</div>";

        echo "<div class='section'>";
        echo "<h2>6. Test de Consulta NC (Opcional)</h2>";

        if (isset($_SESSION['idempresa']) && !empty($_SESSION['idempresa'])) {
            echo "<p>Ejecutando consulta de prueba...</p>";

            try {
                $sql_test = "SELECT COUNT(*) as total
                            FROM factura
                            WHERE estado != 'Anulado'
                              AND idempresa = '{$_SESSION['idempresa']}'";

                $resultado = ejecutarConsulta($sql_test);
                $row = $resultado->fetch_object();

                echo "<p class='success'>✓ Consulta ejecutada exitosamente</p>";
                echo "<p>Facturas disponibles para NC: <strong>" . $row->total . "</strong></p>";

                if ($row->total == 0) {
                    echo "<p class='warning'>⚠️ No tienes facturas registradas. Agrega comprobantes para poder crear NC/ND.</p>";
                }

            } catch (Exception $e) {
                echo "<p class='error'>✗ Error al ejecutar consulta: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        } else {
            echo "<p class='warning'>⚠️ No se puede ejecutar test sin \$_SESSION['idempresa']</p>";
        }

        echo "</div>";
        ?>

        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 2px solid #ddd;">
            <a href="../vistas/pos.php" style="display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;">
                ← Volver al POS
            </a>
            <button onclick="location.reload()" style="display: inline-block; padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">
                🔄 Recargar Verificación
            </button>
        </div>
    </div>
</body>
</html>
