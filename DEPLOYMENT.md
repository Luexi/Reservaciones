# 🚢 Guía de Deployment - Rosa Mezcal

## Deployment a Producción

### Opción 1: VPS (DigitalOcean, AWS, etc.)

#### Requisitos
- VPS con Ubuntu 22.04+
- Dominio configurado (ej: rosamezcal.mx)
- 2GB RAM mínimo
- Docker instalado

#### Paso 1: Preparar el Servidor

```bash
# Conectar al servidor
ssh root@tu-servidor-ip

# Actualizar sistema
apt update && apt upgrade -y

# Instalar Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh

# Instalar Docker Compose
apt install docker-compose -y

# Crear usuario para la app
adduser rosamezcal
usermod -aG docker rosamezcal
su - rosamezcal
```

#### Paso 2: Clonar el Proyecto

```bash
cd /home/rosamezcal
git clone https://github.com/tu-usuario/rosa-mezcal-reservaciones.git
cd rosa-mezcal-reservaciones
```

#### Paso 3: Configurar Variables de Producción

```bash
cp .env.example .env
nano .env
```

Configurar:
```bash
SUPABASE_URL=https://tu-proyecto.supabase.co
SUPABASE_KEY=tu-key-produccion
SUPABASE_DB_HOST=db.tu-proyecto.supabase.co
SUPABASE_DB_PASSWORD=password-seguro

FB_PAGE_TOKEN=token-produccion
FB_VERIFY_TOKEN=token-verificacion-seguro
```

#### Paso 4: Configurar Nginx Reverse Proxy

```bash
# Como root
apt install nginx certbot python3-certbot-nginx -y

# Crear configuración
nano /etc/nginx/sites-available/rosamezcal
```

Contenido:
```nginx
server {
    listen 80;
    server_name rosamezcal.mx www.rosamezcal.mx;

    location /reservaciones {
        proxy_pass http://localhost:80;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    # Existing site
    location / {
        # Tu configuración actual
    }
}
```

```bash
# Activar configuración
ln -s /etc/nginx/sites-available/rosamezcal /etc/nginx/sites-enabled/
nginx -t
systemctl restart nginx
```

#### Paso 5: Certificado SSL

```bash
certbot --nginx -d rosamezcal.mx -d www.rosamezcal.mx
```

#### Paso 6: Deploy con Docker

```bash
cd /home/rosamezcal/rosa-mezcal-reservaciones
docker-compose up -d
```

#### Paso 7: Configurar Auto-restart

```bash
# Crear servicio systemd
sudo nano /etc/systemd/system/rosamezcal.service
```

Contenido:
```ini
[Unit]
Description=Rosa Mezcal Reservation System
Requires=docker.service
After=docker.service

[Service]
Type=oneshot
RemainAfterExit=yes
WorkingDirectory=/home/rosamezcal/rosa-mezcal-reservaciones
ExecStart=/usr/bin/docker-compose up -d
ExecStop=/usr/bin/docker-compose down
TimeoutStartSec=0

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl enable rosamezcal
sudo systemctl start rosamezcal
```

---

### Opción 2: Deploy en cPanel (Hosting Compartido)

#### Limitaciones
- ❌ No soporta Docker directamente
- ✅ Solo PHP puede correr
- ⚠️ Bots requerirán servidor separado

#### Pasos:

1. **Subir archivos PHP**
   - Usar FileZilla o FileManager
   - Subir carpeta `reservaciones/` a `public_html/`

2. **Configurar Base de Datos**
   - Supabase sigue siendo la BD (remota)
   - Actualizar `config/database.php` con credenciales

3. **Bots en servidor separado**
   - Deploy bots en Heroku/Railway (gratis)
   - Configurar webhooks apuntando a cPanel

---

### Opción 3: Deploy Bots en Heroku (Gratis)

#### Messenger Bot

```bash
cd bot-messenger

# Login Heroku
heroku login

# Crear app
heroku create rosa-mezcal-messenger

# Configurar variables
heroku config:set FB_PAGE_TOKEN=tu-token
heroku config:set FB_VERIFY_TOKEN=tu-verify-token

# Deploy
git init
git add .
git commit -m "Initial deploy"
heroku git:remote -a rosa-mezcal-messenger
git push heroku main
```

URL del bot: `https://rosa-mezcal-messenger.herokuapp.com/webhook`

---

### Opción 4: Railway.app (Recomendado para Bots)

