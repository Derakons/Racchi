-- =====================================
-- CORRECCIÓN DE POLÍTICAS RLS PARA EVITAR RECURSIÓN INFINITA
-- Portal Digital de Raqchi
-- =====================================

-- DESHABILITAR RLS TEMPORALMENTE PARA CONFIGURACIÓN INICIAL
ALTER TABLE usuarios DISABLE ROW LEVEL SECURITY;
ALTER TABLE reservas DISABLE ROW LEVEL SECURITY;
ALTER TABLE resenas DISABLE ROW LEVEL SECURITY;
ALTER TABLE logs_auditoria DISABLE ROW LEVEL SECURITY;

-- ELIMINAR TODAS LAS POLÍTICAS PROBLEMÁTICAS
DROP POLICY IF EXISTS "Usuarios pueden ver su propio perfil" ON usuarios;
DROP POLICY IF EXISTS "Usuarios pueden actualizar su propio perfil" ON usuarios;
DROP POLICY IF EXISTS "Usuarios pueden ver sus propias reservas" ON reservas;
DROP POLICY IF EXISTS "Usuarios pueden crear reservas" ON reservas;
DROP POLICY IF EXISTS "Todos pueden ver reseñas aprobadas" ON resenas;
DROP POLICY IF EXISTS "Usuarios pueden crear reseñas" ON resenas;
DROP POLICY IF EXISTS "Usuarios pueden editar sus propias reseñas" ON resenas;
DROP POLICY IF EXISTS "Admins tienen acceso completo a usuarios" ON usuarios;
DROP POLICY IF EXISTS "Admins tienen acceso completo a reservas" ON reservas;

-- POLÍTICAS SIMPLIFICADAS PARA EVITAR RECURSIÓN

-- HABILITAR RLS SOLO EN TABLAS CRÍTICAS
ALTER TABLE resenas ENABLE ROW LEVEL SECURITY;
ALTER TABLE logs_auditoria ENABLE ROW LEVEL SECURITY;

-- Política simple para reseñas: solo mostrar aprobadas
CREATE POLICY "resenas_select_approved" ON resenas FOR SELECT USING (aprobada = true);

-- Política para insertar reseñas: permitir a todos (se puede restringir después)
CREATE POLICY "resenas_insert_public" ON resenas FOR INSERT WITH CHECK (true);

-- Política para logs: solo lectura para sistema
CREATE POLICY "logs_select_system" ON logs_auditoria FOR SELECT USING (true);
CREATE POLICY "logs_insert_system" ON logs_auditoria FOR INSERT WITH CHECK (true);

-- NOTA: Para usuarios y reservas, mantener RLS deshabilitado durante desarrollo
-- En producción se puede habilitar con políticas más específicas

-- Crear función helper para verificar admin (sin recursión)
CREATE OR REPLACE FUNCTION is_admin_user()
RETURNS boolean
LANGUAGE sql
SECURITY DEFINER
AS $$
  SELECT CASE 
    WHEN current_setting('app.user_role', true) = 'admin' THEN true 
    ELSE false 
  END;
$$;
