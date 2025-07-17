-- =====================================
-- DATOS INICIALES PARA EL PORTAL RAQCHI
-- =====================================

-- =====================================
-- CONFIGURACIONES BÁSICAS
-- =====================================
INSERT INTO configuraciones (clave, valor, tipo, descripcion, categoria, publico) VALUES
('sitio_nombre', 'Portal Digital Raqchi', 'texto', 'Nombre del sitio web', 'general', true),
('sitio_descripcion', 'Descubre la magia arqueológica de Raqchi con nuestro portal digital', 'texto', 'Descripción del sitio', 'general', true),
('sitio_email', 'info@raqchi.com', 'texto', 'Email de contacto principal', 'contacto', true),
('sitio_telefono', '+51 984 123 456', 'texto', 'Teléfono de contacto', 'contacto', true),
('sitio_direccion', 'San Pedro de Cacha, Canchis, Cusco', 'texto', 'Dirección física', 'contacto', true),
('horario_atencion', '{"lunes_viernes": "8:00 - 17:00", "sabados": "8:00 - 15:00", "domingos": "Cerrado"}', 'json', 'Horarios de atención', 'contacto', true),
('moneda_base', 'PEN', 'texto', 'Moneda base del sistema', 'pagos', false),
('impuesto_igv', '18', 'numero', 'Porcentaje de IGV', 'pagos', false),
('edad_nino_maximo', '12', 'numero', 'Edad máxima considerada como niño', 'tickets', true),
('capacidad_maxima_diaria', '500', 'numero', 'Capacidad máxima de visitantes por día', 'operaciones', false),
('dias_anticipacion_reserva', '60', 'numero', 'Días máximos de anticipación para reservas', 'operaciones', true),
('horas_limite_cancelacion', '24', 'numero', 'Horas límite para cancelar reserva', 'operaciones', true),
('paypal_sandbox', 'true', 'booleano', 'Usar PayPal en modo sandbox', 'pagos', false),
('email_verificacion_requerida', 'true', 'booleano', 'Requiere verificación de email para registro', 'usuarios', false),
('moderacion_resenas', 'true', 'booleano', 'Las reseñas requieren moderación', 'contenido', false)
ON CONFLICT (clave) DO UPDATE SET
    valor = EXCLUDED.valor,
    tipo = EXCLUDED.tipo,
    descripcion = EXCLUDED.descripcion,
    categoria = EXCLUDED.categoria,
    publico = EXCLUDED.publico,
    updated_at = NOW();

-- =====================================
-- USUARIO ADMINISTRADOR INICIAL
-- =====================================
INSERT INTO usuarios (
    id,
    nombre,
    email,
    password,
    rol,
    estado,
    fecha_verificacion,
    created_at
) VALUES (
    gen_random_uuid(),
    'Administrador Portal',
    'admin@raqchi.com',
    '$2y$10$x16VDiPDKDl6hlarHEiH3uEOzwyQIxHQk8LNfuuryLnY3C4G9bWZW', -- password actualizada
    'admin',
    'activo',
    NOW(),
    NOW()
)
ON CONFLICT (email) DO UPDATE SET
    nombre = EXCLUDED.nombre,
    rol = EXCLUDED.rol,
    estado = EXCLUDED.estado,
    updated_at = NOW();

-- =====================================
-- CATEGORÍAS DE SERVICIOS
-- =====================================
INSERT INTO categorias_servicios (nombre, descripcion, icono, color, orden) VALUES
('Visitas Arqueológicas', 'Explora el complejo arqueológico de Raqchi', 'icon-temple', '#8B4513', 1),
('Servicios de Guía', 'Guías especializados en historia y cultura', 'icon-guide', '#DAA520', 2),
('Talleres Culturales', 'Aprende sobre la cultura local y tradiciones', 'icon-craft', '#CD853F', 3),
('Tours Gastronómicos', 'Degusta la cocina tradicional de la región', 'icon-food', '#A0522D', 4),
('Actividades Familiares', 'Experiencias diseñadas para toda la familia', 'icon-family', '#DEB887', 5),
('Fotografía', 'Servicios especializados de fotografía', 'icon-camera', '#F4A460', 6);

