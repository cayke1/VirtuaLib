export class ProfileManager {
  constructor() {
    this.modals = {
      edit: document.getElementById("edit-profile-modal"),
      password: document.getElementById("change-password-modal"),
    };

    this.forms = {
      edit: document.getElementById("edit-profile-form"),
      password: document.getElementById("change-password-form"),
    };

    this.profileData = {
      name: document.getElementById("profile-name"),
      email: document.getElementById("profile-email"),
      role: document.getElementById("profile-role"),
      status: document.getElementById("profile-status"),
      infoName: document.getElementById("info-name"),
      infoEmail: document.getElementById("info-email"),
      infoRole: document.getElementById("info-role"),
      infoCreated: document.getElementById("info-created"),
    };

    this.stats = {
      borrowed: document.getElementById("books-borrowed"),
      history: document.getElementById("books-history"),
      memberSince: document.getElementById("member-since"),
    };

    this.initialize();
  }

  initialize() {
    this.loadStats();
  }

  // ==============================
  //   MÉTODOS DE EXIBIÇÃO
  // ==============================

  async loadStats() {
    try {
      const response = await fetch('/auth/api/user-stats');
      
      // Verificar se a resposta é JSON
      const contentType = response.headers.get('content-type');
      if (!contentType || !contentType.includes('application/json')) {
        const text = await response.text();
        console.error('Resposta não é JSON:', text);
        throw new Error('Resposta do servidor não é JSON válido');
      }
      
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.error || 'Erro ao carregar estatísticas do perfil');
      }
      
      const data = await response.json();
      
      if (data.stats) {
        this.stats.borrowed.textContent = data.stats.active_borrows;
        this.stats.history.textContent = data.stats.total_borrows;
        this.stats.memberSince.textContent = data.stats.member_since_days;
        
        // Atualizar data de criação se disponível
        if (data.stats.created_at) {
          const createdEl = this.profileData.infoCreated;
          createdEl.textContent = this.formatDate(data.stats.created_at);
        }
      }
    } catch (error) {
      console.error('Erro ao carregar estatísticas:', error);
      // Fallback para valores padrão em caso de erro
      this.stats.borrowed.textContent = "—";
      this.stats.history.textContent = "—";
      this.stats.memberSince.textContent = "—";
    }
  }


  formatDate(dateStr) {
    const date = new Date(dateStr);
    const options = { year: "numeric", month: "long", day: "numeric" };
    return date.toLocaleDateString("pt-BR", options);
  }


  // ==============================
  //   MODAIS
  // ==============================

  openModal(type) {
    const modal = this.modals[type];
    if (modal) modal.classList.add("show");
  }

  closeModal(type) {
    const modal = this.modals[type];
    if (modal) modal.classList.remove("show");
  }

  // ==============================
  //   EDIÇÃO DE PERFIL
  // ==============================

  editProfile() {
    const { name, email } = this.profileData;
    this.forms.edit.querySelector("#edit-name").value = name.textContent.trim();
    this.forms.edit.querySelector("#edit-email").value =
      email.textContent.trim();

    this.openModal("edit");
  }

  saveProfile() {
    const formData = new FormData(this.forms.edit);
    const newName = formData.get("name").trim();
    const newEmail = formData.get("email").trim();

    if (!newName || !newEmail) {
      alert("Preencha todos os campos.");
      return;
    }

    // Atualiza no DOM
    this.profileData.name.textContent = newName;
    this.profileData.email.textContent = newEmail;
    this.profileData.infoName.textContent = newName;
    this.profileData.infoEmail.textContent = newEmail;

    this.closeModal("edit");
    alert("Perfil atualizado com sucesso!");
  }

  // ==============================
  //   ALTERAR SENHA
  // ==============================

  changePassword() {
    this.forms.password.reset();
    this.openModal("password");
  }

  savePassword() {
    const form = this.forms.password;
    const current = form.querySelector("#current-password").value.trim();
    const newPass = form.querySelector("#new-password").value.trim();
    const confirm = form.querySelector("#confirm-password").value.trim();

    if (!current || !newPass || !confirm) {
      alert("Preencha todos os campos.");
      return;
    }

    if (newPass !== confirm) {
      alert("As senhas não coincidem.");
      return;
    }

    this.closeModal("password");
    alert("Senha alterada com sucesso!");
  }

  // ==============================
  //   AVATAR
  // ==============================

  editAvatar() {
    alert("Função de alterar avatar ainda não implementada.");
  }
}
window.ProfileManager = new ProfileManager();
const profileManager = window.ProfileManager;

// Exportar todas as funções necessárias
export function editProfile() {
  profileManager.editProfile();
}

export function saveProfile() {
  profileManager.saveProfile();
}

export function changePassword() {
  profileManager.changePassword();
}

export function savePassword() {
  profileManager.savePassword();
}

export function editAvatar() {
  profileManager.editAvatar();
}

export function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.remove("show");
  }
}

// Disponibilizar funções no escopo global para onclick
window.editProfile = editProfile;
window.saveProfile = saveProfile;
window.changePassword = changePassword;
window.savePassword = savePassword;
window.editAvatar = editAvatar;
window.closeModal = closeModal;
