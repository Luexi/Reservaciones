<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación - Rosa Mezcal</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/variables.css">
    <style>
        .confirmation-container {
            max-width: 600px;
            margin: 100px auto;
            text-align: center;
        }

        .success-icon {
            font-size: 5rem;
            margin-bottom: var(--spacing-lg);
        }

        .confirmation-details {
            background: var(--surface-dark);
            padding: var(--spacing-xl);
            border-radius: var(--radius-lg);
            margin: var(--spacing-lg) 0;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: var(--spacing-sm) 0;
            border-bottom: 1px solid var(--border-color);
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: var(--text-secondary);
        }

        .detail-value {
            font-weight: 600;
            color: var(--text-primary);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="confirmation-container">
            <div class="success-icon">✅</div>
            <h1>¡Reservación Confirmada!</h1>
            <p class="subtitle">Tu mesa ha sido reservada exitosamente</p>

            <div class="confirmation-details" id="details">
                <!-- Will be populated by JavaScript -->
            </div>

            <p style="color: var(--text-secondary); margin: var(--spacing-lg) 0;">
                Recibirás un mensaje de confirmación en breve.<br>
                Te esperamos en Rosa Mezcal 🍹
            </p>

            <a href="index.php" class="btn btn-primary">Hacer otra reservación</a>
        </div>
    </div>

    <script>
        // Get reservation ID from URL
        const urlParams = new URLSearchParams(window.location.search);
        const reservationId = urlParams.get('id');

        if (reservationId) {
            // In a real app, you would fetch the reservation details
            // For now, we'll show a placeholder
            document.getElementById('details').innerHTML = `
                <div class="detail-row">
                    <span class="detail-label">Código de Reservación:</span>
                    <span class="detail-value">#${reservationId.substring(0, 8).toUpperCase()}</span>
                </div>
            `;
        }
    </script>
</body>

</html>