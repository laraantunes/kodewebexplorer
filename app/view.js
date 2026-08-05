// app/view.js - Módulo de Renderização Principal (Grade, Lista, Busca, Multisseleção e Drag&Drop)

let lastSelectedIndex = -1;

async function loadCurrentFolder() {
    const container = document.getElementById('content-container');
    if (!container) return;

    container.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 50px; color: var(--text-muted); font-size: 15px;">⏳ Lendo arquivos no servidor...</div>';

    const res = await apiGet('files', { action: 'list_files', path: AppState.currentPath });
    
    if (res.is_file_redirect) {
        AppState.currentPath = res.parent_path;
        localStorage.setItem('kw_explorer_path', AppState.currentPath);
        AppState.selectedItems = [res.file_to_select];
        
        if (typeof renderBreadcrumb === 'function') renderBreadcrumb();
        if (typeof highlightTreeItem === 'function') highlightTreeItem(AppState.currentPath);
        
        return loadCurrentFolder();
    }

    if (!res.success || !res.files) {
        container.innerHTML = `<div style="grid-column: 1 / -1; text-align: center; padding: 50px; color: var(--accent-danger);">⚠️ ${res.error || 'Falha ao ler diretório.'}</div>`;
        return;
    }

    AppState.currentFiles = res.files;
    renderCurrentFolder();
}

function setViewMode(mode) {
    AppState.viewMode = mode;
    localStorage.setItem('kw_explorer_view_mode', mode);
    document.getElementById('btn-view-grid').classList.toggle('active', mode === 'grid');
    document.getElementById('btn-view-list').classList.toggle('active', mode === 'list');
    renderCurrentFolder();
}

function renderCurrentFolder(filesToRender = null) {
    const files = filesToRender || AppState.currentFiles;
    const container = document.getElementById('content-container');
    if (!container) return;

    if (files.length === 0) {
        container.className = 'grid-view-container';
        container.innerHTML = `
            <div style="grid-column: 1 / -1; text-align: center; padding: 70px 20px; color: var(--text-muted);">
                <div style="font-size: 50px; margin-bottom: 12px; opacity: 0.6;">📭</div>
                <h3 style="color: #fff; font-weight: 500; font-size: 16px;">Pasta vazia ou nenhum item localizado</h3>
                <p style="margin-top: 6px; font-size: 13px;">Arraste arquivos e pastas aqui ou use os botões de Upload!</p>
            </div>
        `;
        updateSelectionUI();
        return;
    }

    if (AppState.viewMode === 'grid') {
        container.className = 'grid-view-container';
        let html = '';
        files.forEach((item, idx) => {
            const isSel = AppState.selectedItems.includes(item.path);
            const icon = getFileIcon(item);
            const thumbUrl = (isImage(item.ext) && !item.is_dir && item.size < 5000000) ? `api/serve.php?path=${encodeURIComponent(item.path)}` : '';

            html += `
                <div class="grid-item ${isSel ? 'selected' : ''}" data-path="${item.path}" data-index="${idx}" onclick="handleItemClick(event, '${item.path}', ${idx}, ${item.is_dir})" oncontextmenu="showContextMenu(event, '${item.path}', ${idx})">
                    <input type="checkbox" class="grid-item-checkbox" ${isSel ? 'checked' : ''} onclick="event.stopPropagation(); toggleSelectCheckbox('${item.path}', ${idx});">
                    ${thumbUrl ? `<img src="${thumbUrl}" class="grid-item-thumb" alt="${item.name}">` : `<div class="grid-item-icon">${icon}</div>`}
                    <div class="grid-item-name" title="${item.name}">${item.name}</div>
                    <div class="grid-item-size">${item.is_dir ? 'Pasta' : item.size_formatted}</div>
                    ${item.parent_path !== undefined && item.parent_path !== '' ? `<div style="font-size: 10px; color: var(--accent); max-width:100%; overflow:hidden; text-overflow:ellipsis;">📂 ${item.parent_path}</div>` : ''}
                </div>
            `;
        });
        container.innerHTML = html;
    } else {
        container.className = '';
        let html = `
            <table class="list-view-table">
                <thead>
                    <tr>
                        <th class="list-checkbox-cell"><input type="checkbox" id="header-select-all" onclick="toggleSelectAll()"></th>
                        <th class="list-icon-cell"></th>
                        <th>Nome</th>
                        <th>Tamanho</th>
                        <th>Data Modificação</th>
                        <th>Permissões</th>
                    </tr>
                </thead>
                <tbody>
        `;
        files.forEach((item, idx) => {
            const isSel = AppState.selectedItems.includes(item.path);
            const icon = getFileIcon(item);
            html += `
                <tr class="list-row ${isSel ? 'selected' : ''}" data-path="${item.path}" data-index="${idx}" onclick="handleItemClick(event, '${item.path}', ${idx}, ${item.is_dir})" oncontextmenu="showContextMenu(event, '${item.path}', ${idx})">
                    <td class="list-checkbox-cell" onclick="event.stopPropagation(); toggleSelectCheckbox('${item.path}', ${idx});">
                        <input type="checkbox" ${isSel ? 'checked' : ''}>
                    </td>
                    <td class="list-icon-cell">${icon}</td>
                    <td class="list-name-cell">${item.name} ${item.parent_path ? `<span style="color:var(--accent); font-size:11px; margin-left:8px;">(${item.parent_path})</span>` : ''}</td>
                    <td>${item.is_dir ? 'Pasta' : item.size_formatted}</td>
                    <td style="color: var(--text-muted);">${item.modified}</td>
                    <td style="font-family: var(--font-mono); font-size: 12px; color: var(--accent-success);">${item.perms || '-'}</td>
                </tr>
            `;
        });
        html += `</tbody></table>`;
        container.innerHTML = html;
    }

    updateSelectionUI();
}

