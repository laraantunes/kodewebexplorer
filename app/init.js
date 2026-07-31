// app/init.js - Resizer Fluido dos Painéis, Atalhos de Teclado, Modais e Auto-Update GitHub

let targetActionForDestination = '';

// --- INITIALIZADOR DO DE AMBIENTE E REDIMENSIONADORES ---
document.addEventListener('DOMContentLoaded', () => {
    initPanelResizers();
    initKeyboardShortcuts();
    
    // Restore View Mode
    const savedViewMode = localStorage.getItem('kw_explorer_view_mode');
    if (savedViewMode) {
        AppState.viewMode = savedViewMode;
        document.getElementById('btn-view-grid').classList.toggle('active', savedViewMode === 'grid');
        document.getElementById('btn-view-list').classList.toggle('active', savedViewMode === 'list');
    }
    
    // Restore Details Panel state
    if (window.innerWidth > 768) {
        const savedDetailsState = localStorage.getItem('kw_explorer_details_open');
        const pRight = document.getElementById('panel-right');
        const rRight = document.getElementById('resizer-right');
        const btnToggle = document.getElementById('btn-toggle-props');
        if (savedDetailsState === 'false') {
            if (pRight) pRight.style.display = 'none';
            if (rRight) rRight.style.display = 'none';
            if (btnToggle) btnToggle.classList.remove('active');
        } else if (savedDetailsState === 'true') {
            if (pRight) pRight.style.display = 'flex';
            if (rRight) rRight.style.display = 'block';
            if (btnToggle) btnToggle.classList.add('active');
        }
    }

    // Restore Path
    const savedPath = localStorage.getItem('kw_explorer_path');
    if (savedPath !== null) {
        AppState.currentPath = savedPath;
    }
    
    // Inicia a renderização principal
    renderBreadcrumb();
    loadTreeRoot();
    loadCurrentFolder();
});

// Resizer Vertical dos Painéis Esquerdo e Direito (Desktop)
function initPanelResizers() {
    const resizerLeft = document.getElementById('resizer-left');
    const resizerRight = document.getElementById('resizer-right');
    const panelLeft = document.getElementById('panel-left');
    const panelRight = document.getElementById('panel-right');
    
    if (resizerLeft && panelLeft) {
        let isResizingLeft = false;
        resizerLeft.addEventListener('mousedown', (e) => {
            isResizingLeft = true;
            resizerLeft.classList.add('resizing');
            document.body.style.cursor = 'col-resize';
            e.preventDefault();
        });
        document.addEventListener('mousemove', (e) => {
            if (!isResizingLeft) return;
            let newWidth = e.clientX;
            if (newWidth >= 180 && newWidth <= 480) {
                panelLeft.style.width = newWidth + 'px';
            }
        });
        document.addEventListener('mouseup', () => {
            if (isResizingLeft) {
                isResizingLeft = false;
                resizerLeft.classList.remove('resizing');
                document.body.style.cursor = 'default';
            }
        });
    }

    if (resizerRight && panelRight) {
        let isResizingRight = false;
        resizerRight.addEventListener('mousedown', (e) => {
            isResizingRight = true;
            resizerRight.classList.add('resizing');
            document.body.style.cursor = 'col-resize';
            e.preventDefault();
        });
        document.addEventListener('mousemove', (e) => {
            if (!isResizingRight) return;
            let newWidth = window.innerWidth - e.clientX;
            if (newWidth >= 220 && newWidth <= 450) {
                panelRight.style.width = newWidth + 'px';
            }
        });
        document.addEventListener('mouseup', () => {
            if (isResizingRight) {
                isResizingRight = false;
                resizerRight.classList.remove('resizing');
                document.body.style.cursor = 'default';
            }
        });
    }
}

