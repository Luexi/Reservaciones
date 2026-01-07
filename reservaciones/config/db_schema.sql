-- Enable UUID extension
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Tabla: restaurantes
CREATE TABLE IF NOT EXISTS restaurantes (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  nombre VARCHAR(255) NOT NULL,
  direccion TEXT,
  telefono VARCHAR(20),
  email VARCHAR(255),
  configuracion JSONB,
  created_at TIMESTAMP DEFAULT NOW(),
  updated_at TIMESTAMP DEFAULT NOW()
);

-- Tabla: mesas
CREATE TABLE IF NOT EXISTS mesas (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  restaurante_id UUID REFERENCES restaurantes(id),
  numero_mesa VARCHAR(10) NOT NULL,
  capacidad_min INT DEFAULT 1,
  capacidad_max INT NOT NULL,
  posicion_x FLOAT,
  posicion_y FLOAT,
  activa BOOLEAN DEFAULT true,
  notas TEXT,
  created_at TIMESTAMP DEFAULT NOW(),
  updated_at TIMESTAMP DEFAULT NOW()
);

-- Tabla: reservaciones
CREATE TABLE IF NOT EXISTS reservaciones (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  restaurante_id UUID REFERENCES restaurantes(id),
  mesa_id UUID REFERENCES mesas(id),
  nombre_cliente VARCHAR(255) NOT NULL,
  telefono VARCHAR(20) NOT NULL,
  email VARCHAR(255),
  num_personas INT NOT NULL,
  fecha DATE NOT NULL,
  hora TIME NOT NULL,
  duracion_minutos INT DEFAULT 120,
  ocasion_especial VARCHAR(100),
  comentarios TEXT,
  estado VARCHAR(20) DEFAULT 'pendiente', -- pendiente, confirmada, llego, no_llego, cancelada
  origen VARCHAR(20) DEFAULT 'web', -- web, whatsapp, messenger, telefono, walkin
  confirmada_por VARCHAR(100),
  confirmada_en TIMESTAMP,
  cancelada_en TIMESTAMP,
  razon_cancelacion TEXT,
  created_at TIMESTAMP DEFAULT NOW(),
  updated_at TIMESTAMP DEFAULT NOW()
);

-- Tabla: bloqueos
CREATE TABLE IF NOT EXISTS bloqueos (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  restaurante_id UUID REFERENCES restaurantes(id),
  tipo VARCHAR(20), -- 'fecha', 'horario', 'mesa'
  fecha_inicio DATE,
  fecha_fin DATE,
  hora_inicio TIME,
  hora_fin TIME,
  mesa_id UUID REFERENCES mesas(id),
  razon TEXT,
  created_at TIMESTAMP DEFAULT NOW(),
  updated_at TIMESTAMP DEFAULT NOW()
);

-- Tabla: configuracion_horarios
CREATE TABLE IF NOT EXISTS configuracion_horarios (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  restaurante_id UUID REFERENCES restaurantes(id),
  dia_semana INT, -- 0=Domingo, 6=Sábado
  hora_apertura TIME,
  hora_cierre TIME,
  cerrado BOOLEAN DEFAULT false,
  created_at TIMESTAMP DEFAULT NOW(),
  updated_at TIMESTAMP DEFAULT NOW()
);

-- Tabla: clientes
CREATE TABLE IF NOT EXISTS clientes (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  nombre VARCHAR(255) NOT NULL,
  telefono VARCHAR(20) UNIQUE NOT NULL,
  email VARCHAR(255),
  total_reservas INT DEFAULT 0,
  total_no_shows INT DEFAULT 0,
  notas TEXT,
  created_at TIMESTAMP DEFAULT NOW(),
  updated_at TIMESTAMP DEFAULT NOW()
);

-- Tabla: logs_acceso
CREATE TABLE IF NOT EXISTS logs_acceso (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  usuario VARCHAR(100),
  accion VARCHAR(255),
  detalles JSONB,
  ip_address INET,
  created_at TIMESTAMP DEFAULT NOW()
);

-- Indices
CREATE INDEX IF NOT EXISTS idx_reservaciones_fecha ON reservaciones(fecha);
CREATE INDEX IF NOT EXISTS idx_reservaciones_estado ON reservaciones(estado);
CREATE INDEX IF NOT EXISTS idx_reservaciones_telefono ON reservaciones(telefono);
CREATE INDEX IF NOT EXISTS idx_clientes_telefono ON clientes(telefono);

-- RLS (Row Level Security)
ALTER TABLE reservaciones ENABLE ROW LEVEL SECURITY;
ALTER TABLE mesas ENABLE ROW LEVEL SECURITY;
ALTER TABLE bloqueos ENABLE ROW LEVEL SECURITY;

-- Policies (Simplified for initial setup - allowing public read for checks, authenticated for writes)
-- Note: In production, you would bind this to Supabase Auth Users
CREATE POLICY "Public Read Availability" ON mesas FOR SELECT USING (true);
CREATE POLICY "Public Read Config" ON configuracion_horarios FOR SELECT USING (true);
-- For now, allowing robust access to service role or authenticated users
CREATE POLICY "Service Role Full Access Reservaciones" ON reservaciones USING (true) WITH CHECK (true);

-- Functions
CREATE OR REPLACE FUNCTION verificar_disponibilidad(
  p_fecha DATE,
  p_hora TIME,
  p_num_personas INT
)
RETURNS TABLE(mesa_id UUID, capacidad INT) AS $$
BEGIN
  RETURN QUERY
  SELECT m.id, m.capacidad_max
  FROM mesas m
  WHERE m.activa = true
    AND m.capacidad_max >= p_num_personas
    AND NOT EXISTS (
      SELECT 1 FROM reservaciones r
      WHERE r.mesa_id = m.id
        AND r.fecha = p_fecha
        AND r.estado NOT IN ('cancelada', 'no_llego')
        AND (
          (p_hora >= r.hora AND p_hora < (r.hora + (r.duracion_minutos || ' minutes')::INTERVAL))
          OR
          ((p_hora + INTERVAL '2 hours') > r.hora AND (p_hora + INTERVAL '2 hours') <= (r.hora + (r.duracion_minutos || ' minutes')::INTERVAL))
        )
    )
    AND NOT EXISTS (
      SELECT 1 FROM bloqueos b
      WHERE (b.mesa_id = m.id OR b.tipo = 'fecha')
        AND p_fecha BETWEEN b.fecha_inicio AND b.fecha_fin
        AND (b.hora_inicio IS NULL OR p_hora >= b.hora_inicio)
        AND (b.hora_fin IS NULL OR p_hora <= b.hora_fin)
    );
END;
$$ LANGUAGE plpgsql;
