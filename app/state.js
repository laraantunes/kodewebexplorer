// app/state.js - Gerenciamento Central de Estado e Toasts do KodeWeb Explorer

const AppState = {
    currentPath: '',
    currentFiles: [],
    selectedItems: [], // array com caminhos relativos
    viewMode: 'grid',  // 'grid' ou 'list'
    clipboard: { action: null, items: [] },
    isSearching: false,
    searchQuery: ''
};

function showToast(message, type = 'info', duration = 3500) {
    const container = document.getElementById('toast-container');
    if (!container) return;
    
    const toast = document.createElement('div');
    toast.style.padding = '12px 18px';
    toast.style.borderRadius = '8px';
    toast.style.color = '#ffffff';
    toast.style.fontSize = '13px';
    toast.style.fontWeight = '500';
    toast.style.boxShadow = '0 6px 20px rgba(0, 0, 0, 0.6)';
    toast.style.display = 'flex';
    toast.style.alignItems = 'center';
    toast.style.gap = '10px';
    toast.style.transition = 'opacity 0.3s, transform 0.3s';
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(20px)';
    toast.style.pointerEvents = 'auto';
    
    let icon = 'ℹ️';
    if (type === 'success') {
        toast.style.background = 'linear-gradient(135deg, #0f442b, #0a2e1d)';
        toast.style.border = '1px solid var(--accent-success)';
        icon = '✅';
    } else if (type === 'error') {
        toast.style.background = 'linear-gradient(135deg, #440d1f, #2e0915)';
        toast.style.border = '1px solid var(--accent-danger)';
        icon = '⚠️';
    } else if (type === 'warning') {
        toast.style.background = 'linear-gradient(135deg, #443209, #2b1f05)';
        toast.style.border = '1px solid var(--accent-warning)';
        icon = '⏳';
    } else {
        toast.style.background = 'linear-gradient(135deg, #1c0931, #140523)';
        toast.style.border = '1px solid var(--accent)';
    }
    
    toast.innerHTML = `<span>${icon}</span> <span>${message}</span>`;
    container.appendChild(toast);
    
    requestAnimationFrame(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    });
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(20px)';
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

function navigateTo(targetPath) {
    AppState.currentPath = targetPath.replace(/\\/g, '/').replace(/^\/+|\/+$/g, '');
    AppState.selectedItems = [];
    AppState.isSearching = false;
    
    const searchInput = document.getElementById('search-input');
    if (searchInput) searchInput.value = '';
    
    renderBreadcrumb();
    updateSelectionUI();
    loadCurrentFolder();
    
    if (typeof highlightTreeItem === 'function') {
        highlightTreeItem(AppState.currentPath);
    }
}

function renderBreadcrumb() {
    const list = document.getElementById('breadcrumb-list');
    if (!list) return;
    
    list.innerHTML = '';
    
    // Raiz do servidor
    const rootLi = document.createElement('li');
    rootLi.className = 'breadcrumb-item ' + (AppState.currentPath === '' ? 'active' : '');
    rootLi.innerHTML = '📁 Hospedagem Raiz';
    rootLi.onclick = () => navigateTo('');
    list.appendChild(rootLi);
    
    if (AppState.currentPath !== '') {
        const parts = AppState.currentPath.split('/');
        let accum = '';
        
        parts.forEach((part, idx) => {
            if (!part) return;
            accum += (accum === '' ? '' : '/') + part;
            const target = accum;
            
            const sep = document.createElement('span');
            sep.className = 'breadcrumb-separator';
            sep.innerText = '›';
            list.appendChild(sep);
            
            const li = document.createElement('li');
            li.className = 'breadcrumb-item ' + (idx === parts.length - 1 ? 'active' : '');
            li.innerText = part;
            li.onclick = () => navigateTo(target);
            list.appendChild(li);
        });
    }
    
    // Rolagem automática para a direita na barra de pão de forma
    const container = document.getElementById('breadcrumb-bar');
    if (container) container.scrollLeft = container.scrollWidth;
    
    // Atualiza estado do botão "Voltar" (desabilitado se estiver na raiz)
    const btnUp = document.getElementById('btn-navigate-up');
    if (btnUp) {
        if (AppState.currentPath === '') {
            btnUp.disabled = true;
            btnUp.style.opacity = '0.5';
            btnUp.style.pointerEvents = 'none';
        } else {
            btnUp.disabled = false;
            btnUp.style.opacity = '1';
            btnUp.style.pointerEvents = 'auto';
        }
    }
}

function navigateUp() {
    if (!AppState.currentPath || AppState.currentPath === '') {
        showToast("Você já está na raiz da hospedagem.", "warning", 2000);
        return;
    }
    const parts = AppState.currentPath.split('/');
    parts.pop();
    const parentPath = parts.join('/');
    navigateTo(parentPath);
}
