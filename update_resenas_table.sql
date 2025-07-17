-- SQL para ejecutar en Supabase para agregar columnas faltantes a la tabla resenas

-- 1) Agregar las columnas necesarias para el panel admin y la API
ALTER TABLE public.resenas
  ADD COLUMN IF NOT EXISTS nombre         VARCHAR(200)       DEFAULT '',
  ADD COLUMN IF NOT EXISTS email          VARCHAR(200)       DEFAULT '',
  ADD COLUMN IF NOT EXISTS ubicacion      TEXT               DEFAULT '',
  ADD COLUMN IF NOT EXISTS fecha_visita   TIMESTAMPTZ,
  ADD COLUMN IF NOT EXISTS ip_address     TEXT               DEFAULT '',
  ADD COLUMN IF NOT EXISTS user_agent     TEXT               DEFAULT '';

-- 2) Modificar el campo aprobada para permitir NULL (pendiente)
ALTER TABLE public.resenas
  ALTER COLUMN aprobada DROP DEFAULT,
  ALTER COLUMN aprobada DROP NOT NULL;

-- 3) Crear índices para búsquedas frecuentes
CREATE INDEX IF NOT EXISTS idx_resenas_email        ON public.resenas(email);
CREATE INDEX IF NOT EXISTS idx_resenas_nombre       ON public.resenas(nombre);

-- 4) Insertar algunas reseñas de prueba
INSERT INTO public.resenas (nombre, email, titulo, comentario, calificacion, aprobada, ip_address, user_agent)
VALUES 
  ('María González', 'maria@test.com', 'Excelente experiencia', 'El tour fue increíble, muy recomendado para familias', 5, true, '192.168.1.1', 'Test'),
  ('Juan Pérez', 'juan@test.com', 'Muy bueno', 'El lugar es hermoso aunque un poco caro', 4, null, '192.168.1.2', 'Test'),
  ('Ana López', 'ana@test.com', 'Regular', 'Esperaba más del servicio', 3, false, '192.168.1.3', 'Test');