-- =====================================
-- SERVICIOS PRINCIPALES
-- =====================================
INSERT INTO servicios (
    nombre,
    categoria_id,
    descripcion,
    descripcion_corta,
    precio_nacional,
    precio_extranjero,
    precio_estudiante,
    duracion_estimada,
    capacidad_maxima,
    edad_minima,
    incluye,
    no_incluye,
    requisitos,
    slug,
    destacado,
    orden
) VALUES
(
    'Visita al Templo de Wiracocha',
    (SELECT id FROM categorias_servicios WHERE nombre = 'Visitas Arqueológicas'),
    'Explora el majestuoso Templo de Wiracocha, una de las construcciones incas más impresionantes del Cusco. Conoce la historia, arquitectura y significado religioso de este sitio sagrado.',
    'Visita guiada al templo principal de Raqchi',
    15.00,
    30.00,
    8.00,
    120,
    50,
    0,
    '["Entrada al complejo arqueológico", "Mapa del sitio", "Información básica en español/inglés"]',
    '["Guía especializado", "Transporte", "Alimentación", "Seguro de viaje"]',
    '["Documento de identidad", "Calzado cómodo", "Protector solar"]',
    'visita-templo-wiracocha',
    true,
    1
),
(
    'Tour Guiado Especializado',
    (SELECT id FROM categorias_servicios WHERE nombre = 'Servicios de Guía'),
    'Recorrido completo con guía especializado en arqueología e historia inca. Incluye explicaciones detalladas sobre la construcción, uso ceremonial y importancia del sitio.',
    'Tour completo con guía arqueólogo certificado',
    50.00,
    80.00,
    35.00,
    180,
    25,
    8,
    '["Guía arqueólogo certificado", "Explicaciones en español/inglés", "Material educativo", "Entrada al complejo"]',
    '["Transporte", "Alimentación", "Bebidas", "Propinas"]',
    '["Reserva previa", "Documento de identidad", "Ropa cómoda", "Agua"]',
    'tour-guiado-especializado',
    true,
    2
),
(
    'Taller de Cerámica Tradicional',
    (SELECT id FROM categorias_servicios WHERE nombre = 'Talleres Culturales'),
    'Aprende las técnicas ancestrales de cerámica utilizadas por los habitantes prehispánicos de Raqchi. Crea tu propia pieza de cerámica bajo la guía de artesanos locales.',
    'Taller práctico de cerámica con artesanos locales',
    45.00,
    65.00,
    30.00,
    150,
    15,
    10,
    '["Materiales de cerámica", "Herramientas", "Instructor especializado", "Pieza terminada"]',
    '["Transporte", "Alimentación", "Materiales adicionales"]',
    '["Ropa que se pueda ensuciar", "Reserva previa", "Edad mínima 10 años"]',
    'taller-ceramica-tradicional',
    false,
    3
),
(
    'Experiencia Gastronómica Andina',
    (SELECT id FROM categorias_servicios WHERE nombre = 'Tours Gastronómicos'),
    'Degusta platos tradicionales preparados con ingredientes locales y técnicas ancestrales. Incluye demostración de cocina en horno de tierra (watia).',
    'Degustación de comida tradicional andina',
    60.00,
    90.00,
    40.00,
    120,
    20,
    0,
    '["Degustación de 5 platos", "Bebidas tradicionales", "Demostración de cocina", "Recetas para llevar"]',
    '["Transporte", "Bebidas alcohólicas", "Menú vegetariano (consultar)"]',
    '["Avisar alergias alimentarias", "Reserva previa"]',
    'experiencia-gastronomica-andina',
    true,
    4
),
(
    'Aventura Familiar en Raqchi',
    (SELECT id FROM categorias_servicios WHERE nombre = 'Actividades Familiares'),
    'Actividad diseñada especialmente para familias con niños. Incluye juegos educativos, búsqueda del tesoro arqueológico y talleres interactivos.',
    'Experiencia educativa y divertida para toda la familia',
    35.00,
    55.00,
    20.00,
    150,
    30,
    5,
    '["Actividades para niños", "Material educativo", "Guía familiar", "Entrada al complejo", "Certificado de explorador"]',
    '["Transporte", "Alimentación", "Souvenirs adicionales"]',
    '["Al menos un adulto responsable", "Niños mayores de 5 años"]',
    'aventura-familiar-raqchi',
    false,
    5
),
(
    'Sesión Fotográfica Profesional',
    (SELECT id FROM categorias_servicios WHERE nombre = 'Fotografía'),
    'Captura los mejores momentos de tu visita con un fotógrafo profesional. Incluye sesión de 1 hora y entrega digital de 20 fotografías editadas.',
    'Sesión fotográfica profesional en el sitio arqueológico',
    120.00,
    180.00,
    90.00,
    90,
    8,
    0,
    '["Fotógrafo profesional", "1 hora de sesión", "20 fotos editadas", "Entrega digital", "Fotos en alta resolución"]',
    '["Impresiones físicas", "Fotos adicionales", "Sesión extendida"]',
    '["Reserva previa", "Entrada al complejo (por separado)", "Cambio de ropa permitido"]',
    'sesion-fotografica-profesional',
    false,
    6
);

