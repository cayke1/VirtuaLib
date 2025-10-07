-- Migração para o novo fluxo de solicitação/autorização/devolução
-- Execute este script para atualizar o schema existente

-- 1. Adicionar novas colunas
ALTER TABLE Borrows 
ADD COLUMN requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER borrowed_at,
ADD COLUMN approved_at TIMESTAMP NULL AFTER requested_at;

-- 2. Migrar dados existentes
-- Para empréstimos ativos (status = 'borrowed' ou 'late'), definir approved_at = borrowed_at
UPDATE Borrows 
SET approved_at = borrowed_at 
WHERE status IN ('borrowed', 'late') AND borrowed_at IS NOT NULL;

-- Para empréstimos devolvidos, manter o fluxo atual
-- (já estão com returned_at preenchido)

-- 3. Atualizar o ENUM de status
ALTER TABLE Borrows 
MODIFY COLUMN status ENUM('pending', 'approved', 'returned', 'late') DEFAULT 'pending';

-- 4. Renomear coluna borrowed_at para requested_at (MySQL não suporta renomear diretamente)
-- Vamos manter borrowed_at por compatibilidade e usar requested_at para novos registros
-- ALTER TABLE Borrows CHANGE COLUMN borrowed_at requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- 5. Tornar due_date nullable (já que só é definido após aprovação)
ALTER TABLE Borrows 
MODIFY COLUMN due_date DATE NULL;

-- 6. Adicionar índices para melhor performance
CREATE INDEX idx_borrows_user_status ON Borrows(user_id, status);
CREATE INDEX idx_borrows_book_status ON Borrows(book_id, status);
CREATE INDEX idx_borrows_requested_at ON Borrows(requested_at);

-- Nota: Para uma migração completa, você pode querer remover a coluna borrowed_at
-- após confirmar que tudo está funcionando:
-- ALTER TABLE Borrows DROP COLUMN borrowed_at;
