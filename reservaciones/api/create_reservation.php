<?php
// reservaciones/api/create_reservation.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once '../config/database.php';

function createReservation($data)
{
    $database = new Database();
    $db = $database->getConnection();

    try {
        // First, check availability
        $checkQuery = "SELECT * FROM verificar_disponibilidad(:fecha, :hora, :num_personas) LIMIT 1";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':fecha', $data['fecha']);
        $checkStmt->bindParam(':hora', $data['hora']);
        $checkStmt->bindParam(':num_personas', $data['num_personas'], PDO::PARAM_INT);
        $checkStmt->execute();

        $available_table = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$available_table) {
            return [
                'success' => false,
                'error' => 'No hay mesas disponibles para la fecha y hora seleccionadas'
            ];
        }

        // Create reservation
        $query = "INSERT INTO reservaciones 
                  (restaurante_id, mesa_id, nombre_cliente, telefono, email, num_personas, 
                   fecha, hora, duracion_minutos, ocasion_especial, comentarios, estado, origen)
                  VALUES 
                  (:restaurante_id, :mesa_id, :nombre, :telefono, :email, :num_personas,
                   :fecha, :hora, :duracion, :ocasion, :comentarios, 'pendiente', :origen)
                  RETURNING id, created_at";

        $stmt = $db->prepare($query);

        // Default restaurant ID (you should set this based on your setup)
        $restaurante_id = $data['restaurante_id'] ?? null;
        $duracion = $data['duracion_minutos'] ?? 120;
        $origen = $data['origen'] ?? 'web';

        $stmt->bindParam(':restaurante_id', $restaurante_id);
        $stmt->bindParam(':mesa_id', $available_table['mesa_id']);
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':telefono', $data['telefono']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':num_personas', $data['num_personas'], PDO::PARAM_INT);
        $stmt->bindParam(':fecha', $data['fecha']);
        $stmt->bindParam(':hora', $data['hora']);
        $stmt->bindParam(':duracion', $duracion, PDO::PARAM_INT);
        $stmt->bindParam(':ocasion', $data['ocasion_especial']);
        $stmt->bindParam(':comentarios', $data['comentarios']);
        $stmt->bindParam(':origen', $origen);

        $stmt->execute();
        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

        // Update or create client record
        updateClientRecord($db, $data['telefono'], $data['nombre'], $data['email']);

        return [
            'success' => true,
            'reservation_id' => $reservation['id'],
            'mesa_asignada' => $available_table['mesa_id'],
            'created_at' => $reservation['created_at'],
            'mensaje' => '¡Reservación creada exitosamente!'
        ];

    } catch (PDOException $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

function updateClientRecord($db, $telefono, $nombre, $email)
{
    try {
        $query = "INSERT INTO clientes (telefono, nombre, email, total_reservas)
                  VALUES (:telefono, :nombre, :email, 1)
                  ON CONFLICT (telefono) 
                  DO UPDATE SET 
                    total_reservas = clientes.total_reservas + 1,
                    nombre = COALESCE(:nombre, clientes.nombre),
                    email = COALESCE(:email, clientes.email)";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
    } catch (PDOException $e) {
        // Log error but don't fail reservation
        error_log("Client update error: " . $e->getMessage());
    }
}

// Handle request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $required = ['nombre', 'telefono', 'num_personas', 'fecha', 'hora'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            echo json_encode([
                'success' => false,
                'error' => "Campo requerido faltante: {$field}"
            ]);
            exit;
        }
    }

    $result = createReservation($data);
    echo json_encode($result);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Método no permitido'
    ]);
}
?>