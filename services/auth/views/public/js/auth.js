/**
 * AuthService - Serviço centralizado para autenticação
 * Gerencia login, logout, verificação de usuário e interceptação de erros
 */
    function redirecionarParaPorta(novaPorta, caminho = '/') {
    const { protocol, hostname, search, hash } = window.location;
    const url = `${protocol}//${hostname}:${novaPorta}${caminho}${search}${hash}`;
    window.location.href = url;
  }
class AuthService {
    constructor() {
        this.currentUser = null;
        this.isAuthenticated = false;
        this.listeners = [];
        this.init();
    }

    /**
     * Inicializa o serviço verificando se há usuário logado
     */
    async init() {
        try {
            await this.checkAuth();
        } catch (error) {
            console.warn('Erro ao verificar autenticação:', error);
        }
    }

    /**
     * Verifica se o usuário está autenticado
     */
    
    async checkAuth() {
        try {
            const response = await this.fetchWithTimeout('/auth/api/me', {
                method: 'GET',
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            });

            if (response.ok) {
                const data = await response.json();
                if (data?.user) {
                    this.setUser(data.user);
                    return true;
                }
            }
            else {
                response = await this.fetchWithTimeout('/api/me', {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' }
                });
                if (response.ok) {
                const data = await response.json();
                if (data?.user) {
                    this.setUser(data.user);
                    return true;
                }else{
                    this.clearUser();
                    return false;
                }
            }
        }
            
            this.clearUser();
            return false;
        } catch (error) {
            console.warn('Erro ao verificar autenticação:', error);
            this.clearUser();
            return false;
        }
    }

    /**
     * Realiza login do usuário
     */
    async login(email, password) {
        try {
            const response = await this.fetchWithTimeout('/auth/api/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ email, password })
            });

            const data = await response.json();
            if (!response.ok) {
                response = await this.fetchWithTimeout('/api/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ email, password })
            });
            }

            if (response.ok && data.user) {
                this.setUser(data.user);
                redirecionarParaPorta(8080, '/books');
                return { success: true, user: data.user, message: data.message };
            } else {
                return { success: false, error: data.error || 'Erro no login' };
            }
        } catch (error) {
            return { success: false, error: 'Erro de conexão' };
        }
    }

    /**
     * Realiza logout do usuário
     */
    async logout() {
        try {
            await this.fetchWithTimeout('/api/logout', {
                method: 'POST',
                credentials: 'same-origin'
            });
        } catch (error) {
            console.warn('Erro ao fazer logout:', error);
        } finally {
            this.clearUser();
            window.location.href = '/login';
        }
    }

    /**
     * Registra novo usuário
     */
    async register(name, email, password) {
        try {
            const response = await this.fetchWithTimeout('/auth/api/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ name, email, password })
            });

            const data = await response.json();
            
            if (response.ok && data.user) {
                this.setUser(data.user);
                
                return { success: true, user: data.user, message: data.message };
            } else {
                const response = await this.fetchWithTimeout('/api/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ name, email, password })});
                const data = await response.json();
                if (response.ok && data.user) {
                    this.setUser(data.user);
                    
                    return { success: true, user: data.user, message: data.message };
                } else {
                    return { success: false, error: data.error || 'Erro ao registrar' };
                }
               
            }
        } catch (error) {
            return { success: false, error: 'Erro de conexão' };
        }
    }

    /**
     * Atualiza perfil do usuário
     */
    async updateProfile(name, email) {
        try {
            const response = await this.fetchWithTimeout('/api/update-profile', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ name, email })
            });

            const data = await response.json();

            if (response.ok && data.user) {
                this.setUser(data.user);
                return { success: true, user: data.user, message: data.message };
            } else {
                return { success: false, error: data.error || 'Erro ao atualizar perfil' };
            }
        } catch (error) {
            return { success: false, error: 'Erro de conexão' };
        }
    }

    /**
     * Altera senha do usuário
     */
    async changePassword(currentPassword, newPassword) {
        try {
            const response = await this.fetchWithTimeout('/api/change-password', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ 
                    current_password: currentPassword, 
                    new_password: newPassword 
                })
            });

            const data = await response.json();

            if (response.ok) {
                return { success: true, message: data.message };
            } else {
                return { success: false, error: data.error || 'Erro ao alterar senha' };
            }
        } catch (error) {
            return { success: false, error: 'Erro de conexão' };
        }
    }

    /**
     * Define o usuário atual e notifica listeners
     */
    setUser(user) {
        this.currentUser = user;
        this.isAuthenticated = true;
        localStorage.setItem('user', JSON.stringify(user));

        this.notifyListeners('login', user);
    }

    /**
     * Remove o usuário atual e notifica listeners
     */
    clearUser() {
        this.currentUser = null;
        this.isAuthenticated = false;
        localStorage.removeItem('user');
        this.notifyListeners('logout');
    }

    /**
     * Adiciona listener para eventos de autenticação
     */
    addListener(callback) {
        this.listeners.push(callback);
    }

    /**
     * Remove listener
     */
    removeListener(callback) {
        const index = this.listeners.indexOf(callback);
        if (index > -1) {
            this.listeners.splice(index, 1);
        }
    }

    /**
     * Notifica todos os listeners sobre mudanças de autenticação
     */
    notifyListeners(event, data = null) {
        this.listeners.forEach(callback => {
            try {
                callback(event, data);
            } catch (error) {
                console.error('Erro em listener de autenticação:', error);
            }
        });
    }

    /**
     * Requer autenticação - redireciona se não autenticado
     */
    requireAuth() {
        if (!this.isAuthenticated) {
            window.location.href = 'login';
            return false;
        }
        return true;
    }

    /**
     * Verifica se o usuário tem role específica
     */
    hasRole(role) {
        return this.isAuthenticated && this.currentUser?.role === role;
    }

    /**
     * Requer role específica - redireciona se não tiver permissão
     */
    requireRole(role) {
        if (!this.requireAuth()) return false;
        if (!this.hasRole(role)) {
            window.location.href = 'login';
            return false;
        }
        return true;
    }

    /**
     * Fetch com timeout e tratamento de erros de autenticação
     */
    async fetchWithTimeout(url, options = {}, timeout = 10000) {
        const controller = new AbortController();
        const id = setTimeout(() => controller.abort(), timeout);
        
        try {
            const response = await fetch(url, { 
                ...options, 
                signal: controller.signal 
            });
            clearTimeout(id);

            // Intercepta erros de autenticação
            if (response.status === 401) {
                this.clearUser();
                if (window.location.pathname !== 'login') {
                    window.location.href = 'login';
                }
                throw new Error('Não autenticado');
            }

            if (response.status === 403) {
                throw new Error('Acesso negado');
            }

            return response;
        } catch (error) {
            clearTimeout(id);
            throw error;
        }
    }

    /**
     * Fetch genérico com interceptação automática
     */
    async fetch(url, options = {}) {
        return this.fetchWithTimeout(url, {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                ...options.headers
            },
            ...options
        });
    }
}

// Instância global do AuthService
window.AuthService = new AuthService();

// Exporta para uso em módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AuthService;
}
