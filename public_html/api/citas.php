<?php
/**
 * Backend - Peluditos y Amorositos (Producción / Hostinger)
 * -----------------------------------------------------------
 * Controlador API REST desarrollado como trabajo freelance a medida.
 * Escrito en PHP sin frameworks para máxima ligereza e integración.
 * Incluye validación de server side, mitigación XSS y DB local.
 */

// Definición de variables de entorno (Datos estáticos y rutas relativas)
$archivo = __DIR__ . '/citas.json';
$email_veterinario = 'administrador1@peluditosyamorositos.com'; // CAMBIA SI ES NECESARIO

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// --- Utilidad: Lectura de BD JSON ---
function leerCitas() {
    global $archivo;
    if (file_exists($archivo)) {
        return json_decode(file_get_contents($archivo), true);
    }
    return [];
}

// --- Utilidad: Escritura en BD JSON ---
function guardarCitas($citas) {
    global $archivo;
    file_put_contents($archivo, json_encode($citas, JSON_PRETTY_PRINT));
}

// --- Endpoint GET: Consulta de horarios (Filtro por mes/día) ---
if ($method === 'GET') {
    $fecha = $_GET['fecha'] ?? null;
    $mes = $_GET['mes'] ?? null;

    // Si se pide un mes completo
    if ($mes && preg_match('/^\d{4}-\d{2}$/', $mes)) {
        $citas = leerCitas();
        $inicio = $mes . '-01';
        $fin = date('Y-m-t', strtotime($inicio));
        $ocupadosPorFecha = [];

        foreach ($citas as $c) {
            if ($c['fecha'] >= $inicio && $c['fecha'] <= $fin) {
                if (!isset($ocupadosPorFecha[$c['fecha']])) {
                    $ocupadosPorFecha[$c['fecha']] = [];
                }
                $ocupadosPorFecha[$c['fecha']][] = $c['hora'];
            }
        }

        echo json_encode($ocupadosPorFecha);
        exit;
    }

    // Si se pide una fecha concreta
    if ($fecha && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        $citas = leerCitas();
        $horariosOcupados = [];
        foreach ($citas as $c) {
            if ($c['fecha'] === $fecha) {
                $horariosOcupados[] = $c['hora'];
            }
        }
        echo json_encode($horariosOcupados);
        exit;
    }

    // Si no hay parámetros válidos, devolver todas las citas
    echo json_encode(leerCitas());
    exit;
}

