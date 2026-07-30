// app/details.js - Controlador em Tempo Real do Painel Direito (Metadados & Miniaturas)

function updateDetailsPanel() {
    const iconBox = document.getElementById('detail-big-icon');
    const imgBox = document.getElementById('detail-preview-img');
    const title = document.getElementById('detail-title');
    const subtitle = document.getElementById('detail-subtitle');
    
    const typeVal = document.getElementById('detail-type');
    const sizeVal = document.getElementById('detail-size');
    const dateVal = document.getElementById('detail-date');
    const permsVal = document.getElementById('detail-perms');
    const pathVal = document.getElementById('detail-path');
    const gridActions = document.getElementById('detail-actions-grid');
    const btnOpen = document.getElementById('detail-btn-open');
    
    if (!title || !iconBox) return;
    
    const count = AppState.selectedItems.length;
    const list = AppState.isSearching ? (AppState.searchResults || AppState.currentFiles) : AppState.currentFiles;
    
    // Caso 1: Nenhuma seleção (exibir dados da Pasta Atual)
    if (count === 0) {
        iconBox.style.display = 'block';
        imgBox.style.display = 'none';
        iconBox.innerText = AppState.currentPath === '' ? '🏠' : '📂';
        title.innerText = AppState.currentPath === '' ? 'Raiz do Servidor' : basename(AppState.currentPath);
        subtitle.innerText = 'Pasta atual na hospedagem';
        
        typeVal.innerText = 'Diretório / Pasta';
        sizeVal.innerText = `${list.length} item(ns) nesta pasta`;
        dateVal.innerText = 'Em tempo real';
        permsVal.innerText = '0755';
        pathVal.innerText = AppState.currentPath === '' ? '/' : ('/' + AppState.currentPath);
        
        if (gridActions) gridActions.style.display = 'none';
        return;
    }

    if (gridActions) gridActions.style.display = 'grid';
    
    // Caso 2: Exibir dados precisos de 1 Item selecionado
    if (count === 1) {
        const path = AppState.selectedItems[0];
        const item = list.find(f => f.path === path) || { name: basename(path), is_dir: false, size_formatted: '-', modified: '-', perms: '-', path: path };
        
        title.innerText = item.name;
        subtitle.innerText = item.is_dir ? 'Pasta do Servidor' : ('Arquivo ' + (item.ext ? item.ext.toUpperCase() : ''));
        
        if (btnOpen) {
            btnOpen.innerHTML = item.is_dir ? '📁 Abrir Pasta' : '👁️ Abrir / Visualizar';
        }
        
        typeVal.innerText = item.is_dir ? 'Pasta' : (item.ext ? `Arquivo .${item.ext.toUpperCase()}` : 'Arquivo Básico');
        sizeVal.innerText = item.is_dir ? '-' : item.size_formatted;
        dateVal.innerText = item.modified || '-';
        permsVal.innerText = item.perms || '-';
        pathVal.innerText = '/' + item.path;
        
        // Se for imagem com tamanho razoável, carregar miniatura preview em alta definição
        if (!item.is_dir && isImage(item.ext) && item.size < 6000000) {
            iconBox.style.display = 'none';
            imgBox.style.display = 'block';
            imgBox.src = `api/serve.php?path=${encodeURIComponent(item.path)}`;
        } else {
            iconBox.style.display = 'block';
            imgBox.style.display = 'none';
            iconBox.innerText = getFileIcon(item);
        }
    } 
    // Caso 3: Múltiplos itens selecionados em lote
    else {
        iconBox.style.display = 'block';
        imgBox.style.display = 'none';
        iconBox.innerText = '🗂️';
        title.innerText = `${count} itens selecionados`;
        subtitle.innerText = 'Operação em lote';
        
        let totalBytes = 0;
        let foldersCount = 0;
        let filesCount = 0;
        
        AppState.selectedItems.forEach(p => {
            const el = list.find(f => f.path === p);
            if (el) {
                if (el.is_dir) foldersCount++;
                else {
                    filesCount++;
                    totalBytes += (el.size || 0);
                }
            }
        });
        
        if (btnOpen) btnOpen.innerHTML = '⚡ Processar Lote';
        
        typeVal.innerText = `${foldersCount} pasta(s), ${filesCount} arquivo(s)`;
        sizeVal.innerText = formatBytesJs(totalBytes) + ' (somando arquivos)';
        dateVal.innerText = 'Múltiplos datas';
        permsVal.innerText = 'Variadas';
        pathVal.innerText = 'Vários caminhos da seleção';
    }
}

function formatBytesJs(bytes) {
    if (bytes === 0) return "0 B";
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function closePropertiesPanel() {
    if (window.innerWidth <= 768) {
        if (typeof closeMobileDrawers === 'function') closeMobileDrawers();
    } else {
        const pRight = document.getElementById('panel-right');
        const rRight = document.getElementById('resizer-right');
        if (pRight) pRight.style.display = 'none';
        if (rRight) rRight.style.display = 'none';
        const btnToggle = document.getElementById('btn-toggle-props');
        if (btnToggle) btnToggle.classList.remove('active');
    }
}

function togglePropertiesPanel() {
    if (window.innerWidth <= 768) {
        if (typeof toggleMobileDrawer === 'function') toggleMobileDrawer('right');
    } else {
        const pRight = document.getElementById('panel-right');
        const rRight = document.getElementById('resizer-right');
        const btnToggle = document.getElementById('btn-toggle-props');
        if (!pRight) return;
        
        if (pRight.style.display === 'none') {
            pRight.style.display = 'flex';
            if (rRight) rRight.style.display = 'block';
            if (btnToggle) btnToggle.classList.add('active');
        } else {
            pRight.style.display = 'none';
            if (rRight) rRight.style.display = 'none';
            if (btnToggle) btnToggle.classList.remove('active');
        }
    }
}
