-- Script SQL para crear el sistema de tickets/entradas en Supabase
-- Ejecutar en el SQL Editor de Supabase

-- 1. Tabla de tipos de tickets/entradas
CREATE TABLE IF NOT EXISTS public.tipos_tickets (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL DEFAULT 0,
    precio_estudiante DECIMAL(10,2),
    precio_grupo DECIMAL(10,2),
    disponible BOOLEAN DEFAULT true,
    imagen_url VARCHAR(500),
    caracteristicas JSONB DEFAULT '[]'::jsonb,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- 2. Tabla de tickets/entradas vendidas
CREATE TABLE IF NOT EXISTS public.tickets (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    tipo_ticket_id UUID REFERENCES public.tipos_tickets(id) ON DELETE CASCADE,
    codigo_ticket VARCHAR(20) UNIQUE NOT NULL,
    nombre_comprador VARCHAR(200) NOT NULL,
    email_comprador VARCHAR(255) NOT NULL,
    telefono_comprador VARCHAR(20),
    documento_comprador VARCHAR(20),
    tipo_documento VARCHAR(10) DEFAULT 'DNI',
    cantidad INTEGER NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10,2) NOT NULL,
    precio_total DECIMAL(10,2) NOT NULL,
    descuento DECIMAL(10,2) DEFAULT 0,
    fecha_visita DATE,
    hora_visita TIME,
    estado VARCHAR(20) DEFAULT 'pendiente', -- pendiente, pagado, usado, cancelado
    metodo_pago VARCHAR(50),
    transaccion_id VARCHAR(100),
    notas TEXT,
    ip_address INET,
    user_agent TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    usado_at TIMESTAMP WITH TIME ZONE,
    usado_por UUID -- referencias a usuarios admin que validaron el ticket
);

-- 3. Insertar tipos de tickets por defecto
INSERT INTO public.tipos_tickets (nombre, descripcion, precio, precio_estudiante, precio_grupo, imagen_url, caracteristicas) VALUES
(
    'Entrada General',
    'Acceso completo al Templo de Wiracocha y sitios arqueológicos de Raqchi',
    15.00,
    7.50,
    12.00,
    '/assets/images/tickets/entrada-general.jpg',
    '["Acceso al templo principal", "Museo de sitio", "Área de construcciones", "Guía básica incluida"]'::jsonb
),
(
    'Entrada Estudiante',
    'Tarifa especial para estudiantes con documento válido',
    7.50,
    7.50,
    null,
    '/assets/images/tickets/entrada-estudiante.jpg',
    '["Acceso al templo principal", "Museo de sitio", "Descuento estudiantil", "Validación de carnet requerida"]'::jsonb
),
(
    'Entrada Grupo',
    'Tarifa especial para grupos de 10 personas o más',
    12.00,
    10.00,
    12.00,
    '/assets/images/tickets/entrada-grupo.jpg',
    '["Acceso al templo principal", "Museo de sitio", "Guía especializado incluido", "Mínimo 10 personas"]'::jsonb
);

-- 4. Crear índices para optimizar consultas
CREATE INDEX IF NOT EXISTS idx_tickets_codigo ON public.tickets(codigo_ticket);
CREATE INDEX IF NOT EXISTS idx_tickets_email ON public.tickets(email_comprador);
CREATE INDEX IF NOT EXISTS idx_tickets_estado ON public.tickets(estado);
CREATE INDEX IF NOT EXISTS idx_tickets_fecha_visita ON public.tickets(fecha_visita);
CREATE INDEX IF NOT EXISTS idx_tickets_created_at ON public.tickets(created_at);

-- 5. Crear función para generar códigos de ticket únicos
CREATE OR REPLACE FUNCTION generate_ticket_code()
RETURNS TEXT AS $$
DECLARE
    code TEXT;
    exists_count INTEGER;
BEGIN
    LOOP
        -- Generar código: RQ + año actual + número aleatorio de 6 dígitos
        code := 'RQ' || EXTRACT(YEAR FROM NOW()) || LPAD(FLOOR(RANDOM() * 1000000)::text, 6, '0');
        
        -- Verificar si el código ya existe
        SELECT COUNT(*) INTO exists_count FROM public.tickets WHERE codigo_ticket = code;
        
        -- Si no existe, salir del loop
        IF exists_count = 0 THEN
            EXIT;
        END IF;
    END LOOP;
    
    RETURN code;
END;
$$ LANGUAGE plpgsql;

-- 6. Crear trigger para auto-generar código de ticket
CREATE OR REPLACE FUNCTION auto_generate_ticket_code()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.codigo_ticket IS NULL OR NEW.codigo_ticket = '' THEN
        NEW.codigo_ticket := generate_ticket_code();
    END IF;
    
    NEW.updated_at := NOW();
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_auto_generate_ticket_code
    BEFORE INSERT OR UPDATE ON public.tickets
    FOR EACH ROW
    EXECUTE FUNCTION auto_generate_ticket_code();

-- 7. Crear trigger para actualizar updated_at en tipos_tickets
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_update_tipos_tickets_updated_at
    BEFORE UPDATE ON public.tipos_tickets
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 8. Habilitar RLS (Row Level Security) si es necesario
ALTER TABLE public.tipos_tickets ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.tickets ENABLE ROW LEVEL SECURITY;

-- 9. Crear políticas RLS básicas (ajustar según necesidades)
-- Permitir lectura pública de tipos de tickets
CREATE POLICY "Allow public read access to tipos_tickets" ON public.tipos_tickets
    FOR SELECT USING (disponible = true);

-- Permitir a usuarios autenticados crear tickets
CREATE POLICY "Allow authenticated users to insert tickets" ON public.tickets
    FOR INSERT TO authenticated WITH CHECK (true);

-- Permitir a usuarios ver solo sus propios tickets (por email)
CREATE POLICY "Users can view own tickets" ON public.tickets
    FOR SELECT USING (
        auth.jwt() ->> 'email' = email_comprador OR
        auth.jwt() ->> 'role' = 'admin'
    );

-- Permitir a admins ver y modificar todos los tickets
CREATE POLICY "Admins can do everything with tickets" ON public.tickets
    TO authenticated
    USING (auth.jwt() ->> 'role' = 'admin')
    WITH CHECK (auth.jwt() ->> 'role' = 'admin');

COMMIT;
