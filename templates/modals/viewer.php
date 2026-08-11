<!-- 1. MODAL DO VISUALIZADOR UNIVERSAL E EDITOR ACE (TEMA DRACULA) -->
<div class="modal-overlay" id="modal-viewer">
    <div class="modal-window viewer-modal" id="viewer-window">
        <div class="modal-header">
            <div class="modal-title" id="viewer-title-bar">
                <span id="viewer-icon">📄</span> <span id="viewer-filename">arquivo.txt</span>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <button class="btn-action" id="viewer-save-btn" onclick="saveAceEditorContent()" style="background: var(--accent-success); color: #000; font-weight: 700; display: none;">
                    💾 Salvar (Ctrl+S)
                </button>
                <button class="btn-action" onclick="downloadCurrentViewerFile()">📥 Download</button>
                <button class="modal-close" onclick="closeViewerModal()" title="Fechar">✕</button>
            </div>
        </div>
        
        <div class="modal-body no-padding" id="viewer-body">
            <!-- A: EDITOR ACE (TEXTOS / CÓDIGO FONTE) -->
            <div id="ace-editor-wrapper" style="width: 100%; height: 100%; display: none; flex: 1; flex-direction: column;">
                <div class="viewer-top-toolbar" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                    <span style="font-size: 12px; color: var(--text-muted);">Sintaxe: <span id="ace-mode-label">Texto</span></span>
                    <div class="editor-mobile-actions" style="display: none; gap: 5px;">
                        <button class="btn-action" id="viewer-save-btn-mobile" onclick="saveAceEditorContent()" style="background: var(--accent-success); color: #000; font-weight: 700; padding: 4px 8px; font-size: 11px;">
                            💾 Salvar
                        </button>
                        <button class="btn-action" onclick="downloadCurrentViewerFile()" style="padding: 4px 8px; font-size: 11px;">📥 Download</button>
                    </div>
                </div>
                <div id="ace-editor-container"></div>
            </div>

            <!-- B: VISUALIZADOR DE PLANILHAS (EXCEL VIA SHEETJS) -->
            <div id="spreadsheet-wrapper" class="spreadsheet-container" style="display: none;">
                <div id="spreadsheet-content"></div>
            </div>
            <div id="spreadsheet-tabs" class="spreadsheet-tabs" style="display: none;"></div>

            <!-- C: VISUALIZADOR DE DOCUMENTOS WORD E TXT (MAMMOTH / READING MODE) -->
            <div id="document-wrapper" style="display: none; overflow-y: auto; width: 100%; height: 100%; background: #12051f;">
                <div id="document-content" class="document-reading-mode"></div>
            </div>

            <!-- C2: VISUALIZADOR DE APRESENTAÇÕES (PPTXVIEWJS) -->
            <div id="pptx-wrapper" style="display: none; overflow-y: auto; width: 100%; height: 100%; background: #12051f; text-align: center; padding: 20px; flex-direction: column;">
                <div id="pptx-loading" style="display: none; text-align:center; padding:50px; color:#fff;">📊 Carregando apresentação...</div>
                
                <div id="pptx-controls" style="display: none; justify-content: center; align-items: center; gap: 15px; margin-bottom: 20px;">
                    <button class="btn-action" id="pptx-prev-btn" onclick="if(window.currentPptxViewer) { Promise.resolve(window.currentPptxViewer.previousSlide()).then(() => updatePptxCounter()); }">⬅️ Anterior</button>
                    <span id="pptx-counter" style="color: #fff; font-size: 14px; font-weight: 600;">Slide 1</span>
                    <button class="btn-action" id="pptx-next-btn" onclick="if(window.currentPptxViewer) { Promise.resolve(window.currentPptxViewer.nextSlide()).then(() => updatePptxCounter()); }">Próximo ➡️</button>
                </div>

                <div id="pptx-canvas-wrapper" style="flex: 1; display: flex; justify-content: center; align-items: flex-start; overflow: auto; width: 100%;">
                    <style>
                        #pptx-container {
                            width: 95% !important;
                            max-width: 1600px !important;
                            max-height: calc(100vh - 180px) !important;
                            height: auto !important;
                            object-fit: contain;
                        }
                    </style>
                    <canvas id="pptx-container" style="display: block; box-shadow: 0 4px 12px rgba(0,0,0,0.5);"></canvas>
                </div>
            </div>

            <!-- D: VISUALIZADOR DE IMAGENS -->
            <div id="image-wrapper" class="image-viewer-canvas" style="display: none;">
                <img id="viewer-img-el" src="" alt="Preview Imagem">
                <div style="position: absolute; bottom: 15px; background: rgba(0,0,0,0.7); padding: 5px 12px; border-radius: 20px; color: #fff; font-size: 12px;" id="viewer-img-dims">
                    Dimensões: Carregando...
                </div>
            </div>

            <!-- D1: VISUALIZADOR DE ÁUDIO -->
            <div id="audio-wrapper" style="display: none; flex: 1; align-items: center; justify-content: center; background: #12051f; flex-direction: column; padding: 40px; text-align: center;">
                <div style="font-size: 64px; margin-bottom: 20px; color: var(--accent);">🎵</div>
                <h3 id="audio-filename-display" style="color: #fff; margin-bottom: 20px; word-break: break-all;"></h3>
                <audio id="viewer-audio-el" controls style="width: 100%; max-width: 500px; outline: none;"></audio>
            </div>

            <!-- D2: VISUALIZADOR DE VÍDEO -->
            <div id="video-wrapper" style="display: none; width: 100%; height: 100%; background: #000; position: relative; justify-content: center; align-items: center; flex-direction: column;">
                <video id="viewer-video-el" controls style="width: 100%; height: 100%; max-height: 100%; outline: none;"></video>
                <button id="viewer-video-fullscreen-btn" class="btn-action" style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.7); color: white; border: 1px solid rgba(255,255,255,0.3); z-index: 10;" title="Tela Cheia">⛶ Tela Cheia</button>
            </div>

            <!-- E: VISUALIZADOR DE PDF / IFRAME -->
            <iframe id="pdf-viewer-frame" style="width: 100%; height: 100%; border: none; display: none;" src=""></iframe>

            <!-- F: MENSAGEM DE ERRO OU ARQUIVO NÃO SUPORTADO FORA DO DOWNLOAD -->
            <div id="unsupported-wrapper" style="display: none; flex: 1; align-items: center; justify-content: center; flex-direction: column; padding: 40px; text-align: center;">
                <div style="font-size: 54px; margin-bottom: 15px;">📦</div>
                <h3 style="color: #fff;">Este formato binário destina-se a download ou aplicativo local</h3>
                <p style="color: var(--text-muted); margin: 10px 0 20px 0;">Não há renderizador gráfico no navegador para este formato específico.</p>
                <button class="btn-action btn-upload" onclick="downloadCurrentViewerFile()">Baixar Arquivo no Computador</button>
            </div>
        </div>
    </div>
</div>