1. Ve a [railway.app](https://railway.app)
2. Conecta GitHub
3. Selecciona el repo
4. Railway detectará automáticamente:
   - `Dockerfile` para web
   - `package.json` para bot-messenger
5. Configura variables de entorno
6. Deploy automático en cada push

---

## Post-Deployment Checklist

### Seguridad
- [ ] Cambiar contraseña de admin
- [ ] Habilitar HTTPS (SSL)
- [ ] Configurar firewall (UFW)
- [ ] Limitar acceso SSH (solo clave pública)
- [ ] Actualizar `FB_VERIFY_TOKEN` a valor seguro
- [ ] Configurar backups automáticos de Supabase

### Configuración
- [ ] Configurar dominio en Supabase (CORS)
- [ ] Actualizar webhooks con URL de producción
- [ ] Escanear QR de WhatsApp en servidor
- [ ] Probar flujo completo de reservación
- [ ] Configurar notificaciones al gerente

### Monitoreo
- [ ] Configurar logs en `/var/log/rosamezcal/`
- [ ] Instalar Uptime监控 (UptimeRobot gratis)
- [ ] Configurar alertas de error
- [ ] Revisar métricas de Supabase

---

## Actualización del Sistema

### Actualizar Código

```bash
cd /home/rosamezcal/rosa-mezcal-reservaciones
git pull origin main
docker-compose down
docker-compose build
docker-compose up -d
```

### Actualizar Base de Datos

```bash
# Conectar a Supabase SQL Editor
# Ejecutar migration SQL
```

### Rollback en caso de error

```bash
# Ver commits
git log --oneline

# Volver a versión anterior
git checkout abc123
docker-compose down && docker-compose up -d

# O usar backup
docker-compose down
cp -r backup_20260107/ .
docker-compose up -d
```

---

## Backups Automaticos

### Script de Backup

```bash
#!/bin/bash
# /home/rosamezcal/backup.sh

BACKUP_DIR="/home/rosamezcal/backups"
DATE=$(date +%Y%m%d_%H%M%S)

# Crear directorio
mkdir -p $BACKUP_DIR

# Backup de archivos
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /home/rosamezcal/rosa-mezcal-reservaciones

# Backup de Supabase (via pg_dump remoto)
# Requiere configurar pg_dump con credenciales de Supabase
PGPASSWORD="tu-password" pg_dump -h db.tu-proyecto.supabase.co \
  -U postgres -d postgres > $BACKUP_DIR/db_$DATE.sql

# Mantener solo últimos 7 días
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete

echo "Backup completed: $DATE"
```

### Configurar Cron

```bash
crontab -e

# Backup diario a las 3 AM
0 3 * * * /home/rosamezcal/backup.sh >> /var/log/rosamezcal-backup.log 2>&1
```

---

## Monitoreo y Logs

### Ver Logs en Tiempo Real

```bash
# Logs de Docker
docker-compose logs -f

# Solo web
docker-compose logs -f web

# Solo bots
docker-compose logs -f wppconnect messenger-bot
```

### Logs de Aplicación

```bash
# Logs de WhatsApp
tail -f reservaciones/logs/whatsapp_webhook.log

# Logs de PHP (dentro del contenedor)
docker-compose exec web tail -f /var/log/apache2/error.log
```

### Monitoreo de Recursos

```bash
# CPU y RAM
docker stats

# Espacio en disco
df -h
```

---

## Performance Optimization

### 1. Habilitar Caché de Redis

Modificar `config/database.php`:

```php
// Cache availability queries
$redis = new Redis();
$redis->connect('redis', 6379);

$cache_key = "availability_{$fecha}_{$hora}_{$num_personas}";
$cached = $redis->get($cache_key);

if ($cached) {
    return json_decode($cached, true);
}

// ... query BD ...

$redis->setex($cache_key, 300, json_encode($result)); // 5 min cache
```

### 2. Comprimir Assets

```bash
# Minificar CSS
apt install yui-compressor
yui-compressor reservaciones/assets/css/booking.css > booking.min.css

# Minificar JS
apt install uglifyjs
uglifyjs reservaciones/assets/js/booking.js -o booking.min.js
```

### 3. Optimizar Imágenes

```bash
# Instalar ImageMagick
apt install imagemagick

# Optimizar imágenes
find reservaciones/assets/img -name "*.jpg" -exec mogrify -quality 85 {} \;
```

---

## Troubleshooting en Producción

### Error: "Connection refused" a Supabase
```bash
# Verificar conectividad
ping db.tu-proyecto.supabase.co
curl https://tu-proyecto.supabase.co

# Verificar credenciales
docker-compose exec web php -r "include 'config/database.php'; $db = new Database(); var_dump($db->getConnection());"
```

### WhatsApp desconectado
```bash
# Verificar estado
curl http://localhost:21465/api/rosa_mezcal_session/status-session

# Regenerar QR
curl -X POST http://localhost:21465/api/rosa_mezcal_session/start-session
```

### Sitio lento
```bash
# Verificar recursos
docker stats

# Aumentar recursos PHP
# Editar Dockerfile, agregar:
RUN echo "memory_limit = 512M" > /usr/local/etc/php/conf.d/memory.ini

# Rebuild
docker-compose down && docker-compose build && docker-compose up -d
```

---

## Estimación de Costos (Mensual)

### Stack Gratuito
- ✅ Supabase: Gratis (hasta 500MB BD, 2GB bandwidth)
- ✅ Railway: $5/mes (500 horas gratis)
- ✅ VPS DigitalOcean: $6/mes (básico)
- ✅ Dominio: $12/año (~$1/mes)

**Total: ~$7-12/mes**

### Stack Profesional
- VPS 2GB RAM: $12/mes
- Supabase Pro: $25/mes
- Dominio: $12/año
- Backups: $5/mes

**Total: ~$43/mes**

---

**¡Sistema listo para producción!** 🚀

Para soporte post-deployment, consulta [MANTENIMIENTO.md](MANTENIMIENTO.md)
