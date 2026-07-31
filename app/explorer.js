// app/explorer.js - Controlador da Árvore Lateral e Navegação Mobile (Gavetas)

async function loadTreeRoot() {
    const rootUl = document.getElementById('main-tree-ul');
    if (!rootUl) return;
    
    rootUl.innerHTML = '<li style="color: var(--text-muted); font-size: 12px; padding: 10px;">Carregando diretórios...</li>';
    
    const res = await apiGet('files', { action: 'list_tree', path: '' });
    if (!res.success || !res.folders) {
        rootUl.innerHTML = '<li style="color: var(--accent-danger); font-size: 12px; padding: 10px;">Erro ao ler pastas</li>';
        return;
    }
    
    rootUl.innerHTML = '';
    
    // Nó raiz opcional para clique rápido
    const rootNode = document.createElement('li');
    rootNode.className = 'tree-node';
    rootNode.innerHTML = `
        <div class="tree-item ${AppState.currentPath === '' ? 'active' : ''}" onclick="navigateTo('')">
            <span class="tree-icon" style="color: #00ff88;">🏠</span>
            <span>Raiz</span>
        </div>
    `;
    rootUl.appendChild(rootNode);
    
    res.folders.forEach(f => {
        rootUl.appendChild(createTreeNode(f));
    });
    
    highlightTreeItem(AppState.currentPath);
}

function createTreeNode(folder) {
    const li = document.createElement('li');
    li.className = 'tree-node';
    li.setAttribute('data-path', folder.path);
    
    const hasChildren = folder.has_children;
    const toggleIcon = hasChildren ? '▶' : '';
    const safeId = 'tree-sub-' + folder.path.replace(/[^a-zA-Z0-9]/g, '_');
    
    li.innerHTML = `
        <div class="tree-item" data-item-path="${folder.path}" onclick="handleTreeItemClick(event, '${folder.path}', ${hasChildren})">
            <span class="tree-toggle ${hasChildren ? 'has-child' : ''}" onclick="handleToggleClick(event, '${folder.path}')">${toggleIcon}</span>
            <span class="tree-icon">📂</span>
            <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${folder.name}</span>
        </div>
        <ul class="tree-list" id="${safeId}" data-sub-path="${folder.path}" style="display: none;"></ul>
    `;
    
    return li;
}

async function handleTreeItemClick(event, relPath, hasChildren) {
    event.stopPropagation();
    navigateTo(relPath);
    if (window.innerWidth <= 768) {
        closeMobileDrawers();
    }
    if (hasChildren) {
        expandTreePath(relPath);
    }
}

async function handleToggleClick(event, relPath) {
    event.stopPropagation();
    expandTreePath(relPath, true);
}

async function expandTreePath(relPath, toggle = false) {
    const safeId = 'tree-sub-' + relPath.replace(/[^a-zA-Z0-9]/g, '_');
    const subUl = document.getElementById(safeId) || Array.from(document.querySelectorAll('ul.tree-list')).find(el => el.getAttribute('data-sub-path') === relPath);
    if (!subUl) return;
    
    const nodeItem = Array.from(document.querySelectorAll('.tree-item')).find(el => el.getAttribute('data-item-path') === relPath);
    const toggleSpan = nodeItem ? nodeItem.querySelector('.tree-toggle') : null;
    
    if (subUl.style.display === 'block' && toggle) {
        subUl.style.display = 'none';
        if (toggleSpan) toggleSpan.classList.remove('expanded');
        return;
    }
    
    subUl.style.display = 'block';
    if (toggleSpan) toggleSpan.classList.add('expanded');
    
    // Lazy loading
    if (subUl.children.length === 0) {
        subUl.innerHTML = '<li style="font-size:11px; color:var(--text-muted); padding:2px 10px;">Lendo...</li>';
        const res = await apiGet('files', { action: 'list_tree', path: relPath });
        subUl.innerHTML = '';
        if (res.success && res.folders) {
            res.folders.forEach(f => {
                subUl.appendChild(createTreeNode(f));
            });
        }
    }
}

async function highlightTreeItem(targetPath) {
    document.querySelectorAll('.tree-item').forEach(el => el.classList.remove('active'));
    
    if (!targetPath || targetPath === '') {
        const rootEl = document.querySelector('#main-tree-ul > li > .tree-item') || document.querySelector('.tree-list.root-list > li > .tree-item');
        if (rootEl) {
            rootEl.classList.add('active');
            rootEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
        return;
    }
    
    // Garantir que todas as pastas pai estejam carregadas e expandidas na árvore
    const parts = targetPath.split('/');
    let accum = '';
    for (let i = 0; i < parts.length - 1; i++) {
        if (!parts[i]) continue;
        accum += (accum === '' ? '' : '/') + parts[i];
        await expandTreePath(accum, false);
    }
    
    // Agora buscar o elemento exato sem riscos de falha por caracteres especiais
    const targetEl = Array.from(document.querySelectorAll('.tree-item')).find(el => el.getAttribute('data-item-path') === targetPath);
    if (targetEl) {
        targetEl.classList.add('active');
        targetEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

// --- CONTROLES DE GAVETA / DRAWER EM CELULARES (RESPONSIVO <= 768px) ---
function toggleMobileDrawer(side) {
    const leftPanel = document.getElementById('panel-left');
    const rightPanel = document.getElementById('panel-right');
    const overlay = document.getElementById('mobile-overlay');
    const navTree = document.getElementById('mobile-nav-tree');
    const navFiles = document.getElementById('mobile-nav-files');
    const navDetails = document.getElementById('mobile-nav-details');
    
    if (side === 'left') {
        const isOpening = !leftPanel.classList.contains('mobile-active');
        closeMobileDrawers();
        if (isOpening) {
            leftPanel.classList.add('mobile-active');
            overlay.classList.add('active');
            navTree.classList.add('active');
            navFiles.classList.remove('active');
        }
    } else if (side === 'right') {
        const isOpening = !rightPanel.classList.contains('mobile-active');
        closeMobileDrawers();
        if (isOpening) {
            rightPanel.classList.add('mobile-active');
            overlay.classList.add('active');
            navDetails.classList.add('active');
            navFiles.classList.remove('active');
        }
    }
}

function closeMobileDrawers() {
    const leftPanel = document.getElementById('panel-left');
    const rightPanel = document.getElementById('panel-right');
    const overlay = document.getElementById('mobile-overlay');
    const navTree = document.getElementById('mobile-nav-tree');
    const navFiles = document.getElementById('mobile-nav-files');
    const navDetails = document.getElementById('mobile-nav-details');
    
    if (leftPanel) leftPanel.classList.remove('mobile-active');
    if (rightPanel) rightPanel.classList.remove('mobile-active');
    if (overlay) overlay.classList.remove('active');
    
    if (navTree) navTree.classList.remove('active');
    if (navDetails) navDetails.classList.remove('active');
    if (navFiles) navFiles.classList.add('active');
}