function getFileIcon(item) {
    if (item.is_dir) return '📁';
    const ext = strToExt(item.ext);
    if (['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'ico', 'bmp'].includes(ext)) return '🖼️';
    if (['pdf'].includes(ext)) return '📕';
    if (['doc', 'docx', 'rtf', 'odt'].includes(ext)) return '📘';
    if (['xls', 'xlsx', 'csv', 'ods'].includes(ext)) return '📊';
    if (['ppt', 'pptx'].includes(ext)) return '📙';
    if (['zip', 'rar', 'tar', 'gz', '7z'].includes(ext)) return '🗜️';
    if (['mp4', 'mov', 'webm', 'avi'].includes(ext)) return '🎞️';
    if (['mp3', 'wav', 'ogg', 'flac'].includes(ext)) return '🎵';
    if (['php', 'js', 'html', 'css', 'sql', 'py', 'json', 'xml', 'md', 'txt', 'env'].includes(ext)) return '📜';
    return '📄';
}
function isImage(ext) { return ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'bmp'].includes(strToExt(ext)); }
function strToExt(ext) { return (ext || '').toString().toLowerCase(); }

// --- MULTISSELEÇÃO DE ARQUIVOS (CTRL, SHIFT & CHECKBOXES) ---
function handleItemClick(event, path, index, isDir) {
    if (event.ctrlKey || event.metaKey) {
        toggleSelectCheckbox(path, index);
        return;
    }
    if (event.shiftKey && lastSelectedIndex !== -1) {
        selectRange(lastSelectedIndex, index);
        return;
    }

    // Clique normal
    if (AppState.selectedItems.length <= 1) {
        if (isDir && !AppState.selectedItems.includes(path)) {
            // Navega direto se clicar numa pasta não selecionada antes
            navigateTo(path);
            return;
        } else if (!isDir && !AppState.selectedItems.includes(path)) {
            AppState.selectedItems = [path];
            lastSelectedIndex = index;
            renderCurrentFolder();
            if (typeof updateDetailsPanel === 'function') updateDetailsPanel();
            return;
        } else {
            // Abre o visualizador de arquivo ou entra na pasta
            handleAction('open');
        }
    } else {
        AppState.selectedItems = [path];
        lastSelectedIndex = index;
        renderCurrentFolder();
        if (typeof updateDetailsPanel === 'function') updateDetailsPanel();
    }
}

function toggleSelectCheckbox(path, index) {
    lastSelectedIndex = index;
    const idx = AppState.selectedItems.indexOf(path);
    if (idx !== -1) {
        AppState.selectedItems.splice(idx, 1);
    } else {
        AppState.selectedItems.push(path);
    }
    renderCurrentFolder();
    if (typeof updateDetailsPanel === 'function') updateDetailsPanel();
}

function selectRange(startIdx, endIdx) {
    const min = Math.min(startIdx, endIdx);
    const max = Math.max(startIdx, endIdx);
    const list = AppState.isSearching ? (AppState.searchResults || AppState.currentFiles) : AppState.currentFiles;

    for (let i = min; i <= max; i++) {
        const item = list[i];
        if (item && !AppState.selectedItems.includes(item.path)) {
            AppState.selectedItems.push(item.path);
        }
    }
    renderCurrentFolder();
    if (typeof updateDetailsPanel === 'function') updateDetailsPanel();
}

function toggleSelectAll() {
    const list = AppState.isSearching ? (AppState.searchResults || AppState.currentFiles) : AppState.currentFiles;
    if (AppState.selectedItems.length === list.length && list.length > 0) {
        AppState.selectedItems = [];
    } else {
        AppState.selectedItems = list.map(item => item.path);
    }
    renderCurrentFolder();
    if (typeof updateDetailsPanel === 'function') updateDetailsPanel();
}

function updateSelectionUI() {
    const count = AppState.selectedItems.length;
    const selToolbar = document.getElementById('selection-toolbar');
    const defToolbar = document.getElementById('default-toolbar');
    const countSpan = document.getElementById('count-sel');
    const badgeMobile = document.getElementById('mobile-badge-count');

    if (countSpan) countSpan.innerText = count;
    if (badgeMobile) {
        badgeMobile.style.display = count > 0 ? 'inline' : 'none';
        badgeMobile.innerText = count;
    }

    if (selToolbar && defToolbar) {
        if (count > 0) {
            selToolbar.style.display = 'flex';
            defToolbar.style.display = 'flex';
        } else {
            selToolbar.style.display = 'none';
            defToolbar.style.display = 'flex';
        }
    }

    const list = AppState.isSearching ? (AppState.searchResults || AppState.currentFiles) : AppState.currentFiles;
    const allSelected = count > 0 && count === list.length;

    const headerCheck = document.getElementById('header-select-all');
    if (headerCheck) {
        headerCheck.checked = allSelected;
    }

    const btnSelectAll = document.getElementById('btn-select-all');
    if (btnSelectAll) {
        if (allSelected) {
            btnSelectAll.innerHTML = '🚫 <span class="btn-text">Desmarcar Tudo</span>';
            btnSelectAll.title = "Desmarcar todos os itens";
        } else {
            btnSelectAll.innerHTML = '☑️ <span class="btn-text">Selecionar Tudo</span>';
            btnSelectAll.title = "Selecionar todos os itens";
        }
    }

    if (typeof updateDetailsPanel === 'function') updateDetailsPanel();
}

// --- BUSCA TEMPO REAL COM DEBOUNCE ---
let searchTimeout = null;
function debounceSearch(val) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => performSearch(val), 350);
}

