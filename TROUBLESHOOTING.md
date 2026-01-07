# 🔧 Troubleshooting - Guía de Solución de Problemas

## Problemas Comunes

### 1. La página de reservaciones no carga horarios
**Síntoma**: El selector de horas se queda cargando o vacío.
**Causa Probable**:
- Error de conexión a la Base de Datos.
- API `check_availability.php` fallando.
**Solución**:
1. Abrir DevTools (F12) -> Network.
2. Ver respuesta de `check_availability.php`.
3. Si es error 500, revisar logs de PHP (`docker-compose logs web`).
4. Verificar credenciales en `.env`.

### 2. "Error al crear reservación"
**Síntoma**: Al dar click en Reservar, sale un toast rojo de error.
**Causa Probable**:
- La mesa fue ganada por otro usuario en ese milisegundo.
- Datos inválidos (teléfono muy largo, etc).
**Solución**:
- Intentar nuevamente.
- Verificar validaciones en `reservaciones/assets/js/booking.js`.

### 3. El Bot de WhatsApp no responde
**Síntoma**: Mensajes se quedan en un tic o dos tics pero sin respuesta.
**Causa Probable**:
- Sesión de WPPConnect desconectada.
- Servidor caído.
**Solución**:
1. Verificar estado: `curl http://localhost:21465/api/rosa_mezcal_session/check-connection-session`
2. Si `status: DISCONNECTED`, re-escanear QR.
3. Reiniciar contenedor: `docker-compose restart wppconnect`

### 4. Admin no puede guardar mapa de mesas
**Síntoma**: Al dar click en "Guardar", no pasa nada o error.
**Causa Probable**:
- Permisos de escritura en BD.
- Error JS en `table-map.js`.
**Solución**:
- Verificar consola del navegador por errores JS.
- Verificar que la tabla `mesas` tenga permisos de escritura para el usuario de BD.

### 5. Docker no inicia
**Síntoma**: `docker-compose up` falla.
**Solución**:
- Verificar puertos ocupados (80, 3306, 6379).
- `docker-compose down --rmi local` y volver a construir.

---

## Logs Importantes

| Servicio | Ubicación | Comando Ver |
|----------|-----------|-------------|
| **Apache/PHP** | Container Stdout | `docker-compose logs -f web` |
| **WhatsApp** | Container Stdout | `docker-compose logs -f wppconnect` |
| **App Webhooks** | `reservaciones/logs/` | `tail -f reservaciones/logs/*.log` |
| **Supabase** | Dashboard Web | Ver panel de Supabase |

---

Si el problema persiste, contactar soporte con:
1. Descripción del error.
2. Captura de pantalla.
3. Logs relevantes.
