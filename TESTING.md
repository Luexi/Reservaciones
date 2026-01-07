# 🧪 Guía de Testing - Rosa Mezcal

Plan de pruebas para validar el correcto funcionamiento del sistema.

## 1. Pruebas Funcionales (Frontend Cliente)

| ID | Prueba | Pasos | Resultado Esperado | Estado |
|----|--------|-------|--------------------|--------|
| F01 | Carga Inicial | Entrar a `/reservaciones/` | Página carga < 2s, se ve formulario | ⬜ |
| F02 | Validación | Intentar enviar vacío | Mostrar mensaje "Campos requeridos" | ⬜ |
| F03 | Calendario | Seleccionar fecha pasada | No permitir selección | ⬜ |
| F04 | Disponibilidad | Seleccionar fecha válida | Cargar horas disponibles en select | ⬜ |
| F05 | Reserva Exitosa | Llenar todo y enviar | Redirección a confirmación con código | ⬜ |

## 2. Pruebas Funcionales (Admin)

| ID | Prueba | Pasos | Resultado Esperado | Estado |
|----|--------|-------|--------------------|--------|
| A01 | Login Correcto | User `admin` Pass `rosa2026` | Acceso a Dashboard | ⬜ |
| A02 | Login Incorrecto | Pass incorrecto | Mensaje de error, no acceso | ⬜ |
| A03 | Mapa de Mesas | Arrastrar mesa #1 | La mesa se mueve y guarda posición | ⬜ |
| A04 | Filtrar Reservas | Filtrar por "Hoy" | Mostrar solo reservas de hoy | ⬜ |
| A05 | Config Horario | Cambiar hora cierre | Nuevo horario activo en frontend | ⬜ |

## 3. Pruebas de Integración (Bots)

| ID | Prueba | Pasos | Resultado Esperado | Estado |
|----|--------|-------|--------------------|--------|
| B01 | WhatsApp Saludo | Enviar "Hola" | Bot responde menú principal | ⬜ |
| B02 | WhatsApp Flow | Seguir flujo "Reservar" | Bot pide datos secuencialmente | ⬜ |
| B03 | WPP Crear BD | Completar flujo | Reserva aparece en Admin Panel | ⬜ |
| B04 | Messenger | Enviar mensaje FB | Bot responde igual que WPP | ⬜ |

## 4. Pruebas de Carga y Seguridad

| ID | Prueba | Pasos | Resultado Esperado | Estado |
|----|--------|-------|--------------------|--------|
| S01 | SQL Injection | Input `' OR '1'='1` | Sistema sanitiza, no error SQL | ⬜ |
| S02 | XSS | Input `<script>alert(1)</script>` | Texto guardado plano, no ejecuta | ⬜ |
| P01 | Concurrencia | 10 usuarios reservan misma mesa | Solo 1 logra reservar, 9 error | ⬜ |

## Cómo ejecutar pruebas automatizadas (Futuro)

Se recomienda implementar Playwright para pruebas E2E automatizadas:

```bash
npx playwright test
```

(Scripts de prueba pendientes para fase 2)
