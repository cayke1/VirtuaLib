// Controle da página de perfil
document.addEventListener('DOMContentLoaded', function() {
    loadProfileData();
    loadUserStats();
    loadRecentActivity();
});

// Carregar dados do perfil
async function loadProfileData() {
    try {
        const response = await fetch('/api/auth/me', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            const data = await response.json();
            if (data.user) {
                updateProfileInfo(data.user);
            } else {
                // Usuário não logado, redirecionar para login
                window.location.href = '/login';
            }
        } else {
            // Erro na autenticação, redirecionar para login
            window.location.href = '/login';
        }
    } catch (error) {
        console.error('Erro ao carregar dados do perfil:', error);
        window.location.href = '/login';
    }
}

// Atualizar informações do perfil na interface
function updateProfileInfo(user) {
    // Atualizar elementos principais
    const profileName = document.getElementById('profile-name');
    const profileEmail = document.getElementById('profile-email');
    const profileRole = document.getElementById('profile-role');
    
    if (profileName) profileName.textContent = user.name || 'Usuário';
    if (profileEmail) profileEmail.textContent = user.email || 'usuario@email.com';
    if (profileRole) profileRole.textContent = user.role === 'admin' ? 'Administrador' : 'Usuário';

    // Atualizar informações detalhadas
    const infoName = document.getElementById('info-name');
    const infoEmail = document.getElementById('info-email');
    const infoRole = document.getElementById('info-role');
    const infoCreated = document.getElementById('info-created');
    
    if (infoName) infoName.textContent = user.name || 'Usuário';
    if (infoEmail) infoEmail.textContent = user.email || 'usuario@email.com';
    if (infoRole) infoRole.textContent = user.role === 'admin' ? 'Administrador' : 'Usuário';
    if (infoCreated && user.created_at) {
        const createdDate = new Date(user.created_at);
        infoCreated.textContent = createdDate.toLocaleDateString('pt-BR');
    }

    // Calcular dias como membro
    if (user.created_at) {
        const createdDate = new Date(user.created_at);
        const today = new Date();
        const diffTime = Math.abs(today - createdDate);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        const memberSince = document.getElementById('member-since');
        if (memberSince) memberSince.textContent = diffDays;
    }
}

// Carregar estatísticas do usuário
async function loadUserStats() {
    try {
        // Simular dados por enquanto - em produção, viria de uma API
        const booksBorrowed = document.getElementById('books-borrowed');
        const booksHistory = document.getElementById('books-history');
        
        if (booksBorrowed) booksBorrowed.textContent = '0';
        if (booksHistory) booksHistory.textContent = '0';
    } catch (error) {
        console.error('Erro ao carregar estatísticas:', error);
    }
}

// Carregar atividade recente
async function loadRecentActivity() {
    try {
        const activityList = document.getElementById('activity-list');
        if (activityList) {
            activityList.innerHTML = `
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="activity-content">
                        <p class="activity-text">Conta criada com sucesso</p>
                        <span class="activity-time">Hoje</span>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="activity-content">
                        <p class="activity-text">Bem-vindo à VirtuaLib!</p>
                        <span class="activity-time">Hoje</span>
                    </div>
                </div>
            `;
        }
    } catch (error) {
        console.error('Erro ao carregar atividade recente:', error);
    }
}

// Editar avatar
function editAvatar() {
    // Por enquanto, apenas um alerta
    alert('Funcionalidade de edição de avatar será implementada em breve!');
}

// Editar perfil
function editProfile() {
    const modal = document.getElementById('edit-profile-modal');
    if (modal) {
        // Preencher formulário com dados atuais
        const currentName = document.getElementById('profile-name').textContent;
        const currentEmail = document.getElementById('profile-email').textContent;
        
        document.getElementById('edit-name').value = currentName;
        document.getElementById('edit-email').value = currentEmail;
        
        modal.classList.add('show');
    }
}

// Alterar senha
function changePassword() {
    const modal = document.getElementById('change-password-modal');
    if (modal) {
        modal.classList.add('show');
    }
}

// Fechar modal
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
    }
}

// Salvar perfil
async function saveProfile() {
    const name = document.getElementById('edit-name').value;
    const email = document.getElementById('edit-email').value;
    
    if (!name || !email) {
        alert('Por favor, preencha todos os campos!');
        return;
    }
    
    try {
        // Aqui seria feita a requisição para salvar o perfil
        // Por enquanto, apenas simular sucesso
        alert('Perfil atualizado com sucesso!');
        closeModal('edit-profile-modal');
        
        // Atualizar interface
        document.getElementById('profile-name').textContent = name;
        document.getElementById('profile-email').textContent = email;
        document.getElementById('info-name').textContent = name;
        document.getElementById('info-email').textContent = email;
        
    } catch (error) {
        console.error('Erro ao salvar perfil:', error);
        alert('Erro ao salvar perfil. Tente novamente.');
    }
}

// Salvar senha
async function savePassword() {
    const currentPassword = document.getElementById('current-password').value;
    const newPassword = document.getElementById('new-password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    
    if (!currentPassword || !newPassword || !confirmPassword) {
        alert('Por favor, preencha todos os campos!');
        return;
    }
    
    if (newPassword !== confirmPassword) {
        alert('As senhas não coincidem!');
        return;
    }
    
    if (newPassword.length < 6) {
        alert('A nova senha deve ter pelo menos 6 caracteres!');
        return;
    }
    
    try {
        // Aqui seria feita a requisição para alterar a senha
        // Por enquanto, apenas simular sucesso
        alert('Senha alterada com sucesso!');
        closeModal('change-password-modal');
        
        // Limpar formulário
        document.getElementById('change-password-form').reset();
        
    } catch (error) {
        console.error('Erro ao alterar senha:', error);
        alert('Erro ao alterar senha. Tente novamente.');
    }
}

// Fechar modais ao clicar fora
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('show');
    }
});
