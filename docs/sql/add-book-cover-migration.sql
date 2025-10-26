-- Migration para adicionar campo de capa aos livros
-- Execute este script para adicionar suporte a capas de livros

-- Adicionar coluna cover_image na tabela Books
ALTER TABLE Books ADD COLUMN cover_image VARCHAR(255) NULL AFTER description;

-- Comentário: O campo cover_image armazenará o caminho relativo da imagem da capa
-- Exemplo: /uploads/covers/book_123.jpg
