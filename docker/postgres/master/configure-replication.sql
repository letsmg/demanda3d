-- =============================================
-- PostgreSQL Master: Replication Configuration
-- Executado no primeiro boot do container
-- =============================================

-- Cria slot de replicação física (se não existir)
SELECT pg_create_physical_replication_slot('demanda3d_replica', true)
WHERE NOT EXISTS (
    SELECT 1 FROM pg_replication_slots WHERE slot_name = 'demanda3d_replica'
);