// Atalhos Globais
function initKeyboardShortcuts() {
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const activeModal = document.querySelector('.modal-overlay.active');
            if (activeModal) {
                if (activeModal.id === 'modal-viewer' && typeof closeViewerModal === 'function') {
                    closeViewerModal();
                } else {
                    activeModal.classList.remove('active');
                }
                return;
            }
        }

        // Ignora se estiver dentro de um campo de texto ou input
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') {
            return;
        }

        // Ctrl+S / Cmd+S para salvar (já capturado no Ace, mas se estiver focado fora)
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
            e.preventDefault();
            const viewerModal = document.getElementById('modal-viewer');
            if (viewerModal && viewerModal.classList.contains('active')) {
                saveAceEditorContent();
            }
        }
        
        // Ctrl+A para selecionar tudo no Explorer
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'a') {
            e.preventDefault();
            const viewerModal = document.getElementById('modal-viewer');
            if (!viewerModal || !viewerModal.classList.contains('active')) {
                toggleSelectAll();
            }
        }
        
        // Tecla Delete ou Backspace para excluir item selecionado
        if (e.key === 'Delete' || e.key === 'Backspace') {
            const anyModal = document.querySelector('.modal-overlay.active');
            if (!anyModal && AppState.selectedItems.length > 0) {
                e.preventDefault();
                handleAction('delete');
            }
        }
    });
}

// --- CONTROLE DOS MODAIS DE OPÇÕES, WORKSPACE E SOBRE ---
function openOptionsModal(defaultTab = 'workspace') {
    const modal = document.getElementById('modal-options');
    if (!modal) return;
    
    // Carrega dados vigentes
    const pathInput = document.getElementById('opt-workspace-path');
    const localCheck = document.getElementById('opt-is-local');
    if (pathInput) pathInput.value = INITIAL_WORKSPACE || '';
    if (localCheck) localCheck.checked = (IS_LOCAL === 1 || IS_LOCAL === true || IS_LOCAL === '1');
    
    switchOptionsTab(defaultTab);
    modal.classList.add('active');
}

function switchOptionsTab(tabName) {
    ['workspace', 'security', 'about'].forEach(t => {
        const btn = document.getElementById(`tab-btn-${t}`);
        const view = document.getElementById(`tab-view-${t}`);
        if (btn) btn.classList.toggle('active', t === tabName);
        if (view) view.style.display = t === tabName ? 'block' : 'none';
    });
}

async function saveWorkspaceSettings(event) {
    event.preventDefault();
    const pathVal = document.getElementById('opt-workspace-path').value.trim();
    const isLocalVal = document.getElementById('opt-is-local').checked ? '1' : '0';
    
    const res = await apiPost('options', { action: 'save_settings', workspace_path: pathVal, is_local: isLocalVal });
    if (res.success) {
        showToast("✅ Configurações de Workspace salvas! Recarregando aplicação...", "success");
        setTimeout(() => window.location.reload(), 1200);
    }
}

async function updateAdminCredentials(event) {
    event.preventDefault();
    const userVal = document.getElementById('sec-username').value.trim();
    const passVal = document.getElementById('sec-password').value;
    
    if (!userVal || !passVal) return;
    
    const res = await apiPost('options', { action: 'update_credentials', username: userVal, new_password: passVal });
    if (res.success) {
        showToast("🔒 Senha re-criptografada com sucesso no arquivo `auth.enc`!", "success");
        document.getElementById('sec-password').value = '';
    }
}

// --- AUTO-UPDATE SILENCIOSO VIA GITHUB ---
async function checkGithubUpdate() {
    const btn = document.getElementById('btn-github-update');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '⏳ Conectando ao GitHub API...';
    }
    showToast("Verificando se há novas versões em laraantunes/kodewebexplorer...", "info", 5000);
    
    const res = await apiGet('options', { action: 'update_app' });
    if (btn) {
        btn.disabled = false;
        btn.innerHTML = '🔍 Buscar Atualizações no GitHub';
    }
    
    if (res.success) {
        showToast(`🚀 ${res.message}`, "success", 7000);
        setTimeout(() => window.location.reload(), 2500);
    } else {
        showToast(`ℹ️ ${res.error || 'Nenhuma atualização disponível nesta release.'}`, "warning", 6000);
    }
}

