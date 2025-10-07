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
    this.setMemberSince();
  }

  // ==============================
  //   MÉTODOS DE EXIBIÇÃO
  // ==============================

  loadStats() {
    this.stats.borrowed.textContent = Math.floor(Math.random() * 4) + 1;
    this.stats.history.textContent = Math.floor(Math.random() * 10 + 3);
  }

  setMemberSince() {
    const createdEl = this.profileData.infoCreated;
    const user = JSON.parse(localStorage.getItem("user"));
    const createdAt = user.created_at;
    if (createdAt) {
      const days = Math.floor(
        (new Date() - new Date(createdAt)) / (1000 * 60 * 60 * 24)
      );
      this.stats.memberSince.textContent = days;
      if (days <= 0) {
        this.stats.memberSince.textContent = "0";
      }
      createdEl.textContent = this.formatDate(createdAt);
    } else {
      createdEl.textContent = "—";
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
