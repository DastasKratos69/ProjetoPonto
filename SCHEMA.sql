-- ==========================================
-- SCHEMA.sql — Sistema de Ponto e Banco de Horas
-- ==========================================

-- 1. TIPOS ENUMERADOS
CREATE TYPE user_status AS ENUM ('ATIVO', 'BLOQUEADO_RH', 'INATIVO');
CREATE TYPE user_role AS ENUM ('FUNCIONARIO', 'GESTOR', 'RH_ADMIN');
CREATE TYPE entry_method AS ENUM ('FACIAL', 'ID_MANUAL', 'CONTINGENCIA_RH');

-- 2. TABELA DE FUNCIONÁRIOS
CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    employee_code VARCHAR(20) UNIQUE NOT NULL, -- ID do funcionário para contingência
    full_name VARCHAR(100) NOT NULL,
    status user_status DEFAULT 'ATIVO',
    role user_role DEFAULT 'FUNCIONARIO',
    is_twin_exception BOOLEAN DEFAULT FALSE, -- Flag para exceção de gêmeos
    face_vector JSONB, -- Descritor numérico do vetor facial (128d float array)
    photo_url TEXT,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 3. TABELA DE TERMINAIS FÍSICOS
CREATE TABLE terminals (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    terminal_code VARCHAR(20) UNIQUE NOT NULL, -- Ex: 'TERM-01', 'TERM-02'
    location VARCHAR(50) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- 4. TABELA DE REGISTROS DE PONTO
CREATE TABLE time_entries (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    terminal_id UUID REFERENCES terminals(id),
    timestamp TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    method entry_method NOT NULL,
    is_offline_sync BOOLEAN DEFAULT FALSE, -- Indica se foi gravado via Sync offline
    photo_audit_url TEXT, -- Foto tirada no momento da marcação (opcional)
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- 5. TABELA DE SALDO E BANCO DE HORAS
CREATE TABLE time_bank (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    month_reference DATE NOT NULL, -- Ex: '2026-09-01'
    balance_minutes INT NOT NULL DEFAULT 0, -- Saldo acumulado no mês em minutos (-1200m = -20h)
    requires_rh_review BOOLEAN DEFAULT FALSE, -- Ativado quando balance_minutes <= -1200
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE(user_id, month_reference)
);

-- 6. INDICES DE PERFORMANCE
CREATE INDEX idx_users_employee_code ON users(employee_code);
CREATE INDEX idx_time_entries_user_timestamp ON time_entries(user_id, timestamp DESC);
CREATE INDEX idx_time_bank_user_month ON time_bank(user_id, month_reference);

-- 7. REGRAS DE SEGURANÇA (Row Level Security - RLS)
ALTER TABLE users ENABLE ROW LEVEL SECURITY;
ALTER TABLE terminals ENABLE ROW LEVEL SECURITY;
ALTER TABLE time_entries ENABLE ROW LEVEL SECURITY;
ALTER TABLE time_bank ENABLE ROW LEVEL SECURITY;

-- Políticas de acesso público para os Terminais da fábrica
CREATE POLICY "Terminais podem ler lista de usuários" ON users FOR SELECT USING (true);
CREATE POLICY "Terminais podem ler lista de terminais" ON terminals FOR SELECT USING (true);
CREATE POLICY "Terminais podem inserir registros de ponto" ON time_entries FOR INSERT WITH CHECK (true);
