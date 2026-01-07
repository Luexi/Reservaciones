# 📖 Guía de Instalación - Rosa Mezcal

## Requisitos del Sistema

### Mínimos
- Docker 20.10+
- Docker Compose 2.0+
- 2GB RAM disponible
- 5GB espacio en disco

### Recomendados
- 4GB RAM
- 10GB espacio en disco
- Conexión HTTPS (SSL)

## Paso 1: Configurar Supabase

### 1.1 Crear Proyecto
1. Ve a [https://supabase.com](https://supabase.com)
2. Click en "New Project"
3. Nombra tu proyecto: `rosa-mezcal-prod`
4. Elige región más cercana
5. Genera una contraseña segura

### 1.2 Ejecutar Schema
1. Ve a **SQL Editor** en el panel de Supabase
2. Copia todo el contenido de `reservaciones/config/db_schema.sql`
3. Pega y ejecuta

### 1.3 Obtener Credenciales
- **Project URL**: Settings → API → URL
- **Anon Key**: Settings → API → anon public
- **DB Password**: La que creaste en 1.1
- **DB Host**: Settings → Database → Host

## Paso 2: Configurar Variables de Entorno

Crea el archivo `.env` en la raíz del proyecto:

```bash
# Supabase
SUPABASE_URL=https://xxxxxxxxxxx.supabase.co
SUPABASE_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.ey...
SUPABASE_DB_HOST=db.xxxxxxxxxxx.supabase.co
SUPABASE_DB_PASSWORD=tu_password_seguro

# Facebook/Messenger (opcional - configurar después)
FB_PAGE_TOKEN=
FB_VERIFY_TOKEN=rosa_mezcal_verify_2026

# WhatsApp (WPPConnect)
WHATSAPP_SESSION=rosa_mezcal_session
```

## Paso 3: Inicializar Datos Base

### 3.1 Crear Restaurante
En Supabase SQL Editor:

```sql
INSERT INTO restaurantes (nombre, direccion, telefono, email)
VALUES ('Rosa Mezcal', 'Tu Dirección', '+52XXXXXXXXXX', 'contacto@rosamezcal.mx');
```

Copia el `id` generado.

### 3.2 Crear Mesas Iniciales
```sql
-- Reemplaza 'RESTAURANT_ID' con el UUID copiado
INSERT INTO mesas (restaurante_id, numero_mesa, capacidad_max, posicion_x, posicion_y) VALUES
('RESTAURANT_ID', '1', 2, 100, 100),
('RESTAURANT_ID', '2', 4, 250, 100),
('RESTAURANT_ID', '3', 4, 400, 100),
('RESTAURANT_ID', '4', 6, 100, 250),
('RESTAURANT_ID', '5', 6, 250, 250),
('RESTAURANT_ID', '6', 8, 400, 250),
('RESTAURANT_ID', '7', 2, 100, 400),
('RESTAURANT_ID', '8', 4, 250, 400);
```

### 3.3 Configurar Horarios
```sql
-- Lunes a Jueves (1-4)
INSERT INTO configuracion_horarios (restaurante_id, dia_semana, hora_apertura, hora_cierre) VALUES
('RESTAURANT_ID', 1, '18:00', '23:00'),
('RESTAURANT_ID', 2, '18:00', '23:00'),
('RESTAURANT_ID', 3, '18:00', '23:00'),
('RESTAURANT_ID', 4, '18:00', '23:00');

-- Viernes a Domingo (5-0)
INSERT INTO configuracion_horarios (restaurante_id, dia_semana, hora_apertura, hora_cierre) VALUES
('RESTAURANT_ID', 5, '18:00', '01:00'),
('RESTAURANT_ID', 6, '18:00', '01:00'),
('RESTAURANT_ID', 0, '18:00', '01:00');
```

## Paso 4: Docker Deployment

### 4.1 Build
```bash
docker-compose build
```

### 4.2 Iniciar Servicios
```bash
docker-compose up -d
```

### 4.3 Verificar Status
```bash
docker-compose ps
```

Deberías ver:
- ✅ web (puerto 80)
- ✅ redis (puerto 6379)
- ✅ wppconnect (puerto 21465)
- ✅ messenger-bot

## Paso 5: Configurar WhatsApp (WPPConnect)

### 5.1 Escanear QR
1. Ve a: `http://localhost:21465/api/rosa_mezcal_session/start-session`
2. Escanea el código QR con WhatsApp Business
3. Espera la confirmación

### 5.2 Verificar Conexión
```bash
curl http://localhost:21465/api/rosa_mezcal_session/check-connection-session
```

### 5.3 Configurar Webhook
El webhook ya está configurado en `docker-compose.yml` apuntando a:
`http://web/reservaciones/api/webhook_whatsapp.php`

## Paso 6: Configurar Messenger (Opcional)

Ver [CONFIGURACION_APIS.md](CONFIGURACION_APIS.md) para la guía completa.

Pasos resumidos:
1. Crear App en Facebook Developers
2. Agregar producto Messenger
3. Vincular Página de Facebook
4. Configurar Webhook: `https://tu-dominio.com/webhook`
5. Copiar Page Access Token a `.env`

## Paso 7: Primera Prueba

### 7.1 Test Web
1. Ve a `http://localhost/reservaciones/`
2. Completa el formulario
3. Verifica confirmación

### 7.2 Test Admin
1. Ve a `http://localhost/reservaciones/admin/`
2. Login: `admin` / `rosa2026`
3. Verifica dashboard

### 7.3 Test Bot WhatsApp
1. Envía "Hola" al número conectado
2. Verifica respuesta automática

## Personalización

### Cambiar Credenciales Admin
Edita `reservaciones/admin/login.php`:

```php
$ADMIN_USER = 'tu_usuario';
$ADMIN_PASS = password_hash('tu_password_seguro', PASSWORD_BCRYPT);
```

### Cambiar Colores
Edita `reservaciones/assets/css/variables.css`:

```css
:root {
  --primary-color: #TU_COLOR;
  --secondary-color: #TU_COLOR;
}
```

### Cambiar Horarios de Slots
Edita `reservaciones/assets/js/booking.js`, línea ~45:

```javascript
const timeSlots = generateTimeSlots('18:00', '23:00', 30); // inicio, fin, intervalo
```

## Troubleshooting

### Error: "Connection refused" a Supabase
- Verifica credenciales en `.env`
- Verifica que el proyecto Supabase esté activo
- Revisa firewall/ports

### WhatsApp no responde
- Verifica QR escaneado correctamente
- Revisa logs: `docker-compose logs wppconnect`
- Reinicia contenedor: `docker-compose restart wppconnect`

### Admin no carga mesas en mapa
- Verifica que existan mesas en BD
- Abre consola del navegador (F12) para ver errores
- Verifica conexión a API

## Próximos Pasos

1. ✅ Sistema funcionando localmente
2. 📝 Crear datos de prueba
3. 🧪 Testing exhaustivo (ver TESTING.md)
4. 🚀 Deploy a producción (ver DEPLOYMENT.md)
5. 📊 Configurar monitoreo

---

¿Problemas? Revisa [TROUBLESHOOTING.md](TROUBLESHOOTING.md) o contacta soporte.
