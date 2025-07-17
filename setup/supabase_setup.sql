-- =====================================
-- CONFIGURACIÓN DE BASE DE DATOS SUPABASE
-- Portal Digital de Raqchi
-- =====================================

-- Habilitar Row Level Security (RLS) por defecto
ALTER DEFAULT PRIVILEGES REVOKE EXECUTE ON FUNCTIONS FROM PUBLIC;

-- =====================================
-- 1. TABLA DE USUARIOS
-- =====================================
CREATE TABLE IF NOT EXISTS public.usuarios (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol VARCHAR(20) DEFAULT 'cliente' CHECK (rol IN ('admin', 'vendedor', 'cliente')),
    estado VARCHAR(20) DEFAULT 'pendiente' CHECK (estado IN ('activo', 'pendiente', 'suspendido', 'inactivo')),
    telefono VARCHAR(20),
    fecha_nacimiento DATE,
    nacionalidad VARCHAR(50),
    documento_tipo VARCHAR(20) CHECK (documento_tipo IN ('dni', 'ce', 'pasaporte')),
    documento_numero VARCHAR(50),
    direccion TEXT,
    fecha_registro TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    fecha_ultimo_acceso TIMESTAMP WITH TIME ZONE,
    token_verificacion VARCHAR(255),
    fecha_verificacion TIMESTAMP WITH TIME ZONE,
    reset_token VARCHAR(255),
    reset_token_expira TIMESTAMP WITH TIME ZONE,
    ip_registro INET,
    preferencias JSONB DEFAULT '{}',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Índices para usuarios
CREATE INDEX IF NOT EXISTS idx_usuarios_email ON usuarios(email);
CREATE INDEX IF NOT EXISTS idx_usuarios_rol ON usuarios(rol);
CREATE INDEX IF NOT EXISTS idx_usuarios_estado ON usuarios(estado);
CREATE INDEX IF NOT EXISTS idx_usuarios_fecha_registro ON usuarios(fecha_registro);

-- =====================================
-- 2. TABLA DE CATEGORÍAS DE SERVICIOS
-- =====================================
CREATE TABLE IF NOT EXISTS public.categorias_servicios (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    icono VARCHAR(50),
    color VARCHAR(7), -- Color hex
    orden INTEGER DEFAULT 0,
    activo BOOLEAN DEFAULT true,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- =====================================
-- 3. TABLA DE SERVICIOS
-- =====================================
CREATE TABLE IF NOT EXISTS public.servicios (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    categoria_id UUID REFERENCES categorias_servicios(id) ON DELETE SET NULL,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    descripcion_corta VARCHAR(255),
    precio_nacional DECIMAL(10,2) DEFAULT 0,
    precio_extranjero DECIMAL(10,2) DEFAULT 0,
    precio_estudiante DECIMAL(10,2) DEFAULT 0,
    duracion_estimada INTEGER, -- en minutos
    capacidad_maxima INTEGER DEFAULT 50,
    edad_minima INTEGER DEFAULT 0,
    incluye JSONB DEFAULT '[]', -- Array de lo que incluye
    no_incluye JSONB DEFAULT '[]', -- Array de lo que no incluye
    requisitos JSONB DEFAULT '[]', -- Array de requisitos
    imagenes JSONB DEFAULT '[]', -- Array de URLs de imágenes
    disponible BOOLEAN DEFAULT true,
    destacado BOOLEAN DEFAULT false,
    orden INTEGER DEFAULT 0,
    meta_title VARCHAR(200),
    meta_description VARCHAR(300),
    slug VARCHAR(150) UNIQUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Índices para servicios
CREATE INDEX IF NOT EXISTS idx_servicios_categoria ON servicios(categoria_id);
CREATE INDEX IF NOT EXISTS idx_servicios_disponible ON servicios(disponible);
CREATE INDEX IF NOT EXISTS idx_servicios_destacado ON servicios(destacado);
CREATE INDEX IF NOT EXISTS idx_servicios_slug ON servicios(slug);

-- =====================================
-- 4. TABLA DE HORARIOS DE SERVICIOS
-- =====================================
CREATE TABLE IF NOT EXISTS public.horarios_servicios (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    servicio_id UUID REFERENCES servicios(id) ON DELETE CASCADE,
    dia_semana INTEGER CHECK (dia_semana >= 0 AND dia_semana <= 6), -- 0=Domingo, 6=Sábado
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    capacidad_maxima INTEGER DEFAULT 50,
    precio_modificador DECIMAL(5,2) DEFAULT 1.00, -- Multiplicador del precio base
    activo BOOLEAN DEFAULT true,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- =====================================
-- 5. TABLA DE RESERVAS/TICKETS
-- =====================================
CREATE TABLE IF NOT EXISTS public.reservas (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    codigo_reserva VARCHAR(20) UNIQUE NOT NULL,
    usuario_id UUID REFERENCES usuarios(id) ON DELETE SET NULL,
    servicio_id UUID REFERENCES servicios(id) ON DELETE RESTRICT,
    fecha_visita DATE NOT NULL,
    hora_visita TIME,
    cantidad_adultos_nacional INTEGER DEFAULT 0,
    cantidad_adultos_extranjero INTEGER DEFAULT 0,
    cantidad_estudiantes INTEGER DEFAULT 0,
    cantidad_ninos INTEGER DEFAULT 0,
    precio_total DECIMAL(10,2) NOT NULL,
    descuento DECIMAL(10,2) DEFAULT 0,
    precio_final DECIMAL(10,2) NOT NULL,
    estado VARCHAR(20) DEFAULT 'pendiente' CHECK (estado IN ('pendiente', 'confirmada', 'pagada', 'utilizada', 'cancelada', 'expirada')),
    metodo_pago VARCHAR(30),
    referencia_pago VARCHAR(100),
    fecha_pago TIMESTAMP WITH TIME ZONE,
    datos_contacto JSONB DEFAULT '{}', -- Datos del contacto principal
    datos_visitantes JSONB DEFAULT '[]', -- Array con datos de visitantes
    observaciones TEXT,
    codigo_qr TEXT, -- Base64 del QR code
    usado_en TIMESTAMP WITH TIME ZONE,
    usado_por UUID REFERENCES usuarios(id), -- Staff que validó el ticket
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Índices para reservas
CREATE INDEX IF NOT EXISTS idx_reservas_codigo ON reservas(codigo_reserva);
CREATE INDEX IF NOT EXISTS idx_reservas_usuario ON reservas(usuario_id);
CREATE INDEX IF NOT EXISTS idx_reservas_servicio ON reservas(servicio_id);
CREATE INDEX IF NOT EXISTS idx_reservas_fecha_visita ON reservas(fecha_visita);
CREATE INDEX IF NOT EXISTS idx_reservas_estado ON reservas(estado);
CREATE INDEX IF NOT EXISTS idx_reservas_fecha_pago ON reservas(fecha_pago);

-- =====================================
-- 6. TABLA DE CUPONES/DESCUENTOS
-- =====================================
CREATE TABLE IF NOT EXISTS public.cupones (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    tipo VARCHAR(20) CHECK (tipo IN ('porcentaje', 'monto_fijo')),
    valor DECIMAL(10,2) NOT NULL,
    monto_minimo DECIMAL(10,2) DEFAULT 0,
    usos_maximos INTEGER,
    usos_actuales INTEGER DEFAULT 0,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    servicios_aplicables JSONB DEFAULT '[]', -- Array de IDs de servicios
    usuarios_aplicables JSONB DEFAULT '[]', -- Array de IDs de usuarios
    activo BOOLEAN DEFAULT true,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- =====================================
-- 7. TABLA DE RESEÑAS/COMENTARIOS
-- =====================================
CREATE TABLE IF NOT EXISTS public.resenas (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    usuario_id UUID REFERENCES usuarios(id) ON DELETE SET NULL,
    servicio_id UUID REFERENCES servicios(id) ON DELETE CASCADE,
    reserva_id UUID REFERENCES reservas(id) ON DELETE SET NULL,
    calificacion INTEGER CHECK (calificacion >= 1 AND calificacion <= 5),
    titulo VARCHAR(150),
    comentario TEXT,
    fotos JSONB DEFAULT '[]', -- Array de URLs de fotos
    verificada BOOLEAN DEFAULT false, -- Si viene de una reserva confirmada
    aprobada BOOLEAN DEFAULT false, -- Moderación
    respuesta_admin TEXT,
    fecha_respuesta TIMESTAMP WITH TIME ZONE,
    likes INTEGER DEFAULT 0,
    dislikes INTEGER DEFAULT 0,
    reportes INTEGER DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Índices para reseñas
CREATE INDEX IF NOT EXISTS idx_resenas_usuario ON resenas(usuario_id);
CREATE INDEX IF NOT EXISTS idx_resenas_servicio ON resenas(servicio_id);
CREATE INDEX IF NOT EXISTS idx_resenas_calificacion ON resenas(calificacion);
CREATE INDEX IF NOT EXISTS idx_resenas_aprobada ON resenas(aprobada);

-- =====================================
-- 8. TABLA DE CONFIGURACIONES
-- =====================================
CREATE TABLE IF NOT EXISTS public.configuraciones (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    clave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT,
    tipo VARCHAR(20) DEFAULT 'texto' CHECK (tipo IN ('texto', 'numero', 'booleano', 'json')),
    descripcion TEXT,
    categoria VARCHAR(50) DEFAULT 'general',
    publico BOOLEAN DEFAULT false, -- Si es accesible desde el frontend
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- =====================================
-- 9. TABLA DE LOGS/AUDITORÍA
-- =====================================
CREATE TABLE IF NOT EXISTS public.logs_auditoria (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    usuario_id UUID REFERENCES usuarios(id) ON DELETE SET NULL,
    accion VARCHAR(100) NOT NULL,
    tabla_afectada VARCHAR(50),
    registro_id UUID,
    datos_anteriores JSONB,
    datos_nuevos JSONB,
    ip_address INET,
    user_agent TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Índices para logs
CREATE INDEX IF NOT EXISTS idx_logs_usuario ON logs_auditoria(usuario_id);
CREATE INDEX IF NOT EXISTS idx_logs_accion ON logs_auditoria(accion);
CREATE INDEX IF NOT EXISTS idx_logs_tabla ON logs_auditoria(tabla_afectada);
CREATE INDEX IF NOT EXISTS idx_logs_fecha ON logs_auditoria(created_at);

-- =====================================
-- 10. TABLA DE ARCHIVOS/MEDIOS
-- =====================================
CREATE TABLE IF NOT EXISTS public.archivos (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    nombre_original VARCHAR(255) NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL,
    ruta VARCHAR(500) NOT NULL,
    url_publica VARCHAR(500),
    tipo_mime VARCHAR(100),
    tamano INTEGER, -- en bytes
    tipo VARCHAR(50) CHECK (tipo IN ('imagen', 'documento', 'video', 'audio', 'otro')),
    entidad_tipo VARCHAR(50), -- servicio, usuario, resena, etc.
    entidad_id UUID,
    es_principal BOOLEAN DEFAULT false,
    alt_text VARCHAR(255),
    titulo VARCHAR(255),
    descripcion TEXT,
    metadata JSONB DEFAULT '{}',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Índices para archivos
CREATE INDEX IF NOT EXISTS idx_archivos_entidad ON archivos(entidad_tipo, entidad_id);
CREATE INDEX IF NOT EXISTS idx_archivos_tipo ON archivos(tipo);
CREATE INDEX IF NOT EXISTS idx_archivos_principal ON archivos(es_principal);

-- =====================================
-- TRIGGERS PARA UPDATED_AT
-- =====================================

-- Función para actualizar updated_at
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Triggers para updated_at (eliminar si existen y recrear)
DROP TRIGGER IF EXISTS update_usuarios_updated_at ON usuarios;
CREATE TRIGGER update_usuarios_updated_at BEFORE UPDATE ON usuarios FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS update_categorias_servicios_updated_at ON categorias_servicios;
CREATE TRIGGER update_categorias_servicios_updated_at BEFORE UPDATE ON categorias_servicios FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS update_servicios_updated_at ON servicios;
CREATE TRIGGER update_servicios_updated_at BEFORE UPDATE ON servicios FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS update_reservas_updated_at ON reservas;
CREATE TRIGGER update_reservas_updated_at BEFORE UPDATE ON reservas FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS update_cupones_updated_at ON cupones;
CREATE TRIGGER update_cupones_updated_at BEFORE UPDATE ON cupones FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS update_resenas_updated_at ON resenas;
CREATE TRIGGER update_resenas_updated_at BEFORE UPDATE ON resenas FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS update_configuraciones_updated_at ON configuraciones;
CREATE TRIGGER update_configuraciones_updated_at BEFORE UPDATE ON configuraciones FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- =====================================
-- RLS (ROW LEVEL SECURITY) POLICIES - CONFIGURACIÓN SIMPLIFICADA
-- =====================================

-- IMPORTANTE: Durante desarrollo, deshabilitar RLS en tablas principales
-- para evitar recursión infinita. En producción se pueden habilitar políticas específicas.

-- Deshabilitar RLS en tablas que causan recursión
ALTER TABLE usuarios DISABLE ROW LEVEL SECURITY;
ALTER TABLE reservas DISABLE ROW LEVEL SECURITY;

-- Habilitar RLS solo en tablas específicas
ALTER TABLE resenas ENABLE ROW LEVEL SECURITY;
ALTER TABLE logs_auditoria ENABLE ROW LEVEL SECURITY;

-- Políticas simples para reseñas (sin recursión)
DROP POLICY IF EXISTS "resenas_select_approved" ON resenas;
CREATE POLICY "resenas_select_approved" ON resenas FOR SELECT USING (aprobada = true OR aprobada IS NULL);

DROP POLICY IF EXISTS "resenas_insert_public" ON resenas;
CREATE POLICY "resenas_insert_public" ON resenas FOR INSERT WITH CHECK (true);

-- Políticas para logs de auditoría
DROP POLICY IF EXISTS "logs_select_system" ON logs_auditoria;
CREATE POLICY "logs_select_system" ON logs_auditoria FOR SELECT USING (true);

DROP POLICY IF EXISTS "logs_insert_system" ON logs_auditoria;
CREATE POLICY "logs_insert_system" ON logs_auditoria FOR INSERT WITH CHECK (true);

-- Las demás tablas mantienen acceso público para desarrollo

-- =====================================
-- FUNCIONES AUXILIARES
-- =====================================

-- Función para generar código de reserva único
CREATE OR REPLACE FUNCTION generar_codigo_reserva()
RETURNS TEXT AS $$
DECLARE
    codigo TEXT;
    existe BOOLEAN;
BEGIN
    LOOP
        -- Generar código: RQ + año + mes + día + 4 números aleatorios
        codigo := 'RQ' || TO_CHAR(NOW(), 'YYMMDD') || LPAD(FLOOR(RANDOM() * 10000)::TEXT, 4, '0');
        
        -- Verificar si existe
        SELECT EXISTS(SELECT 1 FROM reservas WHERE codigo_reserva = codigo) INTO existe;
        
        -- Si no existe, salir del loop
        IF NOT existe THEN
            EXIT;
        END IF;
    END LOOP;
    
    RETURN codigo;
END;
$$ LANGUAGE plpgsql;

-- Función para calcular precio total de reserva
CREATE OR REPLACE FUNCTION calcular_precio_reserva(
    servicio_uuid UUID,
    adultos_nacional INTEGER DEFAULT 0,
    adultos_extranjero INTEGER DEFAULT 0,
    estudiantes INTEGER DEFAULT 0,
    cupon_codigo TEXT DEFAULT NULL
)
RETURNS JSONB AS $$
DECLARE
    servicio_data RECORD;
    subtotal DECIMAL(10,2) := 0;
    descuento DECIMAL(10,2) := 0;
    total DECIMAL(10,2) := 0;
    cupon_data RECORD;
BEGIN
    -- Obtener datos del servicio
    SELECT precio_nacional, precio_extranjero, precio_estudiante
    INTO servicio_data
    FROM servicios
    WHERE id = servicio_uuid AND disponible = true;
    
    IF NOT FOUND THEN
        RETURN jsonb_build_object('error', 'Servicio no encontrado');
    END IF;
    
    -- Calcular subtotal
    subtotal := (adultos_nacional * servicio_data.precio_nacional) +
                (adultos_extranjero * servicio_data.precio_extranjero) +
                (estudiantes * servicio_data.precio_estudiante);
    
    -- Aplicar cupón si existe
    IF cupon_codigo IS NOT NULL THEN
        SELECT tipo, valor, monto_minimo
        INTO cupon_data
        FROM cupones
        WHERE codigo = cupon_codigo
        AND activo = true
        AND fecha_inicio <= CURRENT_DATE
        AND fecha_fin >= CURRENT_DATE
        AND (usos_maximos IS NULL OR usos_actuales < usos_maximos)
        AND (servicios_aplicables = '[]'::jsonb OR servicios_aplicables @> to_jsonb(servicio_uuid));
        
        IF FOUND AND subtotal >= cupon_data.monto_minimo THEN
            IF cupon_data.tipo = 'porcentaje' THEN
                descuento := subtotal * (cupon_data.valor / 100);
            ELSE
                descuento := cupon_data.valor;
            END IF;
            
            -- El descuento no puede ser mayor al subtotal
            IF descuento > subtotal THEN
                descuento := subtotal;
            END IF;
        END IF;
    END IF;
    
    total := subtotal - descuento;
    
    RETURN jsonb_build_object(
        'subtotal', subtotal,
        'descuento', descuento,
        'total', total,
        'precio_nacional', servicio_data.precio_nacional,
        'precio_extranjero', servicio_data.precio_extranjero,
        'precio_estudiante', servicio_data.precio_estudiante
    );
END;
$$ LANGUAGE plpgsql;

-- =====================================
-- MEJORAS ADICIONALES SUGERIDAS
-- =====================================

-- Tabla para votos de reseñas (útil/no útil)
CREATE TABLE IF NOT EXISTS public.votos_resenas (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    resena_id UUID REFERENCES resenas(id) ON DELETE CASCADE,
    usuario_id UUID REFERENCES usuarios(id) ON DELETE SET NULL,
    ip_address INET,
    tipo VARCHAR(10) CHECK (tipo IN ('util', 'no_util')),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    UNIQUE(resena_id, usuario_id), -- Un usuario solo puede votar una vez por reseña
    UNIQUE(resena_id, ip_address)  -- Una IP solo puede votar una vez por reseña
);

-- Índices para votos
CREATE INDEX IF NOT EXISTS idx_votos_resena ON votos_resenas(resena_id);
CREATE INDEX IF NOT EXISTS idx_votos_usuario ON votos_resenas(usuario_id);
CREATE INDEX IF NOT EXISTS idx_votos_ip ON votos_resenas(ip_address);

-- Tabla para notificaciones
CREATE TABLE IF NOT EXISTS public.notificaciones (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    usuario_id UUID REFERENCES usuarios(id) ON DELETE CASCADE,
    tipo VARCHAR(50) NOT NULL, -- 'reserva_confirmada', 'resena_aprobada', etc.
    titulo VARCHAR(200) NOT NULL,
    mensaje TEXT NOT NULL,
    datos JSONB DEFAULT '{}', -- Datos adicionales específicos del tipo
    leida BOOLEAN DEFAULT false,
    url_accion VARCHAR(500), -- URL a la que llevar al hacer clic
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Índices para notificaciones
CREATE INDEX IF NOT EXISTS idx_notificaciones_usuario ON notificaciones(usuario_id);
CREATE INDEX IF NOT EXISTS idx_notificaciones_leida ON notificaciones(leida);
CREATE INDEX IF NOT EXISTS idx_notificaciones_tipo ON notificaciones(tipo);

-- Tabla para métricas y analytics
CREATE TABLE IF NOT EXISTS public.metricas (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    fecha DATE NOT NULL,
    metrica VARCHAR(100) NOT NULL, -- 'visitas_servicios', 'conversiones', etc.
    valor DECIMAL(15,2) NOT NULL,
    metadata JSONB DEFAULT '{}',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    UNIQUE(fecha, metrica)
);

-- Índices para métricas
CREATE INDEX IF NOT EXISTS idx_metricas_fecha ON metricas(fecha);
CREATE INDEX IF NOT EXISTS idx_metricas_metrica ON metricas(metrica);

-- Tabla para respuestas a reseñas
CREATE TABLE IF NOT EXISTS public.respuestas_resenas (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    resena_id UUID REFERENCES resenas(id) ON DELETE CASCADE,
    usuario_id UUID REFERENCES usuarios(id) ON DELETE SET NULL, -- Admin que respondió
    contenido TEXT NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Índices para respuestas
CREATE INDEX IF NOT EXISTS idx_respuestas_resena ON respuestas_resenas(resena_id);
CREATE INDEX IF NOT EXISTS idx_respuestas_usuario ON respuestas_resenas(usuario_id);

-- Trigger para respuestas (eliminar si existe y recrear)
DROP TRIGGER IF EXISTS update_respuestas_resenas_updated_at ON respuestas_resenas;
CREATE TRIGGER update_respuestas_resenas_updated_at 
    BEFORE UPDATE ON respuestas_resenas 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- =====================================
-- VISTAS ÚTILES
-- =====================================

-- Vista de reseñas con estadísticas completas
CREATE OR REPLACE VIEW vista_resenas_completas AS
SELECT 
    r.*,
    u.nombre as usuario_nombre,
    u.email as usuario_email,
    s.nombre as servicio_nombre,
    COUNT(vr.id) as total_votos,
    COUNT(CASE WHEN vr.tipo = 'util' THEN 1 END) as votos_utiles,
    COUNT(CASE WHEN vr.tipo = 'no_util' THEN 1 END) as votos_no_utiles,
    EXISTS(SELECT 1 FROM respuestas_resenas WHERE resena_id = r.id) as tiene_respuesta
FROM resenas r
LEFT JOIN usuarios u ON r.usuario_id = u.id
LEFT JOIN servicios s ON r.servicio_id = s.id
LEFT JOIN votos_resenas vr ON r.id = vr.resena_id
GROUP BY r.id, u.nombre, u.email, s.nombre;

-- Vista de estadísticas de servicios
CREATE OR REPLACE VIEW vista_estadisticas_servicios AS
SELECT 
    s.id,
    s.nombre,
    s.precio_nacional,
    s.precio_extranjero,
    COUNT(r.id) as total_reservas,
    COUNT(CASE WHEN r.estado = 'pagada' THEN 1 END) as reservas_pagadas,
    COUNT(CASE WHEN r.estado = 'utilizada' THEN 1 END) as reservas_utilizadas,
    SUM(CASE WHEN r.estado IN ('pagada', 'utilizada') THEN r.precio_final ELSE 0 END) as ingresos_total,
    AVG(CASE WHEN res.calificacion IS NOT NULL THEN res.calificacion END) as calificacion_promedio,
    COUNT(res.id) as total_resenas,
    COUNT(CASE WHEN res.aprobada = true THEN 1 END) as resenas_aprobadas
FROM servicios s
LEFT JOIN reservas r ON s.id = r.servicio_id
LEFT JOIN resenas res ON s.id = res.servicio_id
GROUP BY s.id, s.nombre, s.precio_nacional, s.precio_extranjero;

-- Vista de reservas con información completa
CREATE OR REPLACE VIEW vista_reservas_completas AS
SELECT 
    r.*,
    u.nombre as usuario_nombre,
    u.email as usuario_email,
    s.nombre as servicio_nombre,
    s.duracion_estimada as servicio_duracion
FROM reservas r
LEFT JOIN usuarios u ON r.usuario_id = u.id
LEFT JOIN servicios s ON r.servicio_id = s.id;

-- =====================================
-- FIN DEL SETUP
-- =====================================
