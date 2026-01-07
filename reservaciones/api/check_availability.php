<?php
// reservaciones/api/check_availability.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');

require_once '../config/database.php';

function checkAvailability($fecha, $hora, $num_personas)
{
    $database = new Database();
    $db = $database->getConnection();

    try {
        // Call the PostgreSQL function
        $query = "SELECT * FROM verificar_disponibilidad(:fecha, :hora, :num_personas)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':hora', $hora);
        $stmt->bindParam(':num_personas', $num_personas, PDO::PARAM_INT);
        $stmt->execute();

        $available_tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'available' => count($available_tables) > 0,
            'tables_count' => count($available_tables),
            'tables' => $available_tables
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Handle request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['fecha']) || !isset($data['hora']) || !isset($data['num_personas'])) {
        echo json_encode([
            'success' => false,
            'error' => 'Faltan parámetros requeridos'
        ]);
        exit;
    }

    $result = checkAvailability($data['fecha'], $data['hora'], $data['num_personas']);
    echo json_encode($result);
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Support GET for simple checks
    if (!isset($_GET['fecha']) || !isset($_GET['hora']) || !isset($_GET['num_personas'])) {
        echo json_encode([
            'success' => false,
            'error' => 'Faltan parámetros requeridos'
        ]);
        exit;
    }

    $result = checkAvailability($_GET['fecha'], $_GET['hora'], $_GET['num_personas']);
    echo json_encode($result);
}
?>