async function performSearch(val) {
    val = val.trim();
    if (val === '') {
        AppState.isSearching = false;
        renderCurrentFolder(AppState.currentFiles);
        return;
    }
    AppState.isSearching = true;
    const scope = document.getElementById('search-scope')?.value || 'current';

    const container = document.getElementById('content-container');
    container.innerHTML = '<div style="grid-column:1/-1; text-align:center; padding:30px;">🔍 Buscando por "' + htmlEscape(val) + '"...</div>';

    const res = await apiGet('search', { query: val, scope: scope, current_path: AppState.currentPath });
    if (res.success && res.results) {
        AppState.searchResults = res.results;
        renderCurrentFolder(res.results);
    }
}
function htmlEscape(str) { return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;"); }

// --- MENU DE CONTEXTO ---
function showContextMenu(event, path, idx) {
    event.preventDefault();
    if (!AppState.selectedItems.includes(path)) {
        AppState.selectedItems = [path];
        lastSelectedIndex = idx;
        renderCurrentFolder();
        if (typeof updateDetailsPanel === 'function') updateDetailsPanel();
    }

    const menu = document.getElementById('file-context-menu');
    if (!menu) return;

    let x = event.clientX;
    let y = event.clientY;

    // Ajustar caso saia da tela
    if (x + 200 > window.innerWidth) x = window.innerWidth - 200;
    if (y + 250 > window.innerHeight) y = window.innerHeight - 260;

    menu.style.left = x + 'px';
    menu.style.top = y + 'px';
    menu.classList.add('active');

    // Ocultar ao clicar fora
    document.addEventListener('click', () => menu.classList.remove('active'), { once: true });
}

// --- AÇÕES DO TOOLBAR E DO MENU DE CONTEXTO ---
async function handleAction(actionType) {
    let count = AppState.selectedItems.length;
    let targetPaths = [...AppState.selectedItems];
    let firstPath = targetPaths[0];
    let firstItem = null;

    if (count === 0) {
        if (actionType !== 'open' && actionType !== 'download' && actionType !== 'share' && actionType !== 'get_link' && actionType !== 'rename' && actionType !== 'copy' && actionType !== 'move' && actionType !== 'delete' && actionType !== 'details') {
            return;
        }
        firstPath = AppState.currentPath;
        targetPaths = [firstPath];
        firstItem = {
            name: firstPath === '' ? 'Raiz' : basename(firstPath),
            is_dir: true,
            path: firstPath
        };
        count = 1;

        if (firstPath === '' && (actionType === 'delete' || actionType === 'rename' || actionType === 'move')) {
            showToast("Ação não permitida na pasta raiz.", "error");
            return;
        }
    } else {
        firstItem = (AppState.isSearching ? AppState.searchResults : AppState.currentFiles).find(f => f.path === firstPath);
    }

    switch (actionType) {
        case 'open':
            if (!firstItem) return;
            if (firstItem.is_dir) {
                // If the user double clicked current folder in details panel... it should just reload or go to it.
                // But navigateTo already reloads if same.
                navigateTo(firstItem.path);
            } else {
                openFileViewer(firstItem.path, firstItem.name);
            }
            break;

        case 'download':
            showToast(`Iniciando download de ${count} item(ns)...`, "info");
            const dlUrl = `api/download.php?items=${encodeURIComponent(JSON.stringify(targetPaths))}`;
            window.location.href = dlUrl;
            break;

        case 'share':
            if (count > 1) {
                showToast("Selecione apenas 1 item para compartilhar nativamente.", "warning");
                return;
            }
            if (navigator.share) {
                showToast("Preparando arquivo para compartilhamento...", "info");
                try {
                    const dlUrlSingle = `api/download.php?items=${encodeURIComponent(JSON.stringify([firstPath]))}`;
                    const response = await fetch(dlUrlSingle);
                    if (!response.ok) throw new Error("Erro no download");
                    const blob = await response.blob();
                    const file = new File([blob], firstItem.name, { type: blob.type });

                    if (navigator.canShare && navigator.canShare({ files: [file] })) {
                        await navigator.share({
                            title: firstItem.name,
                            files: [file]
                        });
                    } else {
                        // Fallback sharing link if file sharing isn't supported
                        const baseUrl = window.location.origin + window.location.pathname.replace('index.php', '');
                        const cleanBase = baseUrl.endsWith('/') ? baseUrl : baseUrl + '/';
                        const hash = await getShareHash(firstPath);
                        if (hash) {
                            await navigator.share({
                                title: firstItem.name,
                                url: cleanBase + "public.php?code=" + hash
                            });
                        } else {
                            showToast("Falha ao gerar link seguro.", "error");
                        }
                    }
                } catch (err) {
                    console.error(err);
                    showToast("Falha ao compartilhar o arquivo.", "error");
                }
            } else {
                showToast("Compartilhamento nativo não suportado neste navegador.", "error");
            }
            break;

        case 'get_link':
            if (count > 1) {
                showToast("Selecione apenas 1 item para gerar o link.", "warning");
                return;
            }
            const baseUrlLink = window.location.origin + window.location.pathname.replace('index.php', '');
            const cleanBaseLink = baseUrlLink.endsWith('/') ? baseUrlLink : baseUrlLink + '/';
            const appUrl = cleanBaseLink + "index.php?dir=" + encodeURIComponent(firstPath);

            const wantsPublic = await AppDialog.confirm("Qual tipo de link deseja gerar?\n\n- Público: Para compartilhar externamente.\n- App: Link direto no explorador para acesso rápido.", "Gerar Link", "Público", "App");

            // Se fechou no X, quer cancelar
            if (wantsPublic === null) return;

            let finalUrl = appUrl;
            if (wantsPublic) {
                showToast("Gerando link seguro...", "info");
                const hash = await getShareHash(firstPath);
                if (hash) {
                    finalUrl = cleanBaseLink + "public.php?code=" + hash;
                } else {
                    showToast("Erro ao gerar link público seguro.", "error");
                    return;
                }
            }

            await AppDialog.prompt("Link gerado com sucesso. Copie abaixo (Ctrl+C / Cmd+C):", finalUrl, "Copiar Link");
            break;

        case 'delete':
            if (!(await AppDialog.confirm(`⚠️ Excluir permanentemente ${count} item(ns) da hospedagem?\nEsta ação é irreversível!`, "Excluir Item", "Excluir", "Cancelar"))) return;
            const resDel = await apiPost('files', { action: 'delete', items: targetPaths });
            if (resDel.success) {
                showToast(resDel.message, "success");
                AppState.selectedItems = [];
                loadCurrentFolder();
                if (typeof updateDetailsPanel === 'function') updateDetailsPanel();
            }
            break;

        case 'rename':
            if (count > 1) {
                showToast("Renomeie apenas 1 item por vez.", "warning");
                return;
            }
            const oldName = basename(firstPath);
            const newName = await AppDialog.prompt("Digite o novo nome para o item:", oldName, "Renomear Item");
            if (newName && newName.trim() !== '' && newName !== oldName) {
                const resRen = await apiPost('files', { action: 'rename', path: firstPath, new_name: newName.trim() });
                if (resRen.success) {
                    showToast(resRen.message, "success");
                    AppState.selectedItems = [resRen.new_path];
                    loadCurrentFolder();
                }
            }
            break;

        case 'copy':
        case 'move':
            openDestinationModal(actionType);
            break;

        case 'details':
            if (window.innerWidth <= 768) {
                toggleMobileDrawer('right');
            } else {
                const pRight = document.getElementById('panel-right');
                const rRight = document.getElementById('resizer-right');
                if (pRight && pRight.style.display === 'none') {
                    pRight.style.display = 'flex';
                    if (rRight) rRight.style.display = 'block';
                    const btnToggle = document.getElementById('btn-toggle-props');
                    if (btnToggle) btnToggle.classList.add('active');
                }
            }
            break;
    }
}

function basename(str) { return str.split('/').pop(); }

async function getShareHash(path) {
    try {
        const response = await fetch('api/share_link.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ path: path })
        });
        const data = await response.json();
        if (data.success) return data.hash;
        return null;
    } catch (e) {
        console.error("Erro ao gerar hash", e);
        return null;
    }
}

