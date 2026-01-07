# 🛠️ Referencia API - Rosa Mezcal

## Base URL

```
Local: http://localhost/reservaciones/api/
Producción: https://rosamezcal.mx/reservaciones/api/
```

---

## Endpoints

### 1. Check Availability

Verifica la disponibilidad de mesas para una fecha, hora y número de personas específicos.

**Endpoint:** `GET /check_availability.php`

**Parámetros:**

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| fecha | string | ✅ | Fecha en formato YYYY-MM-DD |
| hora | string | ✅ | Hora en formato HH:MM (24h) |
| num_personas | integer | ✅ | Número de personas (1-20) |

**Ejemplo Request:**

```bash
GET /api/check_availability.php?fecha=2026-01-15&hora=19:00&num_personas=4
```

**Respuesta Exitosa (200):**

```json
{
  "success": true,
  "available": true,
  "tables_count": 3,
  "tables": [
    {
      "mesa_id": "550e8400-e29b-41d4-a716-446655440000",
      "capacidad": 4
    },
    {
      "mesa_id": "550e8400-e29b-41d4-a716-446655440001",
      "capacidad": 6
    }
  ]
}
```

**Respuesta sin disponibilidad (200):**

```json
{
  "success": true,
  "available": false,
  "tables_count": 0,
  "tables": []
}
```

**Respuesta Error (400):**

```json
{
  "success": false,
  "error": "Faltan parámetros requeridos"
}
```

---

### 2. Create Reservation

Crea una nueva reservación en el sistema.

**Endpoint:** `POST /create_reservation.php`

**Headers:**
```
Content-Type: application/json
```

**Body (JSON):**

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| nombre | string | ✅ | Nombre completo del cliente |
| telefono | string | ✅ | Teléfono con código de país (+52...) |
| email | string | ❌ | Email del cliente |
| num_personas | integer | ✅ | Número de personas (1-20) |
| fecha | string | ✅ | Fecha YYYY-MM-DD |
| hora | string | ✅ | Hora HH:MM |
| ocasion_especial | string | ❌ | cumpleaños, aniversario, cita_romantica, reunion_negocios, otro |
| comentarios | string | ❌ | Comentarios adicionales (máx 500 chars) |
| origen | string | ❌ | web, whatsapp, messenger, telefono, walkin (default: web) |

**Ejemplo Request:**

```bash
POST /api/create_reservation.php
Content-Type: application/json

{
  "nombre": "Juan Pérez García",
  "telefono": "+525512345678",
  "email": "juan@example.com",
  "num_personas": 4,
  "fecha": "2026-01-15",
  "hora": "19:30",
  "ocasion_especial": "cumpleaños",
  "comentarios": "Mesa cerca de la ventana si es posible",
  "origen": "web"
}
```

**Respuesta Exitosa (200):**

```json
{
  "success": true,
  "reservation_id": "660e8400-e29b-41d4-a716-446655440000",
  "mesa_asignada": "550e8400-e29b-41d4-a716-446655440002",
  "created_at": "2026-01-07 14:30:00",
  "mensaje": "¡Reservación creada exitosamente!"
}
```

**Respuesta Error (400):**

```json
{
  "success": false,
  "error": "No hay mesas disponibles para la fecha y hora seleccionadas"
}
```

```json
{
  "success": false,
  "error": "Campo requerido faltante: telefono"
}
```

---

### 3. Get Reservations

Obtiene una lista de reservaciones con filtros opcionales.

**Endpoint:** `GET /get_reservations.php`

**Parámetros (todos opcionales):**

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| fecha | string | Filtrar por fecha específica (YYYY-MM-DD) |
| estado | string | pendiente, confirmada, llego, no_llego, cancelada |
| fecha_inicio | string | Rango inicio (YYYY-MM-DD) |
| fecha_fin | string | Rango fin (YYYY-MM-DD) |
| limit | integer | Límite de resultados |

**Ejemplo Request:**

```bash
# Todas las reservas de hoy
GET /api/get_reservations.php?fecha=2026-01-07

# Reservas confirmadas de enero
GET /api/get_reservations.php?estado=confirmada&fecha_inicio=2026-01-01&fecha_fin=2026-01-31

# Últimas 10 reservas
GET /api/get_reservations.php?limit=10
```

**Respuesta Exitosa (200):**

```json
{
  "success": true,
  "count": 2,
  "reservations": [
    {
      "id": "660e8400-e29b-41d4-a716-446655440000",
      "restaurante_id": "770e8400-e29b-41d4-a716-446655440000",
      "mesa_id": "550e8400-e29b-41d4-a716-446655440002",
      "numero_mesa": "5",
      "capacidad_max": 6,
      "nombre_cliente": "Juan Pérez",
      "telefono": "+525512345678",
      "email": "juan@example.com",
      "num_personas": 4,
      "fecha": "2026-01-15",
      "hora": "19:30:00",
      "duracion_minutos": 120,
      "ocasion_especial": "cumpleaños",
      "comentarios": "Mesa cerca de la ventana",
      "estado": "confirmada",
      "origen": "web",
      "confirmada_por": null,
      "confirmada_en": null,
      "created_at": "2026-01-07 14:30:00",
      "updated_at": "2026-01-07 14:30:00"
    }
  ]
}
```

