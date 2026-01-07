<?php
// reservaciones/admin/tables.php
require_once 'auth_check.php';
require_once '../config/supabase.php';

$supabase = new SupabaseClient();

// Get all tables
try {
    $tables = $supabase->get('mesas', [
        'activa' => 'eq.true',
        'order' => 'numero_mesa.asc'
    ]);
} catch (Exception $e) {
    $error = $e->getMessage();
    $tables = [];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa de Mesas - Rosa Mezcal Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/variables.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>
    <nav class="admin-nav">
        <div class="container">
            <div class="nav-brand">🍹 Rosa Mezcal Admin</div>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="reservations.php">Reservaciones</a>
                <a href="tables.php" class="active">Mesas</a>
                <a href="config.php">Configuración</a>
                <a href="logout.php" class="logout-btn">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <main class="admin-main">
        <div class="container">
            <h1>Mapa de Mesas</h1>
            <p class="subtitle">Arrastra y suelta para reorganizar. Click para editar.</p>

            <div class="table-map-container">
                <div id="tableMap" class="table-map">
                    <?php foreach ($tables as $table): ?>
                        <div class="table-item" data-id="<?= $table['id'] ?>"
                            style="left: <?= $table['posicion_x'] ?? 100 ?>px; top: <?= $table['posicion_y'] ?? 100 ?>px;">
                            <div class="table-number">Mesa
                                <?= $table['numero_mesa'] ?>
                            </div>
                            <div class="table-capacity">
                                <?= $table['capacidad_max'] ?> personas
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="quick-actions">
                <button class="btn btn-primary" onclick="addNewTable()">➕ Agregar Mesa</button>
                <button class="btn btn-primary" onclick="saveLayout()">💾 Guardar Disposición</button>
            </div>
        </div>
    </main>

    <script src="../assets/js/table-map.js"></script>
</body>

</html>