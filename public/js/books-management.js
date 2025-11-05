// Books Management JavaScript
let books = [];
let editingBookId = null;

// Função para obter URL correta da imagem (suporta tanto URLs locais quanto do R2)
function getImageUrl(imagePath) {
    if (!imagePath) return null;
    
    // Se já é uma URL completa (http/https), retornar como está
    if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
        return imagePath;
    }
    
    // Se começa com /public/, remover o prefixo /public
    if (imagePath.startsWith('/public/')) {
        imagePath = imagePath.substring(7); // Remove '/public'
    }
    
    // Se começa com /uploads/, adicionar /public
    if (imagePath.startsWith('/uploads/')) {
        return '/public' + imagePath;
    }
    
    // Se não tem prefixo, assumir que é um caminho local
    return '/public/uploads/covers/' + imagePath.replace(/^\//, '');
}

// Carregar livros ao inicializar
document.addEventListener('DOMContentLoaded', function() {
    loadBooks();
});

// Carregar lista de livros
async function loadBooks() {
    try {
        const response = await fetch('/books/api/books');
        const data = await response.json();
        
        if (data.books) {
            books = data.books;
            renderBooksTable();
        } else {
            showToast('error', 'Erro ao carregar livros: ' + (data.error || 'Erro desconhecido'), 'Erro');
        }
    } catch (error) {
        console.error('Erro ao carregar livros:', error);
        showToast('error', 'Erro de conexão ao carregar livros', 'Erro');
    }
}

