<main class="main-content">
    <div class="container">
        <div class="profile-header">
            <div class="profile-avatar">
                <div class="avatar-circle">
                    <i class="fas fa-user"></i>
                </div>
                <button class="avatar-edit-btn" onclick="editAvatar()">
                    <i class="fas fa-camera"></i>
                </button>
            </div>
            <div class="profile-info">
                <h1 class="profile-name" id="profile-name"><?php echo $user['name']; ?></h1>
                <p class="profile-email" id="profile-email"><?php echo $user['email'] ?></p>
                <div class="profile-badges">
                    <span class="badge badge-primary" id="profile-role">Usuário</span>
                    <span class="badge badge-success" id="profile-status">Ativo</span>
                </div>
            </div>
            <div class="profile-actions">
                <button class="btn btn-outline" onclick="editProfile()">
                    <i class="fas fa-edit"></i>
                    Editar Perfil
                </button>
                <button class="btn btn-primary" onclick="changePassword()">
                    <i class="fas fa-key"></i>
                    Alterar Senha
                </button>
            </div>
        </div>

        <div class="profile-content">
            <div class="profile-stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-number" id="books-borrowed">—</h3>
                        <p class="stat-label">Livros Emprestados</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-number" id="books-history">—</h3>
                        <p class="stat-label">Histórico Total</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-number" id="member-since">—</h3>
                        <p class="stat-label">Dias como Membro</p>
                    </div>
                </div>
                <div class="stat-card" id="overdue-card" style="display: none;">
                    <div class="stat-icon overdue-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-number" id="overdue-count">0</h3>
                        <p class="stat-label">Empréstimos Atrasados</p>
                    </div>
                </div>
            </div>

            <div class="profile-sections">
                <div class="section">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-user-circle"></i>
                            Informações Pessoais
                        </h2>
                    </div>
                    <div class="section-content">
                        <div class="info-grid">
                            <div class="info-item">
                                <label class="info-label">Nome Completo</label>
                                <p class="info-value" id="info-name"><?php echo $user['name'] ?></p>
                            </div>
                            <div class="info-item">
                                <label class="info-label">E-mail</label>
                                <p class="info-value" id="info-email"><?php echo $user['email'] ?></p>
                            </div>
                            <div class="info-item">
                                <label class="info-label">Tipo de Conta</label>
                                <p class="info-value" id="info-role"><?php echo ($user['role'] == 'admin') ?  'Administrador' :  'Usuário' ?></p>
                            </div>
                            <div class="info-item">
                                <label class="info-label">Membro desde</label>
                                <p class="info-value" id="info-created"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section" id="overdue-section" style="display: none;">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-exclamation-triangle"></i>
                            Empréstimos Atrasados
                        </h2>
                    </div>
                    <div class="section-content">
                        <div id="overdue-list">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<div class="modal" id="edit-profile-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Editar Perfil</h3>
            <button class="modal-close" onclick="closeModal('edit-profile-modal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="edit-profile-form">
                <div class="form-group">
                    <label for="edit-name" class="form-label">Nome Completo</label>
                    <input type="text" id="edit-name" name="name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="edit-email" class="form-label">E-mail</label>
                    <input type="email" id="edit-email" name="email" class="form-input" required>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('edit-profile-modal')">Cancelar</button>
            <button class="btn btn-primary" onclick="saveProfile()">Salvar</button>
        </div>
    </div>
</div>

<div class="modal" id="change-password-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Alterar Senha</h3>
            <button class="modal-close" onclick="closeModal('change-password-modal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="change-password-form">
                <div class="form-group">
                    <label for="current-password" class="form-label">Senha Atual</label>
                    <input type="password" id="current-password" name="current_password" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="new-password" class="form-label">Nova Senha</label>
                    <input type="password" id="new-password" name="new_password" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="confirm-password" class="form-label">Confirmar Nova Senha</label>
                    <input type="password" id="confirm-password" name="confirm_password" class="form-input" required>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('change-password-modal')">Cancelar</button>
            <button class="btn btn-primary" onclick="savePassword()">Alterar Senha</button>
        </div>
    </div>
</div>
<style>
    <?php include __DIR__ . '/public/css/profile.css'; ?>
</style>
<script type="module" >
    <?php include __DIR__ . '/public/js/profile.js'; ?>
</script>

<script type="module">
    import { 
        editProfile, 
        saveProfile, 
        changePassword, 
        savePassword, 
        editAvatar, 
        closeModal 
    } from '/public/js/profile.js';
</script>