// --- UPLOAD MANUAL E DRAG & DROP OVERLAY ---
function triggerFileUpload() {
    const input = document.getElementById('hidden-file-input');
    if (input) input.click();
}

function triggerFolderUpload() {
    const input = document.getElementById('hidden-folder-input');
    if (input) input.click();
}

async function handleFilesSelected(input) {
    if (!input.files || input.files.length === 0) return;
    await performFileUpload(input.files);
    input.value = '';
}

async function handleFolderSelected(input) {
    if (!input.files || input.files.length === 0) return;
    const relPaths = Array.from(input.files).map(f => f.webkitRelativePath || f.name);
    await performFileUpload(input.files, relPaths);
    input.value = '';
}

async function performFileUpload(fileList, relativePaths = null) {
    showToast(`Iniciando upload de ${fileList.length} arquivo(s) para o servidor...`, "info", 5000);
    const formData = new FormData();
    formData.append('target_path', AppState.currentPath);

    if (relativePaths) {
        formData.append('relative_paths', JSON.stringify(relativePaths));
    }

    for (let i = 0; i < fileList.length; i++) {
        formData.append('files[]', fileList[i]);
    }

    const res = await apiPost('upload', formData);
    if (res.success) {
        showToast("Upload concluído com sucesso!", "success");
        loadCurrentFolder();
    } else {
        showToast(`❌ Erro no upload: ${res.error || 'Falha desconhecida'}`, "error", 6000);
    }
}

// Vinculação de eventos de Drag & Drop no Viewport Central
document.addEventListener('DOMContentLoaded', () => {
    const viewport = document.getElementById('center-viewport');
    const overlay = document.getElementById('drag-drop-overlay');
    if (!viewport || !overlay) return;

    let dragCounter = 0;

    viewport.addEventListener('dragenter', (e) => {
        e.preventDefault();
        dragCounter++;
        overlay.classList.add('active');
    });

    viewport.addEventListener('dragover', (e) => e.preventDefault());

    viewport.addEventListener('dragleave', (e) => {
        e.preventDefault();
        dragCounter--;
        if (dragCounter <= 0) {
            dragCounter = 0;
            overlay.classList.remove('active');
        }
    });

    viewport.addEventListener('drop', async (e) => {
        e.preventDefault();
        dragCounter = 0;
        overlay.classList.remove('active');

        const files = e.dataTransfer.files;
        if (files && files.length > 0) {
            await performFileUpload(files);
        }
    });
});
