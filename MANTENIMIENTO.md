# 🛠️ Mantenimiento y Soporte - Rosa Mezcal

Guía para mantener el sistema operando correctamente a largo plazo.

## Tareas Diarias

1. **Revisar Logs de Errores**
   - Verificar si hay errores recurrentes en `reservaciones/logs/`
   - Comando: `tail -f reservaciones/logs/whatsapp_webhook.log`

2. **Verificar Bots**
   - Enviar un mensaje de prueba ("Hola") al bot de WhatsApp y Messenger para asegurar que responden.

## Tareas Semanales

1. **Backups de Base de Datos**
   - Aunque Supabase tiene backups automáticos, se recomienda exportar datos críticos.
   - Ver script en `DEPLOYMENT.md`.

2. **Revisar Espacio en Disco**
   - Ejecutar `df -h` en el servidor para asegurar que los logs no han llenado el disco.

3. **Reiniciar Servicios (Opcional)**
   - Un reinicio preventivo puede liberar memoria.
   - `docker-compose restart`

## Tareas Mensuales

1. **Actualizaciones de Seguridad**
   - Servidor: `apt update && apt upgrade`
   - Imágenes Docker: `docker-compose pull && docker-compose up -d`

2. **Limpieza de Base de Datos**
   - Archivar reservas muy antiguas (> 1 año) si la tabla crece demasiado.

## Procedimientos de Emergencia

### El sistema está caído (Error 500)
1. Conectar por SSH al servidor.
2. Verificar contenedores: `docker-compose ps`
3. Ver logs de error: `docker-compose logs web`
4. Si es error de BD, verificar conexión a Supabase.
5. Reiniciar: `docker-compose down && docker-compose up -d`

### WhatsApp desconectado
1. Ir a `http://localhost:21465/api/rosa_mezcal_session/start-session`
2. Escanear el QR nuevamente.

### Base de Datos inaccesible
1. Entrar al panel de Supabase.
2. Verificar estado del proyecto (Active/Paused).
3. Si fue pausado por inactividad, reactivarlo.

## Contactos de Soporte

- **Desarrollador Principal**: [Tu Nombre/Empresa]
- **Hosting Supabase**: support@supabase.com
- **Hosting VPS**: support@[digitalocean/aws].com

---

**Historial de Mantenimiento**

| Fecha | Acción | Realizado por |
|-------|--------|---------------|
| 2026-01-07 | Instalación inicial | Luis |
| | | |