-- =====================================
-- HORARIOS DE SERVICIOS
-- =====================================
-- Eliminar horarios existentes antes de insertar nuevos
DELETE FROM horarios_servicios;

-- Horarios para visita básica (todos los días)
DO $$
DECLARE
    templo_servicio_id UUID;
    guiado_servicio_id UUID;
    ceramica_servicio_id UUID;
    gastronomico_servicio_id UUID;
    familiar_servicio_id UUID;
    foto_servicio_id UUID;
BEGIN
    -- Obtener IDs de servicios
    SELECT id INTO templo_servicio_id FROM servicios WHERE slug = 'visita-templo-wiracocha';
    SELECT id INTO guiado_servicio_id FROM servicios WHERE slug = 'tour-guiado-especializado';
    SELECT id INTO ceramica_servicio_id FROM servicios WHERE slug = 'taller-ceramica-tradicional';
    SELECT id INTO gastronomico_servicio_id FROM servicios WHERE slug = 'experiencia-gastronomica-andina';
    SELECT id INTO familiar_servicio_id FROM servicios WHERE slug = 'aventura-familiar-raqchi';
    SELECT id INTO foto_servicio_id FROM servicios WHERE slug = 'sesion-fotografica-profesional';

    -- Horarios para visita básica (todos los días)
    INSERT INTO horarios_servicios (servicio_id, dia_semana, hora_inicio, hora_fin, capacidad_maxima) VALUES
    (templo_servicio_id, 0, '08:00', '17:00', 50),
    (templo_servicio_id, 1, '08:00', '17:00', 50),
    (templo_servicio_id, 2, '08:00', '17:00', 50),
    (templo_servicio_id, 3, '08:00', '17:00', 50),
    (templo_servicio_id, 4, '08:00', '17:00', 50),
    (templo_servicio_id, 5, '08:00', '17:00', 50),
    (templo_servicio_id, 6, '08:00', '17:00', 50);

    -- Horarios para tour guiado (martes a sábado, mañana)
    INSERT INTO horarios_servicios (servicio_id, dia_semana, hora_inicio, hora_fin, capacidad_maxima) VALUES
    (guiado_servicio_id, 2, '09:00', '12:00', 25),
    (guiado_servicio_id, 3, '09:00', '12:00', 25),
    (guiado_servicio_id, 4, '09:00', '12:00', 25),
    (guiado_servicio_id, 5, '09:00', '12:00', 25),
    (guiado_servicio_id, 6, '09:00', '12:00', 25);

    -- Horarios para tour guiado (martes a sábado, tarde)
    INSERT INTO horarios_servicios (servicio_id, dia_semana, hora_inicio, hora_fin, capacidad_maxima) VALUES
    (guiado_servicio_id, 2, '14:00', '17:00', 25),
    (guiado_servicio_id, 3, '14:00', '17:00', 25),
    (guiado_servicio_id, 4, '14:00', '17:00', 25),
    (guiado_servicio_id, 5, '14:00', '17:00', 25),
    (guiado_servicio_id, 6, '14:00', '17:00', 25);

    -- Horarios para taller de cerámica (miércoles, viernes, sábado)
    INSERT INTO horarios_servicios (servicio_id, dia_semana, hora_inicio, hora_fin, capacidad_maxima) VALUES
    (ceramica_servicio_id, 3, '10:00', '12:30', 15),
    (ceramica_servicio_id, 5, '10:00', '12:30', 15),
    (ceramica_servicio_id, 6, '10:00', '12:30', 15);

    -- Horarios para experiencia gastronómica (jueves a domingo)
    INSERT INTO horarios_servicios (servicio_id, dia_semana, hora_inicio, hora_fin, capacidad_maxima) VALUES
    (gastronomico_servicio_id, 4, '12:00', '14:00', 20),
    (gastronomico_servicio_id, 5, '12:00', '14:00', 20),
    (gastronomico_servicio_id, 6, '12:00', '14:00', 20),
    (gastronomico_servicio_id, 0, '12:00', '14:00', 20);

    -- Horarios para aventura familiar (sábados y domingos)
    INSERT INTO horarios_servicios (servicio_id, dia_semana, hora_inicio, hora_fin, capacidad_maxima) VALUES
    (familiar_servicio_id, 6, '09:00', '11:30', 30),
    (familiar_servicio_id, 0, '09:00', '11:30', 30);

    -- Horarios para fotografía (todos los días, múltiples horarios)
    INSERT INTO horarios_servicios (servicio_id, dia_semana, hora_inicio, hora_fin, capacidad_maxima) VALUES
    (foto_servicio_id, 0, '09:00', '10:30', 8), (foto_servicio_id, 0, '11:00', '12:30', 8), (foto_servicio_id, 0, '14:00', '15:30', 8), (foto_servicio_id, 0, '16:00', '17:30', 8),
    (foto_servicio_id, 1, '09:00', '10:30', 8), (foto_servicio_id, 1, '11:00', '12:30', 8), (foto_servicio_id, 1, '14:00', '15:30', 8), (foto_servicio_id, 1, '16:00', '17:30', 8),
    (foto_servicio_id, 2, '09:00', '10:30', 8), (foto_servicio_id, 2, '11:00', '12:30', 8), (foto_servicio_id, 2, '14:00', '15:30', 8), (foto_servicio_id, 2, '16:00', '17:30', 8),
    (foto_servicio_id, 3, '09:00', '10:30', 8), (foto_servicio_id, 3, '11:00', '12:30', 8), (foto_servicio_id, 3, '14:00', '15:30', 8), (foto_servicio_id, 3, '16:00', '17:30', 8),
    (foto_servicio_id, 4, '09:00', '10:30', 8), (foto_servicio_id, 4, '11:00', '12:30', 8), (foto_servicio_id, 4, '14:00', '15:30', 8), (foto_servicio_id, 4, '16:00', '17:30', 8),
    (foto_servicio_id, 5, '09:00', '10:30', 8), (foto_servicio_id, 5, '11:00', '12:30', 8), (foto_servicio_id, 5, '14:00', '15:30', 8), (foto_servicio_id, 5, '16:00', '17:30', 8),
    (foto_servicio_id, 6, '09:00', '10:30', 8), (foto_servicio_id, 6, '11:00', '12:30', 8), (foto_servicio_id, 6, '14:00', '15:30', 8), (foto_servicio_id, 6, '16:00', '17:30', 8);
