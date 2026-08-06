// app/viewer.js - Visualizador Universal de Arquivos e Editor Ace Dracula

let aceEditorInstance = null;
let currentViewerFilePath = '';

async function openFileViewer(relPath, fallbackName = '') {
    currentViewerFilePath = relPath;
    const filename = fallbackName || basename(relPath);
    const ext = strToExt(filename.split('.').pop());

    const overlay = document.getElementById('modal-viewer');
    const titleSpan = document.getElementById('viewer-filename');
    const saveBtn = document.getElementById('viewer-save-btn');
    if (!overlay || !titleSpan) return;

    titleSpan.innerText = filename;
    overlay.classList.add('active');

    // Ocultar todas as seções e redefinir botão de salvar
    document.getElementById('ace-editor-wrapper').style.display = 'none';
    document.getElementById('spreadsheet-wrapper').style.display = 'none';
    document.getElementById('spreadsheet-tabs').style.display = 'none';
    document.getElementById('document-wrapper').style.display = 'none';
    document.getElementById('image-wrapper').style.display = 'none';
    const audioWrap = document.getElementById('audio-wrapper');
    if (audioWrap) audioWrap.style.display = 'none';
    const videoWrap = document.getElementById('video-wrapper');
    if (videoWrap) videoWrap.style.display = 'none';
    document.getElementById('pdf-viewer-frame').style.display = 'none';
    document.getElementById('unsupported-wrapper').style.display = 'none';

    if (saveBtn) saveBtn.style.display = 'none';

    showToast(`Abrindo visualizador para ${filename}...`, "info", 2000);

    // CATEGORIA 1: IMAGENS
    if (['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'ico', 'bmp'].includes(ext)) {
        const imgWrap = document.getElementById('image-wrapper');
        const imgEl = document.getElementById('viewer-img-el');
        const dims = document.getElementById('viewer-img-dims');
        imgWrap.style.display = 'flex';
        imgEl.src = `api/serve.php?path=${encodeURIComponent(relPath)}`;
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
        audioEl.src = `api/serve.php?path=${encodeURIComponent(relPath)}`;
        audioEl.play().catch(e => console.log('Autoplay prevent or error:', e));
        return;
    }

    // CATEGORIA 1.2: VÍDEO
    if (['mp4', 'webm', 'mov', 'mkv'].includes(ext) || (ext === 'ogg' && filename.includes('.ogv'))) {
        const videoWrap = document.getElementById('video-wrapper');
        const videoEl = document.getElementById('viewer-video-el');
        videoWrap.style.display = 'flex';
        videoEl.src = `api/serve.php?path=${encodeURIComponent(relPath)}`;
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
            const res = await fetch(`api/serve.php?path=${encodeURIComponent(relPath)}`);
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
            const res = await fetch(`api/serve.php?path=${encodeURIComponent(relPath)}`);
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

    // CATEGORIA 4: PDF VIA IFRAME NATIVO
    if (ext === 'pdf') {
        const iframe = document.getElementById('pdf-viewer-frame');
        iframe.style.display = 'block';
        iframe.src = `api/serve.php?path=${encodeURIComponent(relPath)}#view=FitH`;
        return;
    }

    // CATEGORIA 5: CÓDIGO FONTE E ARQUIVOS TXT (EDITOR ACE COM TEMA DRACULA E SALVAMENTO)
    const textExts = ['php', 'js', 'html', 'css', 'sql', 'py', 'json', 'xml', 'md', 'txt', 'env', 'ini', 'log', 'sh', 'bat', 'yml', 'yaml', 'gitignore', 'htaccess'];
    if (textExts.includes(ext) || ext === '') {
        const aceWrap = document.getElementById('ace-editor-wrapper');
        aceWrap.style.display = 'flex';
        if (saveBtn) saveBtn.style.display = 'inline-flex';

        const res = await apiGet('files', { action: 'read_file', path: relPath });
        if (!res.success) {
            showToast("Erro ao carregar conteúdo do arquivo de texto.", "error");
            return;
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
        aceEditorInstance.setValue(res.content || '', -1);
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
    window.location.href = `api/download.php?items=${encodeURIComponent(JSON.stringify([currentViewerFilePath]))}`;
}

function closeViewerModal() {
    const overlay = document.getElementById('modal-viewer');
    if (overlay) overlay.classList.remove('active');
    const iframe = document.getElementById('pdf-viewer-frame');
    if (iframe) iframe.src = '';
    
    // Stop audio/video
    const audioEl = document.getElementById('viewer-audio-el');
    if (audioEl) { audioEl.pause(); audioEl.src = ''; }
    
    const videoEl = document.getElementById('viewer-video-el');
    if (videoEl) { videoEl.pause(); videoEl.src = ''; }

    currentViewerFilePath = '';
}

// Add fullscreen event for video
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
});
