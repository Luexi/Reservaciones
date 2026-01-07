<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservaciones - Rosa Mezcal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/booking.css">
</head>

<body>
    <div class="container">
        <header class="header">
            <h1>🍹 Rosa Mezcal</h1>
            <p class="subtitle">Reserva tu mesa</p>
        </header>

        <main class="booking-container">
            <div class="card">
                <form id="reservationForm" class="reservation-form">
                    <div class="form-group">
                        <label for="nombre">Nombre Completo *</label>
                        <input type="text" id="nombre" name="nombre" required placeholder="Tu nombre completo">
                    </div>

                    <div class="form-group">
                        <label for="telefono">Teléfono *</label>
                        <input type="tel" id="telefono" name="telefono" required placeholder="+52 123 456 7890"
                            pattern="[+]?[0-9\s]{10,15}">
                    </div>

                    <div class="form-group">
                        <label for="email">Email (opcional)</label>
                        <input type="email" id="email" name="email" placeholder="tu@email.com">
                    </div>

                    <div class="form-group">
                        <label for="num_personas">Número de Personas *</label>
                        <select id="num_personas" name="num_personas" required>
                            <option value="">Selecciona...</option>
                            <option value="1">1 persona</option>
                            <option value="2">2 personas</option>
                            <option value="3">3 personas</option>
                            <option value="4">4 personas</option>
                            <option value="5">5 personas</option>
                            <option value="6">6 personas</option>
                            <option value="7">7 personas</option>
                            <option value="8">8 personas</option>
                            <option value="9">9 personas</option>
                            <option value="10">10 personas</option>
                            <option value="15">15 personas</option>
                            <option value="20">20 personas</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="fecha">Fecha *</label>
                        <input type="date" id="fecha" name="fecha" required>
                    </div>

                    <div class="form-group">
                        <label for="hora">Hora *</label>
                        <select id="hora" name="hora" required disabled>
                            <option value="">Primero selecciona fecha y personas</option>
                        </select>
                        <div id="availability-status" class="availability-status"></div>
                    </div>

                    <div class="form-group">
                        <label for="ocasion">Ocasión Especial</label>
                        <select id="ocasion" name="ocasion">
                            <option value="">Ninguna</option>
                            <option value="cumpleaños">Cumpleaños</option>
                            <option value="aniversario">Aniversario</option>
                            <option value="cita_romantica">Cita Romántica</option>
                            <option value="reunion_negocios">Reunión de Negocios</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="comentarios">Comentarios Adicionales</label>
                        <textarea id="comentarios" name="comentarios" rows="3" maxlength="500"
                            placeholder="Alguna petición especial..."></textarea>
                        <small class="char-count">0/500</small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block" id="submitBtn">
                        Reservar Mesa
                    </button>
                </form>
            </div>
        </main>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <!-- Loading Overlay -->
    <div id="loading" class="loading hidden">
        <div class="spinner"></div>
        <p>Procesando reservación...</p>
    </div>

    <script src="assets/js/booking.js"></script>
</body>

</html>