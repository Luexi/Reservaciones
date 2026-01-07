<?php
// reservaciones/api/update_table_positions.php
header('Content-Type: application/json');
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $positions = $data['positions'] ?? [];

    if (empty($positions)) {
        echo json_encode(['success' => false, 'error' => 'No positions provided']);
        exit;
    }

    $database = new Database();
    $db = $database->getConnection();

    try {
        $db->beginTransaction();

        $stmt = $db->prepare("UPDATE mesas SET posicion_x = :x, posicion_y = :y, updated_at = NOW() WHERE id = :id");

        foreach ($positions as $pos) {
            $stmt->bindParam(':id', $pos['id']);
            $stmt->bindParam(':x', $pos['x']);
            $stmt->bindParam(':y', $pos['y']);
            $stmt->execute();
        }

        $db->commit();

        echo json_encode(['success' => true, 'message' => 'Positions updated']);

    } catch (PDOException $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
}
?>