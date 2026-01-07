# 🍹 Rosa Mezcal - Sistema de Reservaciones

Sistema completo de reservaciones para el bar **Rosa Mezcal** con interfaz web, panel administrativo y bots automatizados para WhatsApp y Facebook Messenger.

## 📋 Características

### Cliente
- ✅ Formulario de reservación minimalista
- 📅 Calendario interactivo con disponibilidad en tiempo real
- 🕐 Selección de horarios disponibles
- 📱 Diseño responsive (mobile-first)
- ✉️ Confirmación instantánea

### Admin
- 🔐 Autenticación segura
- 📊 Dashboard con métricas en tiempo real
- 🗺️ Mapa visual de mesas (drag & drop)
- 📋 Gestión completa de reservaciones
- ⚙️ Configuración de horarios y bloqueos
- 👥 Gestión de walk-ins

### Automatización
- 💬 Bot de WhatsApp (WPPConnect)
- 💬 Bot de Facebook Messenger
- 🔔 Notificaciones automáticas al gerente
- 📝 Registro de historial de clientes

## 🛠️ Stack Tecnológico

- **Backend**: PHP 8.2 + Apache
- **Database**: Supabase (PostgreSQL)
- **Frontend**: HTML5 + CSS3 + Vanilla JavaScript
- **Bots**: Node.js + Express
- **WhatsApp**: WPPConnect Server
- **Messenger**: Facebook Graph API
- **Deployment**: Docker + Docker Compose
- **Cache**: Redis

## 📦 Requisitos

- Docker & Docker Compose
- Cuenta de Supabase (gratis)
- Número de WhatsApp Business (para bot)
- Página de Facebook (para Messenger bot)

## 🚀 Instalación Rápida

### 1. Clonar el proyecto
```bash
git clone [tu-repo]
cd Reservaciones\ Gonzalez
```

### 2. Configurar variables de entorno
```bash
cp .env.example .env
# Edita .env con tus credenciales
```

### 3. Configurar Supabase
1. Crea un proyecto en [supabase.com](https://supabase.com)
2. Ejecuta el schema: `reservaciones/config/db_schema.sql`
3. Copia las credenciales a `.env`

### 4. Iniciar con Docker
```bash
docker-compose up -d
```

### 5. Acceder al sistema
- **Cliente**: http://localhost/reservaciones/
- **Admin**: http://localhost/reservaciones/admin/
  - Usuario: `admin`
  - Contraseña: `rosa2026`

## 📚 Documentación Completa

- [📖 Guía de Instalación](INSTALACION.md)
- [🔧 Configuración de APIs](CONFIGURACION_APIS.md)
- [👨‍💼 Manual de Admin](MANUAL_ADMIN.md)
- [🛠️ Referencia API](API_REFERENCE.md)
- [🚢 Guía de Deployment](DEPLOYMENT.md)

## 🎨 Paleta de Colores (Rosa Mezcal)

- **Primary**: `#E91E63` (Mezcal Pink)
- **Secondary**: `#00E676` (Agave Green)
- **Background**: `#121212` (Dark Luxury)
- **Surface**: `#1E1E1E`

## 📂 Estructura del Proyecto

```
Reservaciones Gonzalez/
├── reservaciones/
│   ├── assets/
│   │   ├── css/
│   │   │   ├── variables.css
│   │   │   ├── booking.css
│   │   │   └── admin.css
│   │   └── js/
│   │       ├── booking.js
│   │       └── table-map.js
│   ├── admin/
│   │   ├── login.php
│   │   ├── dashboard.php
│   │   └── tables.php
│   ├── api/
│   │   ├── check_availability.php
│   │   ├── create_reservation.php
│   │   └── webhook_whatsapp.php
│   ├── config/
│   │   ├── database.php
│   │   └── db_schema.sql
│   └── index.php
├── bot-messenger/
│   ├── index.js
│   ├── package.json
│   └── Dockerfile
├── docker-compose.yml
├── Dockerfile
└── .env.example
```

## 🔐 Seguridad

- ✅ Contraseñas hasheadas con bcrypt
- ✅ Sesiones con timeout (30 min)
- ✅ Validación en cliente y servidor
- ✅ Sanitización de inputs
- ✅ RLS (Row Level Security) en Supabase
- ✅ HTTPS recomendado en producción

## 🧪 Testing

Ver [TESTING.md](TESTING.md) para la guía completa de pruebas.

```bash
# Test checklist básico
- [ ] Crear reservación desde web
- [ ] Verificar disponibilidad
- [ ] Login admin
- [ ] Mover mesas en el mapa
- [ ] Respuesta del bot de WhatsApp
```

## 🤝 Contribuir

Este proyecto fue creado específicamente para **Rosa Mezcal**. Para adaptarlo a tu restaurante, revisa la sección de [Personalización](INSTALACION.md#personalización).

## 📞 Soporte

Para soporte, contacta al equipo de desarrollo o revisa la documentación completa.

## 📄 Licencia

Propietario: Rosa Mezcal © 2026

---

**Desarrollado con ❤️ para Rosa Mezcal 🍹**