END $$;

-- =====================================
-- CUPONES DE EJEMPLO
-- =====================================
INSERT INTO cupones (codigo, nombre, descripcion, tipo, valor, fecha_inicio, fecha_fin, usos_maximos) VALUES
('BIENVENIDO2025', 'Cupón de Bienvenida 2025', 'Descuento especial para nuevos visitantes', 'porcentaje', 15.00, '2025-01-01', '2025-12-31', 100),
('FAMILIA20', 'Descuento Familiar', 'Descuento especial para actividades familiares', 'porcentaje', 20.00, '2025-01-01', '2025-12-31', NULL),
('ESTUDIANTE10', 'Descuento Estudiante', 'Descuento adicional para estudiantes', 'monto_fijo', 10.00, '2025-01-01', '2025-12-31', NULL),
('VERANO2025', 'Promoción de Verano', 'Descuento especial temporada alta', 'porcentaje', 10.00, '2025-06-01', '2025-08-31', 200);

-- =====================================
-- RESEÑAS DE EJEMPLO (APROBADAS)
-- =====================================
INSERT INTO resenas (
    servicio_id,
    calificacion,
    titulo,
    comentario,
    verificada,
    aprobada,
    created_at
) VALUES
(
    (SELECT id FROM servicios WHERE slug = 'visita-templo-wiracocha'),
    5,
    'Experiencia increíble',
    'El templo de Wiracocha es impresionante. La vista y la historia del lugar son fascinantes. Totalmente recomendado para conocer más sobre la cultura inca.',
    true,
    true,
    '2024-12-13 10:00:00'
),
(
    (SELECT id FROM servicios WHERE slug = 'tour-guiado-especializado'),
    5,
    'Guía excepcional',
    'Nuestro guía tenía un conocimiento profundo de la historia. Las explicaciones fueron muy claras y nos ayudó a entender la importancia del sitio.',
    true,
    true,
    '2024-12-18 14:30:00'
),
(
    (SELECT id FROM servicios WHERE slug = 'taller-ceramica-tradicional'),
    4,
    'Taller muy interesante',
    'Aprender las técnicas tradicionales fue una experiencia única. Los instructores son muy pacientes y conocedores.',
    true,
    true,
    '2024-12-23 11:15:00'
),
(
    (SELECT id FROM servicios WHERE slug = 'experiencia-gastronomica-andina'),
    5,
    'Comida deliciosa',
    'Los sabores tradicionales son únicos. La demostración de cocina fue muy educativa y la comida estaba exquisita.',
    true,
    true,
    '2024-12-28 16:45:00'
),
(
    (SELECT id FROM servicios WHERE slug = 'aventura-familiar-raqchi'),
    4,
    'Perfecto para niños',
    'Mis hijos se divirtieron mucho con la búsqueda del tesoro. Las actividades están bien pensadas para mantener su interés.',
    true,
    true,
    '2025-01-02 09:20:00'
),
(
    (SELECT id FROM servicios WHERE slug = 'visita-templo-wiracocha'),
    4,
    'Vale la pena la visita al templo',
    'Un lugar lleno de historia y energía. El estado de conservación es bueno y se aprende mucho sobre la cultura inca.',
    true,
    true,
    '2025-01-07 13:10:00'
);

