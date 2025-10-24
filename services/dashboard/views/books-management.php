<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="/public/css/layout.css">
    <link rel="stylesheet" href="/public/css/dashboard.css">
    <link rel="stylesheet" href="/public/css/toast.css">
    <link rel="stylesheet" href="/public/css/books-management.css">
</head>
<body>

    <aside class="sidebar">
        <?php include __DIR__."/components/sidebar.php"?>
    </aside>

    <div class="container">
    <div class="books-management">
        <div class="page-header">
            <h1 class="page-title">Gerenciamento de Livros</h1>
            <button class="btn-primary" onclick="openCreateModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 5v14m-7-7h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Adicionar Livro
            </button>
        </div>

        <div class="books-table">
            <div class="table-header">
                <h2 class="table-title">Lista de Livros</h2>
            </div>
            <div class="table-container">
                <table class="table" id="books-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Autor</th>
                            <th>Gênero</th>
                            <th>Ano</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="books-tbody">
                        <tr>
                            <td colspan="7" class="loading">Carregando livros...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para criar/editar livro -->
    <div id="book-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modal-title">Adicionar Livro</h2>
                <button class="close" onclick="closeModal()">&times;</button>
            </div>
            <form id="book-form">
                <div class="form-group">
                    <label class="form-label" for="title">Título *</label>
                    <input type="text" id="title" name="title" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="author">Autor *</label>
                    <input type="text" id="author" name="author" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="genre">Gênero *</label>
                    <input type="text" id="genre" name="genre" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="year">Ano de Publicação *</label>
                    <input type="number" id="year" name="year" class="form-input" min="1000" max="2024" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="description">Descrição *</label>
                    <textarea id="description" name="description" class="form-textarea" required></textarea>
                </div>
                <div class="form-group">
                    <div class="form-checkbox">
                        <input type="checkbox" id="available" name="available" checked>
                        <label class="form-label" for="available">Disponível para empréstimo</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn-primary" id="submit-btn">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container"></div>
    </div>

    <script src="/public/js/toast.js"></script>
    <script src="/public/js/books-management.js"></script>
</body>
</html>