---

### 4. Update Table Positions

Actualiza las posiciones X,Y de las mesas en el mapa visual.

**Endpoint:** `POST /update_table_positions.php`

**Headers:**
```
Content-Type: application/json
```

**Body (JSON):**

```json
{
  "positions": [
    {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "x": 150.5,
      "y": 200.0
    },
    {
      "id": "550e8400-e29b-41d4-a716-446655440001",
      "x": 300.0,
      "y": 200.0
    }
  ]
}
```

**Respuesta Exitosa (200):**

```json
{
  "success": true,
  "message": "Positions updated"
}
```

**Respuesta Error (400):**

```json
{
  "success": false,
  "error": "No positions provided"
}
```

---

### 5. WhatsApp Webhook (Interno)

Recibe eventos de WPPConnect y procesa mensajes.

**Endpoint:** `POST /webhook_whatsapp.php`

**Headers:**
```
Content-Type: application/json
```

**Body (de WPPConnect):**

```json
{
  "event": "onMessage",
  "session": "rosa_mezcal_session",
  "data": {
    "id": "message-id",
    "from": "525512345678@c.us",
    "body": "Hola",
    "timestamp": 1704645894
  }
}
```

**Respuesta:**

```json
{
  "success": true
}
```

> ⚠️ Este endpoint es llamado automáticamente por WPPConnect. No debe ser llamado manualmente.

---

## Estados de Reservación

| Estado | Descripción |
|--------|-------------|
| `pendiente` | Reservación creada, esperando confirmación |
| `confirmada` | Reservación confirmada por el admin o automáticamente |
| `llego` | Cliente llegó al restaurante |
| `no_llego` | Cliente no se presentó (no-show) |
| `cancelada` | Reservación cancelada |

---

## Códigos de Respuesta HTTP

| Código | Significado |
|--------|-------------|
| 200 | Éxito |
| 400 | Error de validación o parámetros faltantes |
| 405 | Método HTTP no permitido |
| 500 | Error interno del servidor |

---

## Códigos de Ejemplo

### JavaScript (Fetch API)

```javascript
// Check availability
async function checkAvailability(fecha, hora, personas) {
  const response = await fetch(
    `/api/check_availability.php?fecha=${fecha}&hora=${hora}&num_personas=${personas}`
  );
  const data = await response.json();
  return data;
}

// Create reservation
async function createReservation(reservationData) {
  const response = await fetch('/api/create_reservation.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(reservationData)
  });
  const data = await response.json();
  return data;
}
```

### cURL

```bash
# Check availability
curl "http://localhost/reservaciones/api/check_availability.php?fecha=2026-01-15&hora=19:00&num_personas=4"

# Create reservation
curl -X POST http://localhost/reservaciones/api/create_reservation.php \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Juan Pérez",
    "telefono": "+525512345678",
    "num_personas": 4,
    "fecha": "2026-01-15",
    "hora": "19:30"
  }'
```

### PHP

```php
// Check availability
$fecha = '2026-01-15';
$hora = '19:00';
$personas = 4;

$response = file_get_contents(
  "http://localhost/reservaciones/api/check_availability.php?" .
  "fecha=$fecha&hora=$hora&num_personas=$personas"
);
$data = json_decode($response, true);

// Create reservation
$reservation = [
  'nombre' => 'Juan Pérez',
  'telefono' => '+525512345678',
  'num_personas' => 4,
  'fecha' => '2026-01-15',
  'hora' => '19:30'
];

$ch = curl_init('http://localhost/reservaciones/api/create_reservation.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($reservation));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$data = json_decode($response, true);
```

### Python

```python
import requests

# Check availability
params = {
    'fecha': '2026-01-15',
    'hora': '19:00',
    'num_personas': 4
}
response = requests.get('http://localhost/reservaciones/api/check_availability.php', params=params)
data = response.json()

# Create reservation
reservation = {
    'nombre': 'Juan Pérez',
    'telefono': '+525512345678',
    'num_personas': 4,
    'fecha': '2026-01-15',
    'hora': '19:30'
}
response = requests.post('http://localhost/reservaciones/api/create_reservation.php', json=reservation)
data = response.json()
```

---

## Rate Limiting

Actualmente no hay rate limiting implementado. Para producción, se recomienda:

- 100 requests/minuto por IP para endpoints públicos
- 1000 requests/minuto para servicios internos (bots)

---

## Versionamiento

Versión actual: **v1.0**

No hay versionamiento de API implementado. Futuras versiones usarán:
- `/api/v2/...`

---

## Soporte

Para reportar bugs o solicitar features relacionados con la API, contactar al equipo de desarrollo.
