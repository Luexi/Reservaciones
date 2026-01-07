# 🔧 Configuración de APIs - Rosa Mezcal

## Facebook Messenger Bot

### Paso 1: Crear App en Facebook Developers

1. Ve a [https://developers.facebook.com](https://developers.facebook.com)
2. Click en **"My Apps"** → **"Create App"**
3. Selecciona **"Business"** como tipo
4. Llena los datos:
   - **App Name**: Rosa Mezcal Bot
   - **Contact Email**: tu@email.com
   - (**App Purpose**: Business

### Paso 2: Agregar Messenger

1. En el dashboard de tu app, click **"Add Product"**
2. Busca **"Messenger"** y click **"Set Up"**
3. En la sección **"Access Tokens"**:
   - Click **"Add or Remove Pages"**
   - Selecciona tu página "Rosa Mezcal"
   - Acepta permisos
4. **Genera el Page Access Token** y guárdalo

```bash
# Agrega a .env
FB_PAGE_TOKEN=EAAxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

### Paso 3: Configurar Webhook

#### 3.1 Exponer tu servidor (desarrollo local)

Usa **ngrok** para exponer localhost:

```bash
# Instala ngrok
# https://ngrok.com/download

# Expone puerto 3000
ngrok http 3000
```

Copia la URL HTTPS (ej: `https://abc123.ngrok.io`)

#### 3.2 Registrar Webhook en Facebook

1. En Facebook Developers → Messenger → Settings
2. Click **"Add Callback URL"**
3. Llena:
   - **Callback URL**: `https://tu-dominio.com/webhook` (o ngrok URL)
   - **Verify Token**: `rosa_mezcal_verify_2026` (debe coincidir con .env)
4. Click **"Verify and Save"**

#### 3.3 Suscribirse a Eventos

En **"Webhooks"** → **"Add subscriptions"**, selecciona:
- ✅ `messages`
- ✅ `messaging_postbacks`
- ✅ `messaging_optins`

Click **"Save"**

### Paso 4: Probar el Bot

1. Ve a tu página de Facebook "Rosa Mezcal"
2. Click en **"Send Message"**
3. Escribe "Hola"
4. Deberías recibir respuesta automática 🎉

## WhatsApp Bot (WPPConnect)

### Arquitectura

```
WhatsApp Web ←→ WPPConnect Server ←→ Tu Webhook PHP
```

### Paso 1: Iniciar WPPConnect (ya incluido en Docker)

El contenedor ya está configurado en `docker-compose.yml`:

```yaml
wppconnect:
  image: wppconnect/server:latest
  ports:
    - "21465:21465"
```

### Paso 2: Generar QR Code

#### Opción A: Via Browser
```
http://localhost:21465/api/rosa_mezcal_session/start-session
```

Se abrirá una página con el QR. Escanéalo con WhatsApp Business.

#### Opción B: Via API
```bash
curl -X POST http://localhost:21465/api/rosa_mezcal_session/start-session
```

Copia el QR en base64 y decodifícalo.

### Paso 3: Verificar Conexión

```bash
curl http://localhost:21465/api/rosa_mezcal_session/check-connection-session
```

Respuesta exitosa:
```json
{
  "status": "CONNECTED",
  "qrcode": null,
  "phone": "52XXXXXXXXXX"
}
```

### Paso 4: Configurar Webhook

Ya está configurado automáticamente en el docker-compose:

```yaml
environment:
  - WEBHOOK_URL=http://web/reservaciones/api/webhook_whatsapp.php
```

El webhook recibe eventos y responde automáticamente.

### Paso 5: Probar

Envía un mensaje a tu número Business:
```
Hola
```

Deberías recibir el menú automático.

## API Endpoints (WPPConnect)

### Enviar Mensaje
```bash
curl -X POST http://localhost:21465/api/rosa_mezcal_session/send-message \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "52XXXXXXXXXX",
    "message": "Hola desde la API",
    "isGroup": false
  }'
```

### Verificar Estado
```bash
curl http://localhost:21465/api/rosa_mezcal_session/status-session
```

### Cerrar Sesión
```bash
curl -X POST http://localhost:21465/api/rosa_mezcal_session/close-session
```

### Logout (desconectar dispositivo)
```bash
curl -X POST http://localhost:21465/api/rosa_mezcal_session/logout-session
```

## Notificaciones al Gerente

### Configuración

Edita `reservaciones/api/create_reservation.php` para agregar notificación:

```php
// Después de crear reservación exitosa
notifyManager($reservation);

function notifyManager($reservation) {
    $message = "🔔 *NUEVA RESERVA*\n\n" .
               "👤 {$reservation['nombre']}\n" .
               "📅 {$reservation['fecha']}\n" .
               "🕐 {$reservation['hora']}\n" .
               "👥 {$reservation['num_personas']} personas\n" .
               "📱 {$reservation['telefono']}";
    
    // Número del gerente
    $managerPhone = '52XXXXXXXXXX';
    
    // Enviar via WPPConnect
    $ch = curl_init('http://wppconnect:21465/api/rosa_mezcal_session/send-message');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'phone' => $managerPhone,
        'message' => $message
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_exec($ch);
    curl_close($ch);
}
```

## Troubleshooting

### Messenger: "Webhook verification failed"
- Verifica que `FB_VERIFY_TOKEN` en `.env` coincida exactamente
- Verifica que el servidor esté corriendo en el puerto correcto
- Revisa logs: `docker-compose logs messenger-bot`

### WhatsApp: "QR expired"
- Genera nuevo QR: `curl -X POST .../start-session`
- Escanea rápidamente (QR expira en 60 segundos)

### WhatsApp: "Phone disconnected"
- El teléfono debe estar conectado a internet
- Regenera sesión si persiste
- Verifica que WhatsApp Business esté actualizado

### Webhook no recibe mensajes
- Verifica firewall/puertos
- Revisa logs de webhook: `tail -f reservaciones/logs/whatsapp_webhook.log`
- Verifica URL del webhook en configuración

## Seguridad en Producción

### 1. HTTPS Obligatorio
Tanto Facebook como WPPConnect requieren HTTPS en producción.

### 2. Validar Webhooks
Para Messenger, verifica firma:

```javascript
const crypto = require('crypto');

function verifyRequestSignature(req, res, buf) {
    const signature = req.headers['x-hub-signature-256'];
    const expectedSignature = crypto
        .createHmac('sha256', process.env.FB_APP_SECRET)
        .update(buf)
        .digest('hex');
    
    if (signature !== `sha256=${expectedSignature}`) {
        throw new Error('Invalid signature');
    }
}
```

### 3. Rate Limiting
Implementa límites para prevenir abuso:

```php
// En webhook_whatsapp.php
$max_requests_per_minute = 10;
// Implementar con Redis o similar
```

---

**¡APIs configuradas!** Siguiente: [DEPLOYMENT.md](DEPLOYMENT.md)
