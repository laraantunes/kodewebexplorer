// app/viewer.js - Visualizador Universal de Arquivos e Editor Ace Dracula

let aceEditorInstance = null;
let currentViewerFilePath = '';

function getServeUrl(relPath) {
    if (typeof PUBLIC_SHARE_CODE !== 'undefined' && PUBLIC_SHARE_CODE) {
        return `public.php?code=${encodeURIComponent(PUBLIC_SHARE_CODE)}&sub=${encodeURIComponent(relPath)}&serve=1`;
    }
    return `api/serve.php?path=${encodeURIComponent(relPath)}`;
}

async function openFileViewer(relPath, fallbackName = '') {
    currentViewerFilePath = relPath;
    const filename = fallbackName || basename(relPath);
    const ext = strToExt(filename.split('.').pop());

    const overlay = document.getElementById('modal-viewer');
    const titleSpan = document.getElementById('viewer-filename');
    const saveBtn = document.getElementById('viewer-save-btn');
    if (!overlay || !titleSpan) return;

    titleSpan.innerText = filename;
    
    // Limpar estilos inline residuais de quando a modal foi fechada
    overlay.style.display = ''; 
    const viewerWindow = document.getElementById('viewer-window');
    if (viewerWindow) {
        viewerWindow.style.transform = '';
        viewerWindow.style.opacity = '';
    }
    
    overlay.classList.add('active');

    // Ocultar todas as seções e redefinir botão de salvar
    document.getElementById('ace-editor-wrapper').style.display = 'none';
    document.getElementById('spreadsheet-wrapper').style.display = 'none';
    document.getElementById('spreadsheet-tabs').style.display = 'none';
    document.getElementById('document-wrapper').style.display = 'none';
    const pptxWrap = document.getElementById('pptx-wrapper');
    if (pptxWrap) pptxWrap.style.display = 'none';
    document.getElementById('image-wrapper').style.display = 'none';
    const audioWrap = document.getElementById('audio-wrapper');
    if (audioWrap) audioWrap.style.display = 'none';
    const videoWrap = document.getElementById('video-wrapper');
    if (videoWrap) videoWrap.style.display = 'none';
    document.getElementById('pdf-viewer-frame').style.display = 'none';
    document.getElementById('unsupported-wrapper').style.display = 'none';

    if (saveBtn) saveBtn.style.display = 'none';
    const mobileSaveBtn = document.getElementById('viewer-save-btn-mobile');
    if (mobileSaveBtn) mobileSaveBtn.style.display = 'none';
    if (viewerWindow) viewerWindow.classList.remove('is-text-editor');

    showToast(`Abrindo visualizador para ${filename}...`, "info", 2000);

    // CATEGORIA 1: IMAGENS
    if (['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'ico', 'bmp'].includes(ext)) {
        const imgWrap = document.getElementById('image-wrapper');
        const imgEl = document.getElementById('viewer-img-el');
        const dims = document.getElementById('viewer-img-dims');
        imgWrap.style.display = 'flex';
        imgEl.src = getServeUrl(relPath);
        imgEl.onload = () => {
            if (dims) dims.innerText = `Dimensões Reais: ${imgEl.naturalWidth} x ${imgEl.naturalHeight} px`;
        };
        return;
    }

    // CATEGORIA 1.1: ÁUDIO
    if (['mp3', 'wav', 'ogg', 'flac', 'aac', 'm4a'].includes(ext)) {
        const audioWrap = document.getElementById('audio-wrapper');
        const audioEl = document.getElementById('viewer-audio-el');
        const audioTitle = document.getElementById('audio-filename-display');
        audioWrap.style.display = 'flex';
        audioTitle.innerText = filename;
        audioEl.src = getServeUrl(relPath);
        audioEl.play().catch(e => console.log('Autoplay prevent or error:', e));
        return;
    }

    // CATEGORIA 1.2: VÍDEO
    if (['mp4', 'webm', 'mov', 'mkv'].includes(ext) || (ext === 'ogg' && filename.includes('.ogv'))) {
        const videoWrap = document.getElementById('video-wrapper');
        const videoEl = document.getElementById('viewer-video-el');
        videoWrap.style.display = 'flex';
        videoEl.src = getServeUrl(relPath);
        videoEl.play().catch(e => console.log('Autoplay prevent or error:', e));
        return;
    }

    // CATEGORIA 2: ARQUIVO EXCEL / PLANILHAS (XLSX, XLS, CSV) COM SHEETJS
    if (['xlsx', 'xls', 'csv'].includes(ext)) {
        const sheetWrap = document.getElementById('spreadsheet-wrapper');
        const sheetTabs = document.getElementById('spreadsheet-tabs');
        const sheetContent = document.getElementById('spreadsheet-content');

        sheetWrap.style.display = 'block';
        sheetTabs.style.display = 'flex';
        sheetContent.innerHTML = '<div style="text-align:center; padding:50px; color:#fff;">📊 Carregando tabela Excel interativa via SheetJS...</div>';

        try {
            const res = await fetch(getServeUrl(relPath));
            const arrayBuffer = await res.arrayBuffer();
            const workbook = XLSX.read(arrayBuffer, { type: 'array' });

            sheetTabs.innerHTML = '';
            workbook.SheetNames.forEach((name, idx) => {
                const btn = document.createElement('button');
                btn.className = 'switcher-btn ' + (idx === 0 ? 'active' : '');
                btn.innerText = `📄 ${name}`;
                btn.onclick = () => {
                    Array.from(sheetTabs.children).forEach(c => c.classList.remove('active'));
                    btn.classList.add('active');
                    renderExcelSheet(workbook.Sheets[name], sheetContent);
                };
                sheetTabs.appendChild(btn);
            });

            if (workbook.SheetNames.length > 0) {
                renderExcelSheet(workbook.Sheets[workbook.SheetNames[0]], sheetContent);
            }
        } catch (e) {
            sheetContent.innerHTML = `<div style="color:var(--accent-danger); padding:20px;">Falha na conversão SheetJS: ${e.message}</div>`;
        }
        return;
    }

    // CATEGORIA 3: ARQUIVO WORD / DOCUMENTOS (DOCX, DOC) COM MAMMOTH.JS
    if (['docx', 'doc'].includes(ext)) {
        const docWrap = document.getElementById('document-wrapper');
        const docContent = document.getElementById('document-content');
        docWrap.style.display = 'block';
        docContent.innerHTML = '<div style="text-align:center; padding:50px; color:#fff;">📘 Convertendo Word para Reading Mode com Mammoth.js...</div>';

        try {
            const res = await fetch(getServeUrl(relPath));
            const arrayBuffer = await res.arrayBuffer();
            if (typeof mammoth !== 'undefined') {
                const result = await mammoth.convertToHtml({ arrayBuffer: arrayBuffer });
                docContent.innerHTML = result.value || '<p>Documento Word sem texto em linha.</p>';
            } else {
                docContent.innerHTML = '<p>A biblioteca Mammoth não foi carregada pelo navegador.</p>';
            }
        } catch (e) {
            docContent.innerHTML = `<div style="color:var(--accent-danger); padding:20px;">Não foi possível visualizar este documento: ${e.message}</div>`;
        }
        return;
    }

    // CATEGORIA 3.5: APRESENTAÇÕES (PPTX) COM PPTXVIEWJS
    if (['pptx'].includes(ext)) {
        const pptxWrap = document.getElementById('pptx-wrapper');
        let pptxContainer = document.getElementById('pptx-container');
        const pptxCanvasWrapper = document.getElementById('pptx-canvas-wrapper');
        const pptxLoading = document.getElementById('pptx-loading');
        const pptxControls = document.getElementById('pptx-controls');
        
        // RECRIAR O CANVAS PARA DESTRUIR O CONTEXTO WEBGL ANTIGO
        if (pptxContainer) pptxContainer.remove();
        pptxContainer = document.createElement('canvas');
        pptxContainer.id = 'pptx-container';
        pptxContainer.style.display = 'block';
        pptxContainer.style.boxShadow = '0 4px 12px rgba(0,0,0,0.5)';
        if (pptxCanvasWrapper) pptxCanvasWrapper.appendChild(pptxContainer);

        pptxWrap.style.display = 'flex';
        if (pptxControls) pptxControls.style.display = 'none';
        if (pptxLoading) {
            pptxLoading.style.display = 'block';
            pptxLoading.innerHTML = '📊 Carregando apresentação...';
        }
        
        try {
            let ViewerClass = null;
            if (typeof window.PPTXViewer !== 'undefined') ViewerClass = window.PPTXViewer;
            else if (typeof PPTXViewer !== 'undefined') ViewerClass = PPTXViewer;
            else if (typeof PptxViewJS !== 'undefined') {
                ViewerClass = PptxViewJS.PPTXViewer || (PptxViewJS.default && PptxViewJS.default.PPTXViewer) || PptxViewJS.default || PptxViewJS;
            }

            if (ViewerClass && typeof ViewerClass === 'function') {
                const viewer = new ViewerClass({
                    canvas: pptxContainer,
                    width: 1200 // Resolução base para o slide ficar nítido
                });
                viewer.loadFromUrl(getServeUrl(relPath)).then(() => {
                    if (pptxLoading) pptxLoading.style.display = 'none';
                    if (pptxControls) pptxControls.style.display = 'flex';
                    
                    window.currentPptxViewer = viewer;
                    if (typeof viewer.render === 'function') viewer.render();
                    if (typeof updatePptxCounter === 'function') updatePptxCounter();
                }).catch(err => {
                    if (pptxLoading) pptxLoading.style.display = 'none';
                    const details = err.errors ? JSON.stringify(err.errors) : '';
                    console.error("PPTX Error:", err);
                    pptxWrap.innerHTML = `<div style="color:var(--accent-danger); padding:20px;">Erro ao carregar a apresentação: ${err.message || err} <br/>${details}</div>`;
                });
            } else {
                if (pptxLoading) pptxLoading.style.display = 'none';
                pptxWrap.innerHTML = `<div style="color:var(--accent-danger); padding:20px;">A biblioteca PptxViewJS não carregou corretamente. Recarregue a página (F5) e tente novamente.</div>`;
            }
        } catch (e) {
            if (pptxLoading) pptxLoading.style.display = 'none';
            pptxWrap.innerHTML = `<div style="color:var(--accent-danger); padding:20px;">Não foi possível visualizar a apresentação: ${e.message}</div>`;
        }
        return;
    }

    // CATEGORIA 4: PDF VIA IFRAME NATIVO
    if (ext === 'pdf') {
        const iframe = document.getElementById('pdf-viewer-frame');
        iframe.style.display = 'block';
        iframe.src = getServeUrl(relPath) + '#view=FitH';
        return;
    }

    // CATEGORIA 5: CÓDIGO FONTE E ARQUIVOS TXT (EDITOR ACE COM TEMA DRACULA E SALVAMENTO)
    const textExts = ['php', 'js', 'html', 'css', 'sql', 'py', 'json', 'xml', 'md', 'txt', 'env', 'ini', 'log', 'sh', 'bat', 'yml', 'yaml', 'gitignore', 'htaccess'];
    if (textExts.includes(ext) || ext === '') {
        const aceWrap = document.getElementById('ace-editor-wrapper');
        aceWrap.style.display = 'flex';
        
        const isPublic = typeof PUBLIC_SHARE_CODE !== 'undefined';
        if (saveBtn) saveBtn.style.display = isPublic ? 'none' : 'inline-flex';
        const mobileSaveBtn = document.getElementById('viewer-save-btn-mobile');
        if (mobileSaveBtn) mobileSaveBtn.style.display = isPublic ? 'none' : 'inline-flex';
        
        const viewerWindow = document.getElementById('viewer-window');
        if (viewerWindow) viewerWindow.classList.add('is-text-editor');
        
        const saveHint = document.getElementById('ace-save-hint');
        if (saveHint) saveHint.style.display = isPublic ? 'none' : 'inline';

        let fileContent = '';
        if (isPublic) {
            const pubRes = await fetch(`public.php?code=${encodeURIComponent(PUBLIC_SHARE_CODE)}&sub=${encodeURIComponent(relPath)}&read_file=1`);
            const data = await pubRes.json();
            if (!data.success) { showToast("Erro ao carregar conteúdo do arquivo.", "error"); return; }
            fileContent = data.content;
        } else {
            const apiRes = await apiGet('files', { action: 'read_file', path: relPath });
            if (!apiRes.success) { showToast("Erro ao carregar conteúdo do arquivo de texto.", "error"); return; }
            fileContent = apiRes.content;
        }

        if (!aceEditorInstance && typeof ace !== 'undefined') {
            aceEditorInstance = ace.edit("ace-editor-container");
            aceEditorInstance.setTheme("ace/theme/dracula");
            aceEditorInstance.setOptions({
                fontSize: "14px",
                showPrintMargin: false,
                enableBasicAutocompletion: true,
                enableLiveAutocompletion: true
            });
            // Atalho interno do Ace Editor para Ctrl+S
            aceEditorInstance.commands.addCommand({
                name: 'saveFile',
                bindKey: { win: 'Ctrl-S', mac: 'Cmd-S' },
                exec: function () { saveAceEditorContent(); }
            });
        }

        const mode = getAceMode(ext);
        document.getElementById('ace-mode-label').innerText = mode.toUpperCase();
        aceEditorInstance.session.setMode(`ace/mode/${mode}`);
        aceEditorInstance.setValue(fileContent || '', -1);
        aceEditorInstance.setReadOnly(isPublic);
        return;
    }

    // CATEGORIA 6: ARQUIVOS BINÁRIOS NÃO SUPORTADOS PARA RENDERIZAÇÃO
    document.getElementById('unsupported-wrapper').style.display = 'flex';
}

function renderExcelSheet(worksheet, containerEl) {
    const htmlTable = XLSX.utils.sheet_to_html(worksheet, { id: "rendered-excel-table" });
    containerEl.innerHTML = htmlTable.replace("<table", "<table class='spreadsheet-table'");
}

function getAceMode(ext) {
    if (['php'].includes(ext)) return 'php';
    if (['js', 'json'].includes(ext)) return 'javascript';
    if (['html', 'htm'].includes(ext)) return 'html';
    if (['css'].includes(ext)) return 'css';
    if (['sql'].includes(ext)) return 'sql';
    if (['py'].includes(ext)) return 'python';
    if (['md'].includes(ext)) return 'markdown';
    if (['xml'].includes(ext)) return 'xml';
    if (['sh'].includes(ext)) return 'sh';
    if (['yml', 'yaml'].includes(ext)) return 'yaml';
    return 'text';
}

async function saveAceEditorContent() {
    if (!aceEditorInstance || !currentViewerFilePath) return;
    const newContent = aceEditorInstance.getValue();
    showToast("💾 Salvando alterações no arquivo...", "info");
    const res = await apiPost('files', { action: 'save_file', path: currentViewerFilePath, content: newContent });
    if (res.success) {
        showToast("Arquivo salvo e atualizado com sucesso na hospedagem!", "success");
        loadCurrentFolder();
    }
}

function downloadCurrentViewerFile() {
    if (!currentViewerFilePath) return;
    if (typeof PUBLIC_SHARE_CODE !== 'undefined' && PUBLIC_SHARE_CODE) {
        window.location.href = `public.php?code=${encodeURIComponent(PUBLIC_SHARE_CODE)}&sub=${encodeURIComponent(currentViewerFilePath)}&download=1`;
    } else {
        window.location.href = `api/download.php?items=${encodeURIComponent(JSON.stringify([currentViewerFilePath]))}`;
    }
}

function closeViewerModal() {
    const viewerWindow = document.getElementById('viewer-window');
    const modal = document.getElementById('modal-viewer');
    viewerWindow.style.transform = 'scale(0.95)';
    viewerWindow.style.opacity = '0';
    setTimeout(() => {
        modal.style.display = 'none';
        modal.classList.remove('active'); // OBRIGATÓRIO: Libera a navegação por teclado da tela inicial
        
        // Pausar áudio e vídeo
        const audioEl = document.getElementById('viewer-audio-el');
        if (audioEl) { audioEl.pause(); audioEl.src = ''; }
        
        const videoEl = document.getElementById('viewer-video-el');
        if (videoEl) { videoEl.pause(); videoEl.src = ''; }

        // Limpar frame de pdf
        const iframe = document.getElementById('pdf-viewer-frame');
        if (iframe) iframe.src = '';
        
        // Limpar PPTX viewer da memória
        if (window.currentPptxViewer) {
            if (typeof window.currentPptxViewer.destroy === 'function') window.currentPptxViewer.destroy();
            window.currentPptxViewer = null;
        }

        currentViewerFile = null;
    }, 250);
}

// Controle de contador do PPTX
window.updatePptxCounter = function() {
    if (window.currentPptxViewer) {
        const counterEl = document.getElementById('pptx-counter');
        if (counterEl) {
            const current = window.currentPptxViewer.getCurrentSlideIndex() + 1;
            const total = window.currentPptxViewer.getSlideCount();
            counterEl.innerHTML = `Slide ${current} de ${total}`;
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const fsBtn = document.getElementById('viewer-video-fullscreen-btn');
    const videoEl = document.getElementById('viewer-video-el');
    if (fsBtn && videoEl) {
        fsBtn.addEventListener('click', () => {
            if (videoEl.requestFullscreen) {
                videoEl.requestFullscreen();
            } else if (videoEl.webkitRequestFullscreen) { /* Safari */
                videoEl.webkitRequestFullscreen();
            } else if (videoEl.msRequestFullscreen) { /* IE11 */
                videoEl.msRequestFullscreen();
            }
        });
    }

    // Eventos de Teclado Global no Modal
    document.addEventListener('keydown', (e) => {
        const overlay = document.getElementById('modal-viewer');
        if (!overlay || !overlay.classList.contains('active')) return;

        // Fechar com ESC
        if (e.key === 'Escape') {
            closeViewerModal();
            return;
        }

        // Navegação de slides PPTX
        const pptxWrapper = document.getElementById('pptx-wrapper');
        if (pptxWrapper && pptxWrapper.style.display !== 'none' && window.currentPptxViewer) {
            // Próximo Slide (Direita, Baixo, PageDown, Espaço)
            if (['ArrowRight', 'ArrowDown', 'PageDown', ' '].includes(e.key)) {
                e.preventDefault();
                window.currentPptxViewer.nextSlide();
                if (typeof updatePptxCounter === 'function') updatePptxCounter();
            }
            // Slide Anterior (Esquerda, Cima, PageUp)
            else if (['ArrowLeft', 'ArrowUp', 'PageUp'].includes(e.key)) {
                e.preventDefault();
                window.currentPptxViewer.previousSlide();
                if (typeof updatePptxCounter === 'function') updatePptxCounter();
            }
        }
    });

    // Fechar modal ao clicar fora (no overlay)
    const viewerOverlay = document.getElementById('modal-viewer');
    if (viewerOverlay) {
        viewerOverlay.addEventListener('mousedown', (e) => {
            if (e.target === viewerOverlay) {
                closeViewerModal();
            }
        });
    }
});
