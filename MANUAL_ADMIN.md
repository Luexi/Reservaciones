# 👨‍💼 Manual de Administrador - Rosa Mezcal

Este manual describe el uso del Panel de Administración del sistema de reservaciones.

## Acceso al Sistema

1. Diríjase a: `https://rosamezcal.mx/reservaciones/admin/`
2. Ingrese sus credenciales:
   - **Usuario**: `admin`
   - **Contraseña**: (la que haya configurado en la instalación)

## 1. Dashboard (Tablero Principal)

Al ingresar, verá el Dashboard con un resumen de la operación de hoy:

- **Tarjetas de Estadísticas**:
  - **Reservas Hoy**: Total de citas para el día.
  - **Personas Esperadas**: Suma de comensales.
  - **Pendientes**: Reservas que requieren confirmación manual (si aplica).
  - **Próximas (2h)**: Reservas en las próximas 2 horas.

- **Reservaciones Próximas**: Lista de las citas más cercanas. Puede usar el botón **"✅ Llegó"** para marcar rápidamente que el cliente ya está en el restaurante.

## 2. Gestión de Reservaciones

En la sección **"Reservaciones"** puede ver y gestionar toda la lista.

### Filtrado
Use los controles superiores para filtrar por:
- **Fecha**: Ver reservas de un día específico.
- **Estado**: (Pendiente, Confirmada, Llegó, No llegó, Cancelada).

### Estados
- 🟡 **Pendiente**: El cliente solicitó, falta confirmar.
- 🟢 **Confirmada**: La mesa está asegurada.
- 🔵 **Llegó**: El cliente ya está en el local.
- 🔴 **No Llegó**: "No-show".
- ⚫ **Cancelada**: Reserva anulada.

### Acciones
- **Ver**: Detalles completos (comentarios, ocasión especial).
- **Editar**: Cambiar mesa, hora o personas.
- **Cancelar**: Anular la reserva y liberar la mesa.

## 3. Mapa de Mesas (Tables)

Esta es una herramienta visual para organizar su restaurante.

### Funciones:
- **Arrastrar y Soltar**: Haga click y arrastre cualquier mesa para cambiar su posición en el plano.
- **Guardar**: Click en **"💾 Guardar Disposición"** para aplicar los cambios en el sistema de reservas.
- **Agregar Mesa**: Use el botón **"➕ Agregar Mesa"** para crear nuevos espacios.
- **Click en Mesa**: Para ver detalles rápidos o editar su capacidad.

### Colores del Mapa
- 🟢 **Verde**: Mesa disponible ahora.
- 🟡 **Amarillo**: Mesa reservada próximamente.
- 🔴 **Rojo**: Mesa ocupada actualmente.

## 4. Configuración (Config)

Ajuste el comportamiento del sistema sin tocar código.

### Horarios
Defina la hora de apertura y cierre para cada día de la semana.
- Use la casilla **"Cerrado"** para bloquear días completos (ej. Lunes cerrados).

### Reglas de Negocio
- **Duración de Reserva**: Tiempo que una mesa permanece ocupada (default: 2 hrs).
- **Intervalo**: Cada cuánto se ofrecen horarios (15, 30, 60 min).
- **Confirmación Automática**: Si está activo, las reservas web se confirman inmediatamente. Si está apagado, entran como "Pendientes".

## Preguntas Frecuentes

**¿Cómo bloqueo una fecha específica (feriado)?**
Actualmente debe cerrar manualmente el día en "Configuración" o crear una "reserva fantasma" que ocupe todo el restaurante. (Función de bloqueo por fecha en desarrollo para v2).

**¿Cómo cambio mi contraseña?**
Por seguridad, la contraseña se cambia a nivel de servidor o código. Contacte al soporte técnico.

**¿Qué hago si el sistema está lento?**
Verifique su conexión a internet. Si persiste, contacte a soporte para reiniciar los servicios.

---

**Soporte Técnico**: contacto@rosamezcal.mx
