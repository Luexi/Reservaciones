<?php
// reservaciones/admin/reservations.php
require_once 'auth_check.php';
require_once '../config/supabase.php';

$supabase = new SupabaseClient();

// Get filter parameters
$fecha_filtro = $_GET['fecha'] ?? '';
$estado_filtro = $_GET['estado'] ?? '';

// Build query
try {
    $params = ['select' => '*', 'order' => 'fecha.desc,hora.desc', 'limit' => 100];

    if ($fecha_filtro) {
        $params['fecha'] = 'eq.' . $fecha_filtro;
    }

    if ($estado_filtro) {
        $params['estado'] = 'eq.' . $estado_filtro;
    }

    $allReservations = $supabase->get('reservaciones', $params);

    // Get mesa numbers for each reservation
    $reservations = [];
    foreach ($allReservations as $reservation) {
        if ($reservation['mesa_id']) {
            $mesa = $supabase->get('mesas', [
                'id' => 'eq.' . $reservation['mesa_id'],
                'select' => 'numero_mesa,capacidad_max',
                'limit' => 1
            ]);
            if (!empty($mesa)) {
                $reservation['numero_mesa'] = $mesa[0]['numero_mesa'];
                $reservation['capacidad_max'] = $mesa[0]['capacidad_max'];
            }
        }
        $reservations[] = $reservation;
    }

} catch (Exception $e) {
    $error = $e->getMessage();
    $reservations = [];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservaciones - Rosa Mezcal Admin</title>
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
                <a href="reservations.php" class="active">Reservaciones</a>
                <a href="tables.php">Mesas</a>
                <a href="config.php">Configuración</a>
                <a href="logout.php" class="logout-btn">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <main class="admin-main">
        <div class="container">
            <h1>Gestión de Reservaciones</h1>

            <!-- Filters -->
            <div class="card" style="margin-bottom: var(--spacing-lg);">
                <form method="GET" class="filter-form">
                    <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: var(--spacing-md);">
                        <div>
                            <label>Fecha:</label>
                            <input type="date" name="fecha" value="<?= htmlspecialchars($fecha_filtro) ?>">
                        </div>
                        <div>
                            <label>Estado:</label>
                            <select name="estado">
                                <option value="">Todos</option>
                                <option value="pendiente" <?= $estado_filtro === 'pendiente' ? 'selected' : '' ?>>Pendiente
                                </option>
                                <option value="confirmada" <?= $estado_filtro === 'confirmada' ? 'selected' : '' ?>>
                                    Confirmada</option>
                                <option value="llego" <?= $estado_filtro === 'llego' ? 'selected' : '' ?>>Llegó</option>
                                <option value="no_llego" <?= $estado_filtro === 'no_llego' ? 'selected' : '' ?>>No Llegó
                                </option>
                                <option value="cancelada" <?= $estado_filtro === 'cancelada' ? 'selected' : '' ?>>Cancelada
                                </option>
                            </select>
                        </div>
                        <div style="align-self: end;">
                            <button type="submit" class="btn btn-primary">Filtrar</button>
                            <a href="reservations.php" class="btn" style="background: var(--surface-hover);">Limpiar</a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Reservations Table -->
            <div class="card">
                <h2>Reservaciones (
                    <?= count($reservations) ?>)
                </h2>

                <?php if (count($reservations) > 0): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Cliente</th>
                                <th>Teléfono</th>
                                <th>Personas</th>
                                <th>Mesa</th>
                                <th>Estado</th>
                                <th>Origen</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservations as $res): ?>
                                <tr>
                                    <td>
                                        <?= date('d/m/Y', strtotime($res['fecha'])) ?>
                                    </td>
                                    <td>
                                        <?= substr($res['hora'], 0, 5) ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($res['nombre_cliente']) ?>
                                        <?php if ($res['ocasion_especial']): ?>
                                            <br><small style="color: var(--secondary-color);">🎉
                                                <?= htmlspecialchars($res['ocasion_especial']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($res['telefono']) ?>
                                    </td>
                                    <td>
                                        <?= $res['num_personas'] ?>
                                    </td>
                                    <td>
                                        <?= $res['numero_mesa'] ?? 'N/A' ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $res['estado'] ?>">
                                            <?= ucfirst($res['estado']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= ucfirst($res['origen']) ?>
                                    </td>
                                    <td>
                                        <button class="btn-small" onclick="viewDetails('<?= $res['id'] ?>')">Ver</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="color: var(--text-secondary); text-align: center; padding: var(--spacing-xl);">
                        No se encontraron reservaciones con los filtros aplicados.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <style>
        .filter-form label {
            display: block;
            margin-bottom: var(--spacing-xs);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .filter-form input,
        .filter-form select {
            width: 100%;
            padding: var(--spacing-sm);
            background: var(--surface-hover);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            color: var(--text-primary);
            font-family: var(--font-family);
        }

        .badge {
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--radius-sm);
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-pendiente {
            background: rgba(251, 191, 36, 0.2);
            color: #FBB024;
        }

        .badge-confirmada {
            background: rgba(0, 230, 118, 0.2);
            color: var(--success);
        }

        .badge-llego {
            background: rgba(0, 230, 118, 0.3);
            color: var(--success);
        }

        .badge-no_llego {
            background: rgba(207, 102, 121, 0.2);
            color: var(--error);
        }

        .badge-cancelada {
            background: rgba(207, 102, 121, 0.2);
            color: var(--error);
        }
    </style>

    <script>
        function viewDetails(id) {
            alert('Ver detalles de: ' + id + '\n\n(Función en desarrollo)');
        }
    </script>
</body>

</html>