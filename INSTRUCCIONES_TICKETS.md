# Instrucciones para configurar el sistema de tickets

## 1. Ejecutar el SQL en Supabase

Para que el sistema de tickets funcione correctamente, necesitas ejecutar el archivo SQL en la base de datos de Supabase:

1. Ve al panel de Supabase (supabase.com)
2. Accede a tu proyecto
3. Ve a la sección "SQL Editor"
4. Abre el archivo: `setup/tickets_schema.sql`
5. Copia y pega todo el contenido en el SQL Editor
6. Ejecuta el script completo

## 2. Verificar que las tablas se crearon

Después de ejecutar el SQL, deberías tener:
- Tabla `tipos_tickets` con datos de ejemplo
- Tabla `tickets` para almacenar las compras
- Funciones y triggers para generar códigos automáticamente
- Políticas RLS configuradas

## 3. Probar el sistema

Una vez ejecutado el SQL:

1. Ve a: http://localhost/Racchi/public/tickets.php
   - Deberías ver los tipos de tickets disponibles
   - Podrás crear tickets de prueba

2. Ve a: http://localhost/Racchi/admin/tickets.php
   - Deberías ver estadísticas de tickets
   - Podrás validar códigos de tickets
   - Podrás cambiar estados de tickets

## 4. Funcionalidades implementadas

### Página Pública (public/tickets.php):
- Carga tipos de tickets desde Supabase
- Formulario de compra con validaciones
- Cálculo automático de precios (general, estudiante, grupo)
- Generación automática de códigos de ticket
- Modal responsive para el proceso de compra

### Panel Administrativo (admin/tickets.php):
- Estadísticas en tiempo real
- Validador de códigos de ticket
- Lista de tickets con filtros
- Cambio de estados (pendiente → pagado → usado)
- Funcionalidades de cancelación

### API (api/tickets.php):
- GET /api/tickets.php?action=get_types - Obtener tipos de tickets
- GET /api/tickets.php?action=get_tickets - Listar tickets (admin)
- POST /api/tickets.php action=create_ticket - Crear nuevo ticket
- POST /api/tickets.php action=validate_ticket - Validar ticket (admin)
- POST /api/tickets.php action=update_ticket_status - Cambiar estado (admin)

## 5. Estados de los tickets

- **pendiente**: Ticket creado pero no pagado
- **pagado**: Ticket pagado, listo para usar
- **usado**: Ticket ya utilizado para el ingreso
- **cancelado**: Ticket cancelado

## 6. Códigos de ticket

Los códigos se generan automáticamente con el formato:
- RQ + Año + 6 dígitos aleatorios
- Ejemplo: RQ2025123456

¡El sistema está listo para usar una vez que ejecutes el SQL!
