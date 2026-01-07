<?php
// reservaciones/api/webhook_whatsapp.php
header('Content-Type: application/json');

// This webhook receives events from WPPConnect server
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Log for debugging
file_put_contents(__DIR__ . '/../logs/whatsapp_webhook.log', date('Y-m-d H:i:s') . ': ' . $input . "\n", FILE_APPEND);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

// Extract message data
$from = $data['from'] ?? '';
$message = $data['body'] ?? '';
$messageId = $data['id'] ?? '';

// Process message (simplified - in production use proper state management)
$response = processWhatsAppMessage($from, $message);

// Send response via WPPConnect API
sendWhatsAppMessage($from, $response);

echo json_encode(['success' => true]);

function processWhatsAppMessage($from, $message)
{
    $message = strtolower(trim($message));

    // Simple keyword-based responses
    if (strpos($message, 'hola') !== false || strpos($message, 'ayuda') !== false) {
        return "¡Hola! Bienvenido a Rosa Mezcal 🍹\n\n" .
            "Para hacer una reservación, escribe: *reservar*\n" .
            "Para ver horarios, escribe: *horarios*\n" .
            "Para hablar con un agente: *agente*";
    }

    if (strpos($message, 'horario') !== false) {
        return "📅 *Horarios de Rosa Mezcal*\n\n" .
            "Lunes-Jueves: 18:00 - 23:00\n" .
            "Viernes-Domingo: 18:00 - 01:00\n\n" .
            "Escribe *reservar* para hacer tu reservación.";
    }

    if (strpos($message, 'reservar') !== false || strpos($message, 'reserva') !== false) {
        return "✅ Para hacer tu reservación, necesito:\n\n" .
            "1️⃣ Número de personas\n" .
            "2️⃣ Fecha (DD/MM/YYYY)\n" .
            "3️⃣ Hora (HH:MM)\n" .
            "4️⃣ Tu nombre\n\n" .
            "O llámanos al: 📞 +52 XXX XXX XXXX";
    }

    return "No entendí tu mensaje. Escribe *hola* para ver las opciones disponibles.";
}

function sendWhatsAppMessage($to, $message)
{
    // WPPConnect API endpoint (running on port 21465)
    $url = 'http://wppconnect:21465/api/rosa_mezcal_session/send-message';

    $payload = [
        'phone' => $to,
        'message' => $message,
        'isGroup' => false
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $httpCode === 200;
}
?>