// Renderizar tabela de livros
function renderBooksTable() {
    const tbody = document.getElementById('books-tbody');
    
    if (books.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="empty-state">
                    <div class="empty-state-icon">📚</div>
                    <div>Nenhum livro encontrado</div>
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = books.map(book => `
        <tr>
            <td class="book-cover-cell">
                ${book.cover_image ? 
                    `<img src="${getImageUrl(book.cover_image)}" alt="Capa de ${escapeHtml(book.title)}" class="table-cover-image" onerror="this.src='/public/css/placeholder-book.svg'">` : 
                    '<div class="table-cover-placeholder">📖</div>'
                }
            </td>
            <td>${book.id}</td>
            <td>
                <div class="book-title">${escapeHtml(book.title)}</div>
            </td>
            <td>
                <div class="book-author">${escapeHtml(book.author)}</div>
            </td>
            <td>
                <span class="book-genre">${escapeHtml(book.genre)}</span>
            </td>
            <td>
                <div class="book-year">${book.year}</div>
            </td>
            <td>
                <span class="status-badge ${book.available ? 'status-available' : 'status-unavailable'}">
                    ${book.available ? 'Disponível' : 'Indisponível'}
                </span>
            </td>
            <td>
                <div class="action-buttons">
                    <button class="btn-sm btn-edit" onclick="editBook(${book.id})">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="m18.5 2.5 a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Editar
                    </button>
                    <button class="btn-sm btn-delete" onclick="deleteBook(${book.id})">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <polyline points="3,6 5,6 21,6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="m19,6v14a2,2 0 0,1 -2,2H7a2,2 0 0,1 -2,-2V6m3,0V4a2,2 0 0,1 2,-2h4a2,2 0 0,1 2,2v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <line x1="10" y1="11" x2="10" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <line x1="14" y1="11" x2="14" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Excluir
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Abrir modal para criar livro
function openCreateModal() {
    editingBookId = null;
    document.getElementById('modal-title').textContent = 'Adicionar Livro';
    document.getElementById('submit-btn').textContent = 'Salvar';
    clearForm();
    document.getElementById('available').checked = true;
    document.getElementById('book-modal').style.display = 'block';
}

// Editar livro
async function editBook(id) {
    try {
        const response = await fetch(`/books/api/books/${id}`);
        const data = await response.json();
        
        let book = data.book || data.data || data;
        
        if (!book || !book.id) {
            showToast('error', 'Formato de resposta inválido da API', 'Erro');
            return;
        }
        
        editingBookId = id;
        document.getElementById('modal-title').textContent = 'Editar Livro';
        document.getElementById('submit-btn').textContent = 'Atualizar';
        
        // Preencher campos
        document.getElementById('title').value = book.title || '';
        document.getElementById('author').value = book.author || '';
        document.getElementById('genre').value = book.genre || '';
        document.getElementById('year').value = book.year || '';
        document.getElementById('description').value = book.description || '';
        
        const availableCheckbox = document.getElementById('available');
        if (availableCheckbox) {
            availableCheckbox.checked = book.available == 1;
        }
        
        // ✅ Mostrar PDF atual se existir
        const pdfPreview = document.getElementById('pdf-preview');
        const pdfLink = document.getElementById('pdf-link');
        
        if (pdfPreview && pdfLink && book.pdf_file) {
            pdfLink.href = book.pdf_file;
            pdfPreview.style.display = 'block';
        } else if (pdfPreview) {
            pdfPreview.style.display = 'none';
        }
        
        // Mostrar imagem atual
        const preview = document.getElementById('image-preview');
        const previewImg = document.getElementById('preview-img');
        
        if (preview && previewImg) {
            if (book.cover_image) {
                previewImg.src = book.cover_image;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        }
        
        document.getElementById('book-modal').style.display = 'block';
        
    } catch (error) {
        console.error('Erro ao carregar livro:', error);
        showToast('error', `Erro ao carregar livro: ${error.message}`, 'Erro');
    }
}

// Deletar livro
async function deleteBook(id) {
    // Criar toast de confirmação mais robusto
    const confirmToast = showToast('warning', 'Esta ação não pode ser desfeita. Tem certeza que deseja excluir este livro?', 'Confirmar Exclusão', 0);
    
    // Aguardar um pouco para o toast aparecer
    setTimeout(() => {
        const toastElement = document.querySelector(`[data-toast-id="${confirmToast}"]`);
        if (toastElement) {
            // Ajustar layout do toast
            toastElement.style.minWidth = '450px';
            toastElement.style.maxWidth = '600px';
            
            const buttonContainer = document.createElement('div');
            buttonContainer.className = 'toast-buttons';
            
            const confirmBtn = document.createElement('button');
            confirmBtn.textContent = 'Sim, Excluir';
            confirmBtn.style.cssText = `
                background: #ef4444;
                color: white;
                border: none;
                padding: 0.5rem 1rem;
                border-radius: 0.375rem;
                cursor: pointer;
                font-size: 0.875rem;
                font-weight: 500;
                transition: all 0.2s;
            `;
            
            const cancelBtn = document.createElement('button');
            cancelBtn.textContent = 'Cancelar';
            cancelBtn.style.cssText = `
                background: #6b7280;
                color: white;
                border: none;
                padding: 0.5rem 1rem;
                border-radius: 0.375rem;
                cursor: pointer;
                font-size: 0.875rem;
                font-weight: 500;
                transition: all 0.2s;
            `;
            
            confirmBtn.onclick = async () => {
                toastManager.dismiss(confirmToast);
                await performDelete(id);
            };
            
            cancelBtn.onclick = () => {
                toastManager.dismiss(confirmToast);
            };
            
            buttonContainer.appendChild(confirmBtn);
            buttonContainer.appendChild(cancelBtn);
            
            const content = toastElement.querySelector('.toast-content');
            content.appendChild(buttonContainer);
        }
    }, 100);
}

// Função separada para executar a exclusão
async function performDelete(id) {
    try {
        const response = await fetch(`/books/api/books/${id}/delete`, {
            method: 'POST'
        });
        const data = await response.json();
        
        if (data.message) {
            showToast('success', 'Livro excluído com sucesso!', 'Sucesso');
            loadBooks();
        } else {
            showToast('error', 'Erro ao excluir livro: ' + (data.error || 'Erro desconhecido'), 'Erro');
        }
    } catch (error) {
        console.error('Erro ao excluir livro:', error);
        showToast('error', 'Erro de conexão ao excluir livro', 'Erro');
    }
}

// Fechar modal
function closeModal() {
    document.getElementById('book-modal').style.display = 'none';
    editingBookId = null;
    clearForm();
}

function clearForm() {
    document.getElementById('book-form').reset();
    document.getElementById('image-preview').style.display = 'none';
    document.getElementById('preview-img').src = '';
}

// Submeter formulário
document.getElementById('book-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const coverImage = formData.get('cover_image');
    const pdfFile = formData.get('pdf_file');
    
    // Verificar se há arquivos para upload
    const hasImage = coverImage && coverImage.size > 0;
    const hasPdf = pdfFile && pdfFile.size > 0;
    const hasFiles = hasImage || hasPdf;
    
    try {
        let url, method, body;
        
        if (editingBookId) {
            url = `/books/api/books/${editingBookId}/update`;
            method = 'POST';
        } else {
            url = '/books/api/books/create';
            method = 'POST';
        }

        if (hasFiles) {
            // ✅ Upload com arquivos - usar FormData
            // Adiciona os campos de texto ao FormData
            formData.set('available', formData.get('available') ? '1' : '0');
            
            // Remove arquivos vazios do FormData
            if (!hasImage) {
                formData.delete('cover_image');
            }
            if (!hasPdf) {
                formData.delete('pdf_file');
            }
            
            body = formData;
        } else {
            // ✅ Sem arquivos - usar JSON puro
            const bookData = {
                title: formData.get('title'),
                author: formData.get('author'),
                genre: formData.get('genre'),
                year: parseInt(formData.get('year')),
                description: formData.get('description'),
                available: formData.get('available') ? 1 : 0
            };
            body = JSON.stringify(bookData);
        }

        const response = await fetch(url, {
            method: method,
            headers: hasFiles ? {} : {
                'Content-Type': 'application/json'
            },
            body: body
        });

        const data = await response.json();
        
        if (data.message || data.id) {
            showToast('success', editingBookId ? 'Livro atualizado com sucesso!' : 'Livro criado com sucesso!', 'Sucesso');
            closeModal();
            loadBooks();
        } else {
            showToast('error', 'Erro ao salvar livro: ' + (data.error || 'Erro desconhecido'), 'Erro');
        }
    } catch (error) {
        console.error('Erro ao salvar livro:', error);
        showToast('error', 'Erro de conexão ao salvar livro', 'Erro');
    }
});

// Escapar HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Preview de imagem
document.getElementById('cover_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview-img');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
    }
});

// Remover imagem
document.getElementById('remove-image').addEventListener('click', function() {
    document.getElementById('cover_image').value = '';
    document.getElementById('image-preview').style.display = 'none';
    document.getElementById('preview-img').src = '';
});

// Fechar modal ao clicar fora
window.onclick = function(event) {
    const modal = document.getElementById('book-modal');
    if (event.target === modal) {
        closeModal();
    }
}