-- =====================================
-- DATOS INICIALES PARA LAS NUEVAS TABLAS
-- =====================================

-- Votos de ejemplo para las reseñas
INSERT INTO votos_resenas (resena_id, ip_address, tipo, created_at) VALUES
((SELECT id FROM resenas WHERE titulo = 'Experiencia increíble' AND servicio_id = (SELECT id FROM servicios WHERE slug = 'visita-templo-wiracocha') LIMIT 1), '192.168.1.100', 'util', '2025-01-10 10:00:00'),
((SELECT id FROM resenas WHERE titulo = 'Experiencia increíble' AND servicio_id = (SELECT id FROM servicios WHERE slug = 'visita-templo-wiracocha') LIMIT 1), '192.168.1.101', 'util', '2025-01-10 11:00:00'),
((SELECT id FROM resenas WHERE titulo = 'Guía excepcional' AND servicio_id = (SELECT id FROM servicios WHERE slug = 'tour-guiado-especializado') LIMIT 1), '192.168.1.102', 'util', '2025-01-11 09:00:00'),
((SELECT id FROM resenas WHERE titulo = 'Taller muy interesante' AND servicio_id = (SELECT id FROM servicios WHERE slug = 'taller-ceramica-tradicional') LIMIT 1), '192.168.1.103', 'util', '2025-01-12 14:00:00'),
((SELECT id FROM resenas WHERE titulo = 'Comida deliciosa' AND servicio_id = (SELECT id FROM servicios WHERE slug = 'experiencia-gastronomica-andina') LIMIT 1), '192.168.1.104', 'util', '2025-01-13 12:00:00'),
((SELECT id FROM resenas WHERE titulo = 'Comida deliciosa' AND servicio_id = (SELECT id FROM servicios WHERE slug = 'experiencia-gastronomica-andina') LIMIT 1), '192.168.1.105', 'util', '2025-01-13 13:00:00');