// --- MODAL NOVO ITEM (PASTA OU ARQUIVO) ---
function openNewItemModal(type) {
    const modal = document.getElementById('modal-new-item');
    const title = document.getElementById('new-item-title');
    const label = document.getElementById('new-item-label');
    const input = document.getElementById('new-item-input');
    const hiddenType = document.getElementById('new-item-type');
    
    if (!modal) return;
    
    if (type === 'folder') {
        title.innerText = '📁 Criar Nova Pasta';
        label.innerText = 'Nome do Diretório (será criado na pasta atual)';
        input.placeholder = 'ex: imagens';
        hiddenType.value = 'folder';
    } else {
        title.innerText = '📄 Criar Novo Arquivo';
        label.innerText = 'Nome e extensão do arquivo de texto ou código';
        input.placeholder = 'ex: script.js ou notas.txt';
        hiddenType.value = 'file';
    }
    
    input.value = '';
    modal.classList.add('active');
    setTimeout(() => input.focus(), 100);
}

async function submitNewItem(event) {
    event.preventDefault();
    const type = document.getElementById('new-item-type').value;
    const name = document.getElementById('new-item-input').value.trim();
    
    if (!name) return;
    
    const action = type === 'folder' ? 'create_folder' : 'create_file';
    const res = await apiPost('files', { action: action, parent: AppState.currentPath, name: name });
    
    if (res.success) {
        showToast(res.message, "success");
        closeModal('modal-new-item');
        loadCurrentFolder();
        loadTreeRoot(); // Atualizar árvore de pastas
    }
}

// --- MODAL DE DESTINO PARA MOVER / COPIAR ---
async function openDestinationModal(actionType) {
    targetActionForDestination = actionType;
    const modal = document.getElementById('modal-destination');
    const title = document.getElementById('dest-modal-title');
    const treeDiv = document.getElementById('destination-tree-container');
    
    if (!modal || !treeDiv) return;
    
    title.innerText = actionType === 'move' ? '📦 Mover para pasta...' : '📋 Copiar para pasta...';
    treeDiv.innerHTML = 'Lendo estrutura da hospedagem...';
    modal.classList.add('active');
    
    const res = await apiGet('files', { action: 'list_tree', path: '' });
    if (res.success && res.folders) {
        let html = '<ul class="tree-list root-list">';
        html += `<li class="tree-node"><div class="tree-item dest-item active" data-dest="" onclick="selectDestItem('', this)">🏠 Raiz</div></li>`;
        res.folders.forEach(f => {
            html += `<li class="tree-node"><div class="tree-item dest-item" data-dest="${f.path}" onclick="selectDestItem('${f.path}', this)">📂 ${f.name}</div></li>`;
        });
        html += '</ul>';
        treeDiv.innerHTML = html;
        
        // Atribuir o handler ao botão de confirmar
        const btnConf = document.getElementById('confirm-dest-btn');
        let selectedDestPath = '';
        window.selectDestItem = (path, el) => {
            document.querySelectorAll('.dest-item').forEach(x => x.classList.remove('active'));
            el.classList.add('active');
            selectedDestPath = path;
        };
        btnConf.onclick = async () => {
            const resAction = await apiPost('files', { action: targetActionForDestination, items: AppState.selectedItems, destination: selectedDestPath });
            if (resAction.success) {
                showToast(resAction.message, "success");
                closeModal('modal-destination');
                AppState.selectedItems = [];
                loadCurrentFolder();
                if (typeof updateDetailsPanel === 'function') updateDetailsPanel();
            }
        };
    }
}

function closeModal(modalId) {
    const el = document.getElementById(modalId);
    if (el) el.classList.remove('active');
}
