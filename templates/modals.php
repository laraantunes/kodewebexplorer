<!-- Sistema Universal de Modais do KodeWeb Explorer -->

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
                <div class="viewer-top-toolbar">
                    <span style="font-size: 12px; color: var(--text-muted);">Tema: <strong>Dracula</strong> | Sintaxe: <span id="ace-mode-label">Texto</span></span>
                    <span style="font-size: 11px; color: var(--accent);">Pressione <b>Ctrl+S</b> ou <b>Cmd+S</b> para salvar na hospedagem</span>
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

            <!-- D: VISUALIZADOR DE IMAGENS -->
            <div id="image-wrapper" class="image-viewer-canvas" style="display: none;">
                <img id="viewer-img-el" src="" alt="Preview Imagem">
                <div style="position: absolute; bottom: 15px; background: rgba(0,0,0,0.7); padding: 5px 12px; border-radius: 20px; color: #fff; font-size: 12px;" id="viewer-img-dims">
                    Dimensões: Carregando...
                </div>
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

<!-- 2. MODAL DE SELEÇÃO DE DESTINO PARA COPIAR / MOVER (DESTINATION TREE) -->
<div class="modal-overlay" id="modal-destination">
    <div class="modal-window" style="max-width: 460px;">
        <div class="modal-header">
            <div class="modal-title" id="dest-modal-title">📦 Selecione a pasta de destino</div>
            <button class="modal-close" onclick="closeModal('modal-destination')">✕</button>
        </div>
        <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
            <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 12px;">Navegue pela estrutura da hospedagem para escolher o destino:</p>
            <div id="destination-tree-container" class="tree-container" style="border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; background: var(--bg-primary);">
                Carregando arvore...
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-detail" onclick="closeModal('modal-destination')">Cancelar</button>
            <button class="btn-action" id="confirm-dest-btn" style="background: var(--accent); color: #fff;">Confirmar Destino</button>
        </div>
    </div>
</div>

<!-- 3. MODAL DE CRIAR NOVO ITEM (PASTA OU ARQUIVO) -->
<div class="modal-overlay" id="modal-new-item">
    <div class="modal-window" style="max-width: 380px;">
        <div class="modal-header">
            <div class="modal-title" id="new-item-title">📁 Nova Pasta</div>
            <button class="modal-close" onclick="closeModal('modal-new-item')">✕</button>
        </div>
        <form onsubmit="submitNewItem(event)">
            <div class="modal-body">
                <input type="hidden" id="new-item-type" value="folder">
                <label style="display: block; font-size: 13px; margin-bottom: 8px; color: var(--text-primary);" id="new-item-label">Nome da Pasta</label>
                <input type="text" class="form-input" id="new-item-input" required autofocus placeholder="Digite o nome..." style="width: 100%; padding: 10px; background: var(--bg-input); border: 1px solid var(--border-color); color: #fff; border-radius: 6px;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-detail" onclick="closeModal('modal-new-item')">Cancelar</button>
                <button type="submit" class="btn-action" style="background: var(--accent-success); color: #000; font-weight: 600;">Criar Agora</button>
            </div>
        </form>
    </div>
</div>

