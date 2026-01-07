<?php
// reservaciones/admin/dashboard.php
require_once 'auth_check.php';
require_once '../config/supabase.php';

// Get today's stats
$supabase = new SupabaseClient();
$today = date('Y-m-d');

try {
    // Get all today's reservations
    $todayReservations = $supabase->get('reservaciones', [
        'fecha' => 'eq.' . $today,
        'select' => 'id,num_personas,estado'
    ]);

    // Calculate stats
    $stats = [
        'total' => count($todayReservations),
        'personas' => array_sum(array_column($todayReservations, 'num_personas')),
        'pendientes' => count(array_filter($todayReservations, function ($r) {
            return $r['estado'] === 'pendiente';
        }))
    ];

    // Get upcoming reservations (next 2 hours)
    $currentTime = date('H:i:s');
    $allUpcoming = $supabase->get('reservaciones', [
        'fecha' => 'eq.' . $today,
        'hora' => 'gte.' . $currentTime,
        'estado' => 'eq.confirmada',
        'select' => '*',
        'order' => 'hora.asc',
        'limit' => 5
    ]);

    // Get mesa numbers for upcoming reservations
    $upcoming = [];
    foreach ($allUpcoming as $reservation) {
        if ($reservation['mesa_id']) {
            $mesa = $supabase->get('mesas', [
                'id' => 'eq.' . $reservation['mesa_id'],
                'select' => 'numero_mesa',
                'limit' => 1
            ]);
            $reservation['numero_mesa'] = $mesa[0]['numero_mesa'] ?? null;
        } else {
            $reservation['numero_mesa'] = null;
        }
        $upcoming[] = $reservation;
    }

} catch (Exception $e) {
    $error = $e->getMessage();
    $stats = ['total' => 0, 'personas' => 0, 'pendientes' => 0];
    $upcoming = [];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Rosa Mezcal Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/variables.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>
    <nav class="admin-nav">
        <div class="nav-brand">🍹 Rosa Mezcal Admin</div>
        <div class="nav-links">
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="reservations.php">Reservaciones</a>
            <a href="tables.php">Mesas</a>
            <a href="config.php">Configuración</a>
            <a href="logout.php" class="logout-btn">Cerrar Sesión</a>
        </div>
    </nav>

    <main class="admin-main">
        <div class="container">
            <h1>Dashboard</h1>
            <p class="subtitle">Resumen de hoy -
                <?= date('d/m/Y') ?>
            </p>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📅</div>
                    <div class="stat-value">
                        <?= $stats['total'] ?? 0 ?>
                    </div>
                    <div class="stat-label">Reservas Hoy</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-value">
                        <?= $stats['personas'] ?? 0 ?>
                    </div>
                    <div class="stat-label">Personas Esperadas</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-value">
                        <?= $stats['pendientes'] ?? 0 ?>
                    </div>
                    <div class="stat-label">Pendientes</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">🔔</div>
                    <div class="stat-value">
                        <?= count($upcoming) ?>
                    </div>
                    <div class="stat-label">Próximas (2h)</div>
                </div>
            </div>

            <!-- Upcoming Reservations -->
            <div class="card" style="margin-top: var(--spacing-xl);">
                <h2>Reservaciones Próximas</h2>

                <?php if (count($upcoming) > 0): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <th>Cliente</th>
                                <th>Personas</th>
                                <th>Mesa</th>
                                <th>Teléfono</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($upcoming as $res): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($res['hora']) ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($res['nombre_cliente']) ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($res['num_personas']) ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($res['numero_mesa'] ?? 'N/A') ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($res['telefono']) ?>
                                    </td>
                                    <td>
                                        <button class="btn-small" onclick="markAsArrived('<?= $res['id'] ?>')">
                                            ✅ Llegó
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="color: var(--text-secondary); text-align: center; padding: var(--spacing-lg);">
                        No hay reservaciones próximas
                    </p>
                <?php endif; ?>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="reservations.php?action=new" class="btn btn-primary">➕ Nueva Reservación</a>
                <a href="tables.php" class="btn btn-primary">🗺️ Mapa de Mesas</a>
            </div>
        </div>
    </main>

    <script>
        function markAsArrived(id) {
            if (confirm('¿Confirmar llegada del cliente?')) {
                // TODO: Implement API call
                alert('Función en desarrollo');
            }
        }
    </script>
</body>

</html>