<?php
// reservaciones/api/get_reservations.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

function getReservations($filters = [])
{
    $database = new Database();
    $db = $database->getConnection();

    try {
        $query = "SELECT r.*, m.numero_mesa, m.capacidad_max
                  FROM reservaciones r
                  LEFT JOIN mesas m ON r.mesa_id = m.id
                  WHERE 1=1";

        $params = [];

        if (isset($filters['fecha'])) {
            $query .= " AND r.fecha = :fecha";
            $params[':fecha'] = $filters['fecha'];
        }

        if (isset($filters['estado'])) {
            $query .= " AND r.estado = :estado";
            $params[':estado'] = $filters['estado'];
        }

        if (isset($filters['fecha_inicio']) && isset($filters['fecha_fin'])) {
            $query .= " AND r.fecha BETWEEN :fecha_inicio AND :fecha_fin";
            $params[':fecha_inicio'] = $filters['fecha_inicio'];
            $params[':fecha_fin'] = $filters['fecha_fin'];
        }

        $query .= " ORDER BY r.fecha DESC, r.hora DESC";

        if (isset($filters['limit'])) {
            $query .= " LIMIT :limit";
        }

        $stmt = $db->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        if (isset($filters['limit'])) {
            $stmt->bindValue(':limit', $filters['limit'], PDO::PARAM_INT);
        }

        $stmt->execute();
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'count' => count($reservations),
            'reservations' => $reservations
        ];

    } catch (PDOException $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Handle request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $filters = [];

    if (isset($_GET['fecha']))
        $filters['fecha'] = $_GET['fecha'];
    if (isset($_GET['estado']))
        $filters['estado'] = $_GET['estado'];
    if (isset($_GET['fecha_inicio']))
        $filters['fecha_inicio'] = $_GET['fecha_inicio'];
    if (isset($_GET['fecha_fin']))
        $filters['fecha_fin'] = $_GET['fecha_fin'];
    if (isset($_GET['limit']))
        $filters['limit'] = (int) $_GET['limit'];

    $result = getReservations($filters);
    echo json_encode($result);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Método no permitido'
    ]);
}
?>