<?php
// reservaciones/admin/config.php
require_once 'auth_check.php';
require_once '../config/supabase.php';

$supabase = new SupabaseClient();

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_hours') {
        // Update business hours
        try {
            for ($dia = 0; $dia <= 6; $dia++) {
                $cerrado = isset($_POST["cerrado_$dia"]);
                $apertura = $_POST["apertura_$dia"] ?? null;
                $cierre = $_POST["cierre_$dia"] ?? null;
                
                // Check if record exists for this day
                $existing = $supabase->get('configuracion_horarios', [
                    'dia_semana' => 'eq.' . $dia,
                    'limit' => 1
                ]);
                
                $data = [
                    'dia_semana' => $dia,
                    'hora_apertura' => $apertura,
                    'hora_cierre' => $cierre,
                    'cerrado' => $cerrado
                ];
                
                if (empty($existing)) {
                    // Insert new
                    $supabase->post('configuracion_horarios', $data);
                } else {
                    // Update existing
                    $supabase->patch('configuracion_horarios', $data, [
                        'dia_semana' => 'eq.' . $dia
                    ]);
                }
            }
            
            $success_message = 'Horarios actualizados exitosamente';
        } catch(Exception $e) {
            $error_message = 'Error: ' . $e->getMessage();
        }
    }
}

// Get current configuration
try {
    $horarios = $supabase->get('configuracion_horarios', [
        'order' => 'dia_semana.asc'
    ]);
    
    // Organize by day
    $horarios_por_dia = [];
    foreach ($horarios as $h) {
        $horarios_por_dia[$h['dia_semana']] = $h;
    }
} catch(Exception $e) {
    $error = $e->getMessage();
    $horarios_por_dia = [];
}

$dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Rosa Mezcal Admin</title>
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
                <a href="tables.php">Mesas</a>
                <a href="config.php" class="active">Configuración</a>
                <a href="logout.php" class="logout-btn">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <main class="admin-main">
        <div class="container">
            <h1>Configuración del Sistema</h1>

            <?php if ($success_message): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <!-- Business Hours -->
            <div class="card">
                <h2>Horarios de Operación</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="update_hours">
                    
                    <table class="config-table">
                        <thead>
                            <tr>
                                <th>Día</th>
                                <th>Hora Apertura</th>
                                <th>Hora Cierre</th>
                                <th>Cerrado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($i = 0; $i <= 6; $i++): ?>
                                <?php 
                                $config = $horarios_por_dia[$i] ?? null;
                                $cerrado = $config['cerrado'] ?? false;
                                ?>
                                <tr>
                                    <td><strong><?= $dias[$i] ?></strong></td>
                                    <td>
                                        <input type="time" 
                                               name="apertura_<?= $i ?>" 
                                               value="<?= $config['hora_apertura'] ?? '18:00' ?>"
                                               <?= $cerrado ? 'disabled' : '' ?>>
                                    </td>
                                    <td>
                                        <input type="time" 
                                               name="cierre_<?= $i ?>" 
                                               value="<?= $config['hora_cierre'] ?? '23:00' ?>"
                                               <?= $cerrado ? 'disabled' : '' ?>>
                                    </td>
                                    <td>
                                        <input type="checkbox" 
                                               name="cerrado_<?= $i ?>" 
                                               <?= $cerrado ? 'checked' : '' ?>
                                               onchange="toggleDay(<?= $i ?>)">
                                    </td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                    
                    <div style="margin-top: var(--spacing-lg);">
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>

            <!-- Additional Settings -->
            <div class="card" style="margin-top: var(--spacing-lg);">
                <h2>Otras Configuraciones</h2>
                
                <div class="setting-row">
                    <div>
                        <h3>Duración de Reserva</h3>
                        <p>Tiempo predeterminado por reserva</p>
                    </div>
                    <select style="width: 200px;">
                        <option value="90">90 minutos</option>
                        <option value="120" selected>120 minutos (2 horas)</option>
                        <option value="150">150 minutos</option>
                        <option value="180">180 minutos (3 horas)</option>
                    </select>
                </div>

                <div class="setting-row">
                    <div>
                        <h3>Intervalo de Reservas</h3>
                        <p>Cada cuánto tiempo se permite reservar</p>
                    </div>
                    <select style="width: 200px;">
                        <option value="15">15 minutos</option>
                        <option value="30" selected>30 minutos</option>
                        <option value="60">60 minutos</option>
                    </select>
                </div>

                <div class="setting-row">
                    <div>
                        <h3>Confirmación Automática</h3>
                        <p>Auto-confirmar reservas sin revisión manual</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" checked>
                        <span class="slider"></span>
                    </label>
                </div>
            </div>
        </div>
    </main>

    <style>
        .config-table {
            width: 100%;
            margin-top: var(--spacing-lg);
        }
        
        .config-table input[type="time"] {
            padding: var(--spacing-sm);
            background: var(--surface-hover);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            color: var(--text-primary);
            font-family: var(--font-family);
        }
        
        .alert {
            padding: var(--spacing-md);
            border-radius: var(--radius-md);
            margin-bottom: var(--spacing-lg);
        }
        
        .alert-success {
            background: rgba(0, 230, 118, 0.1);
            color: var(--success);
            border: 1px solid var(--success);
        }
        
        .alert-error {
            background: rgba(207, 102, 121, 0.1);
            color: var(--error);
            border: 1px solid var(--error);
        }
        
        .setting-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--spacing-lg) 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .setting-row:last-child {
            border-bottom: none;
        }
        
        .setting-row h3 {
            margin: 0 0 var(--spacing-xs) 0;
            font-size: 1rem;
        }
        
        .setting-row p {
            margin: 0;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        /* Toggle Switch */
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: var(--border-color);
            transition: .4s;
            border-radius: 34px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: var(--primary-color);
        }
        
        input:checked + .slider:before {
            transform: translateX(26px);
        }
    </style>

    <script>
        function toggleDay(day) {
            const checkbox = document.querySelector(`input[name="cerrado_${day}"]`);
            const apertura = document.querySelector(`input[name="apertura_${day}"]`);
            const cierre = document.querySelector(`input[name="cierre_${day}"]`);
            
            if (checkbox.checked) {
                apertura.disabled = true;
                cierre.disabled = true;
            } else {
                apertura.disabled = false;
                cierre.disabled = false;
            }
        }
    </script>
</body>
</html>