<!-- 4. MODAL DE OPÇÕES E SOBRE (COM AUTO-UPDATE GITHUB) -->
<div class="modal-overlay" id="modal-options">
    <div class="modal-window" style="max-width: 550px;">
        <div class="modal-header">
            <div class="modal-title">⚙️ Configurações do Explorer</div>
            <button class="modal-close" onclick="closeModal('modal-options')">✕</button>
        </div>
        
        <div class="modal-tabs">
            <button class="modal-tab-btn active" id="tab-btn-workspace" onclick="switchOptionsTab('workspace')">📁 Workspace</button>
            <button class="modal-tab-btn" id="tab-btn-security" onclick="switchOptionsTab('security')">🔐 Segurança</button>
            <button class="modal-tab-btn" id="tab-btn-about" onclick="switchOptionsTab('about')">ℹ️ Sobre & GitHub</button>
        </div>

        <div class="modal-body">
            <!-- ABA 1: WORKSPACE E AMBIENTE -->
            <div id="tab-view-workspace">
                <form onsubmit="saveWorkspaceSettings(event)">
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #fff;">Pasta Raiz da Hospedagem (Escopo do Explorer)</label>
                        <input type="text" id="opt-workspace-path" class="form-input" required style="width: 100%; padding: 10px; background: var(--bg-input); color: #fff; border: 1px solid var(--border-color); border-radius: 6px;">
                        <span style="font-size: 11px; color: var(--accent-success); display: block; margin-top: 4px;">💡 Defina qual pasta do seu servidor será explorada como raiz principal.</span>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 20px;">
                        <input type="checkbox" id="opt-is-local" style="width: 16px; height: 16px; accent-color: var(--accent);">
                        <label for="opt-is-local" style="cursor: pointer; font-size: 13px;">Ambiente Local (Desenvolvimento e SSL permissivo)</label>
                    </div>

                    <button type="submit" class="btn-action" style="background: var(--accent); color: #fff; width: 100%; justify-content: center; padding: 10px;">Salvar Alterações</button>
                </form>
            </div>

            <!-- ABA 2: SEGURANÇA E SENHAS -->
            <div id="tab-view-security" style="display: none;">
                <form onsubmit="updateAdminCredentials(event)">
                    <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 15px;">Atualize o usuário e a senha criptografados no arquivo `auth.enc` com segurança AES-256-CBC:</p>
                    
                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-size: 13px; margin-bottom: 4px;">Usuário Administrador</label>
                        <input type="text" id="sec-username" class="form-input" value="<?= htmlspecialchars($current_username) ?>" required style="width: 100%; padding: 10px; background: var(--bg-input); color: #fff; border: 1px solid var(--border-color); border-radius: 6px;">
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 13px; margin-bottom: 4px;">Nova Senha Criptografada</label>
                        <input type="password" id="sec-password" class="form-input" placeholder="Digite uma senha forte..." required style="width: 100%; padding: 10px; background: var(--bg-input); color: #fff; border: 1px solid var(--border-color); border-radius: 6px;">
                    </div>

                    <button type="submit" class="btn-action" style="background: var(--accent-success); color: #000; font-weight: 700; width: 100%; justify-content: center; padding: 10px;">Atualizar Senha Agora</button>
                </form>
            </div>

            <!-- ABA 3: SOBRE & AUTO-ATUALIZAÇÃO GITHUB -->
            <div id="tab-view-about" style="display: none; text-align: center; padding: 10px;">
                <img src="logo.svg" alt="KodeWeb Explorer Logo" style="width: 76px; height: 76px; margin-bottom: 12px; filter: drop-shadow(0 0 10px rgba(189,0,255,0.4));">
                <h3 style="color: #fff; font-size: 20px; font-weight: 700;">KodeWeb Explorer</h3>
                <p style="color: var(--accent-success); font-weight: 600; font-size: 13px; margin: 4px 0 12px 0;">Versão Atual: <?= htmlspecialchars($app_version) ?></p>
                <p style="color: var(--text-muted); font-size: 13px; max-width: 400px; margin: 0 auto 16px auto; line-height: 1.5;">
                    Explorador e Gerenciador de Arquivos Web da Família KodeWeb, desenvolvido por <a href="https://laralabs.dev" target="_blank" style="color: var(--accent); text-decoration: none; font-weight: 600;">Laralabs</a>.
                </p>
                <p style="font-size: 12px; margin-bottom: 24px;">
                    GitHub Oficial: <a href="https://github.com/laraantunes/kodewebexplorer" target="_blank" style="color: #00ff88; text-decoration: underline;">github.com/laraantunes/kodewebexplorer</a>
                </p>
                
                <div style="background: rgba(189, 0, 255, 0.12); border: 1px solid var(--accent); border-radius: 8px; padding: 16px;">
                    <h4 style="color: #fff; font-size: 14px; margin-bottom: 6px;">🔄 Atualização Automática</h4>
                    <p style="color: var(--text-muted); font-size: 12px; margin-bottom: 15px;">
                        Verifique e instale automaticamente a última release oficial do GitHub em 1 clique sem perder suas senhas, chaves `.key` nem arquivos de configuração!
                    </p>
                    <button class="btn-action" id="btn-github-update" onclick="checkGithubUpdate()" style="background: linear-gradient(135deg, #bd00ff, #8b00dd); color: #fff; font-weight: 600; width: 100%; justify-content: center; padding: 12px; box-shadow: 0 4px 15px rgba(189,0,255,0.4);">
                        🔍 Buscar Atualizações no GitHub
                    </button>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-detail" onclick="closeModal('modal-options')">Fechar</button>
        </div>
    </div>
</div>