-- Notificaciones de ejemplo
INSERT INTO notificaciones (usuario_id, tipo, titulo, mensaje, datos, created_at) VALUES
((SELECT id FROM usuarios WHERE email = 'admin@raqchi.com'), 'system', 'Sistema Inicializado', 'El portal digital de Raqchi ha sido configurado correctamente.', '{"version": "1.0", "fecha_instalacion": "2025-07-17"}', NOW()),
((SELECT id FROM usuarios WHERE email = 'admin@raqchi.com'), 'resena_nueva', 'Nueva Reseña Pendiente', 'Se ha recibido una nueva reseña que requiere moderación.', '{"resena_id": 1, "calificacion": 5}', NOW() - INTERVAL '1 day');

-- Métricas iniciales
INSERT INTO metricas (fecha, metrica, valor, metadata) VALUES
('2025-07-17', 'visitas_servicios', 156.00, '{"servicio_mas_visitado": "visita-templo-wiracocha"}'),
('2025-07-17', 'reservas_realizadas', 12.00, '{"tipo_predominante": "nacional"}'),
('2025-07-17', 'ingresos_diarios', 2350.00, '{"moneda": "PEN"}'),
('2025-07-16', 'visitas_servicios', 142.00, '{"servicio_mas_visitado": "tour-guiado-especializado"}'),
('2025-07-16', 'reservas_realizadas', 8.00, '{"tipo_predominante": "extranjero"}'),
('2025-07-16', 'ingresos_diarios', 1890.00, '{"moneda": "PEN"}');

-- Respuestas de ejemplo a reseñas
INSERT INTO respuestas_resenas (resena_id, usuario_id, contenido, created_at) VALUES
((SELECT id FROM resenas WHERE titulo = 'Experiencia increíble' AND servicio_id = (SELECT id FROM servicios WHERE slug = 'visita-templo-wiracocha') LIMIT 1), (SELECT id FROM usuarios WHERE email = 'admin@raqchi.com'), '¡Muchas gracias por tu comentario! Nos alegra saber que disfrutaste de tu visita al templo de Wiracocha. Esperamos verte pronto nuevamente.', '2025-01-14 10:00:00'),
((SELECT id FROM resenas WHERE titulo = 'Guía excepcional' AND servicio_id = (SELECT id FROM servicios WHERE slug = 'tour-guiado-especializado') LIMIT 1), (SELECT id FROM usuarios WHERE email = 'admin@raqchi.com'), 'Agradecemos mucho tu reconocimiento hacia nuestro equipo de guías. Trabajamos constantemente para brindar la mejor experiencia educativa posible.', '2025-01-15 11:30:00');

-- =====================================
-- LOGS DE AUDITORÍA INICIALES
-- =====================================
INSERT INTO logs_auditoria (accion, tabla_afectada, datos_nuevos, ip_address) VALUES
('SYSTEM_INIT', 'configuraciones', '{"mensaje": "Configuraciones iniciales cargadas"}', '127.0.0.1'),
('SYSTEM_INIT', 'categorias_servicios', '{"mensaje": "Categorías de servicios creadas"}', '127.0.0.1'),
('SYSTEM_INIT', 'servicios', '{"mensaje": "Servicios iniciales creados"}', '127.0.0.1'),
('SYSTEM_INIT', 'cupones', '{"mensaje": "Cupones promocionales creados"}', '127.0.0.1');

-- =====================================
-- FIN DE DATOS INICIALES
-- =====================================