// --- Endpoint POST: Creación de nueva cita ---
if ($method === 'POST') {
    // Leemos el "payload" raw de la petición HTTP y deserializamos el JSON a Array Asociativo en PHP
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400); // Código HTTP 400: Bad Request
        echo json_encode(['error' => 'La carga útil (payload) no es un JSON válido']);
        exit;
    }

    // Validación de integridad: Requerimos campos mínimos para asegurar consistencia
    $required = ['servicio', 'fecha', 'hora', 'propietario', 'telefono', 'email', 'mascota'];
    foreach ($required as $campo) {
        // Validación del lado del servidor (Nunca confiar solo en el Frontend)
        if (empty($input[$campo])) {
            http_response_code(400);
            echo json_encode(['error' => "Restricción de integridad: Falta el campo $campo"]);
            exit; // Interrumpimos la ejecución para no procesar data incompleta
        }
    }

    // Seguridad de Software: Sanitización estricta de variables de input
    // Mitigación de ataques por Inyección (ej. Cross-Site Scripting - XSS)
    $inputLimpio = [];
    foreach ($input as $key => $val) {
        // Ignorar claves que no nos interesan
        if (!in_array($key, ['servicio', 'fecha', 'hora', 'propietario', 'telefono', 'email', 'mascota', 'especie', 'raza', 'notas'])) {
            continue;
        }
        $inputLimpio[$key] = htmlspecialchars(strip_tags((string)$val), ENT_QUOTES, 'UTF-8');
    }

    // Validación estricta de estructura de correo
    if (!filter_var($inputLimpio['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Formato de correo electrónico inválido']);
        exit;
    }

    // Leer citas existentes
    $citas = leerCitas();

    // Verificar si ya existe una cita con la misma fecha y hora
    foreach ($citas as $c) {
        if ($c['fecha'] === $inputLimpio['fecha'] && $c['hora'] === $inputLimpio['hora']) {
            http_response_code(409);
            echo json_encode(['error' => 'Horario no disponible, ya hay una cita agendada']);
            exit;
        }
    }

    // Agregar nueva cita
    $inputLimpio['fechaRegistro'] = date('Y-m-d H:i:s');
    $citas[] = $inputLimpio;
    guardarCitas($citas);

    // Reasignamos a $input para que el sistema de plantillas debajo lo use de forma segura y transparente
    $input = $inputLimpio;

    // --- Envío de correos ---
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Peluditos y Amorositos <noreply@peluditosyamorositos.com>\r\n";

    // --- Plantilla de Correo HTML: Confirmación Cliente ---
    $asunto_cliente = "🐾 ¡Tu cita en Peluditos y Amorositos ha sido confirmada!";

    $mensaje_cliente = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Confirmación de cita</title>
    </head>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #2C2C2C; background-color: #F9F4EC; margin: 0; padding: 20px;'>
        <div style='max-width: 600px; margin: 0 auto; background-color: #FFFFFF; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1);'>
            <div style='background: linear-gradient(135deg, #4A7550, #2A5530); padding: 30px; text-align: center; color: #fff;'>
                <!-- LOGO: reemplaza la URL por la correcta -->
                <img src='https://peluditosyamorositos.com/images/Logo.png' alt='Peluditos y Amorositos' style='width: 80px; margin-bottom: 10px;'>
                <h1 style='margin: 0; font-size: 28px;'>🐾 Peluditos y Amorositos</h1>
                <p style='margin: 5px 0 0; opacity: 0.9;'>Tienda & Clínica Veterinaria</p>
            </div>
            <div style='padding: 30px;'>
                <h2 style='color: #4A7550; margin-top: 0;'>¡Hola, {$input['propietario']}! 🎉</h2>
                <p>Tu cita ha sido <strong>confirmada exitosamente</strong>. Estaremos esperándote con mucho gusto.</p>

                <div style='background-color: #F9F4EC; padding: 15px; border-radius: 16px; margin: 20px 0;'>
                    <h3 style='color: #4A7550; margin-top: 0;'>📋 Detalles de la cita</h3>
                    <p><strong>🐶 Mascota:</strong> {$input['mascota']}</p>
                    <p><strong>🩺 Servicio:</strong> {$input['servicio']}</p>
                    <p><strong>📅 Fecha:</strong> {$input['fecha']}</p>
                    <p><strong>⏰ Hora:</strong> {$input['hora']}</p>
                </div>

                <div style='background-color: #F9F4EC; padding: 15px; border-radius: 16px; margin: 20px 0;'>
                    <h3 style='color: #4A7550; margin-top: 0;'>📍 ¿Dónde estamos?</h3>
                    <p><strong>Dirección:</strong> Calle 36 27-75, Barrio Cervantes</p>
                    <p><strong>📞 Teléfono:</strong> (+57) 311 2005506</p>
                    <p><strong>🕒 Horarios:</strong> Lunes a sábado 8:30am – 12:30pm y 2:00pm – 7:30pm<br>
                    Domingos y festivos 8:30am – 11:30am</p>
                </div>

                <p style='font-size: 14px; color: #6B6B6B; text-align: center; border-top: 1px solid #E8E0D4; padding-top: 20px; margin-top: 30px;'>
                    💙 Si necesitas cambiar o cancelar tu cita, contáctanos con al menos 24 horas de anticipación.<br>
                    ¡Gracias por confiar en nosotros para cuidar de tu peludito!
                </p>
                <p style='text-align: center; margin-bottom: 0;'>🐕🐈 Con amor,<br><strong>Peluditos y Amorositos</strong></p>
            </div>
        </div>
    </body>
    </html>
    ";

    // --- Plantilla de Correo HTML: Resumen Administrador ---
    $asunto_veterinario = "📋 Nueva cita agendada - Peluditos y Amorositos";
    $mensaje_veterinario = "
    <html>
    <head><title>Nueva cita</title></head>
    <body style='font-family: Arial, sans-serif;'>
        <h2>Nueva cita registrada</h2>
        <p><b>Propietario:</b> {$input['propietario']}</p>
        <p><b>Teléfono:</b> {$input['telefono']}</p>
        <p><b>Email:</b> {$input['email']}</p>
        <p><b>Mascota:</b> {$input['mascota']}</p>
        <p><b>Servicio:</b> {$input['servicio']}</p>
        <p><b>Fecha:</b> {$input['fecha']}</p>
        <p><b>Hora:</b> {$input['hora']}</p>
        <p><b>Notas:</b> " . ($input['notas'] ?? 'Ninguna') . "</p>
        <hr>
        <p>Puedes ver todas las citas en el archivo <code>citas.json</code> de tu servidor.</p>
    </body>
    </html>
    ";

    // Enviar correos
    mail($input['email'], $asunto_cliente, $mensaje_cliente, $headers);
    mail($email_veterinario, $asunto_veterinario, $mensaje_veterinario, $headers);

    http_response_code(200);
    echo json_encode(['mensaje' => 'Cita guardada correctamente']);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Método no permitido']);