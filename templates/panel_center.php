<!-- Painel Central (Grade e Lista de Arquivos com Toolbar e Área Drag & Drop) -->
<main class="panel" id="panel-center">
    <!-- Barra de Ferramentas de Ações Rápida -->
    <div class="center-toolbar">
        <div class="toolbar-left-container">
            <div class="toolbar-group" id="default-toolbar">
                <button class="btn-action" id="btn-navigate-up" onclick="navigateUp()" title="Voltar para a pasta anterior (Nível Superior)" disabled style="opacity: 0.5; pointer-events: none;">
                    ⬅️ <span class="btn-text">Voltar</span>
                </button>
                <button class="btn-action btn-upload" onclick="triggerFileUpload()">
                    📤 <span class="btn-text">Upload Arquivos</span>
                </button>
                <button class="btn-action" onclick="triggerFolderUpload()" title="Enviar pasta inteira conservando subdiretórios">
                    📂 <span class="btn-text">Upload Pasta</span>
                </button>
                <button class="btn-action" onclick="toggleSelectAll()" id="btn-select-all">
                    ☑️ <span class="btn-text">Selecionar Tudo</span>
                </button>
            </div>

            <div class="toolbar-group" id="selection-toolbar" style="display: none; transition: all 0.2s;">
                <span class="toolbar-separator" style="color: rgba(255,255,255,0.2); font-size: 16px; user-select: none;">|</span>
                <button class="btn-action" id="btn-dl-selected" onclick="handleAction('download')">📥 Baixar (<span id="count-sel">0</span>)</button>
                <button class="btn-action" onclick="handleAction('copy')">📋 Copiar</button>
                <button class="btn-action" onclick="handleAction('move')">📦 Mover</button>
                <button class="btn-action" onclick="handleAction('delete')" style="border-color: var(--accent-danger); color: var(--accent-danger);">🗑️ Excluir</button>
            </div>
        </div>

        <!-- Botão Info e Seletor Modo de Exibição (Grade vs Lista) -->
        <div style="display: flex; align-items: center; gap: 10px; margin-left: auto;" id="center-right-actions">
            <button class="btn-action" id="btn-toggle-props" onclick="togglePropertiesPanel()" title="Exibir / Ocultar Painel de Propriedades no Desktop">
                ℹ️ <span class="btn-text">Info</span>
            </button>
            <div class="view-switcher" style="flex-shrink: 0;">
                <button class="switcher-btn active" id="btn-view-grid" onclick="setViewMode('grid')">🔲 Grade</button>
                <button class="switcher-btn" id="btn-view-list" onclick="setViewMode('list')">📜 Lista</button>
            </div>
        </div>
    </div>

    <!-- Área Principal de Conteúdo -->
    <div class="content-viewport" id="center-viewport">
        <!-- Container dos itens em Grade ou Lista -->
        <div id="content-container" class="grid-view-container">
            <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--text-muted);">
                Aguardando leitura de arquivos...
            </div>
        </div>
        
        <!-- Overlay Neon Luminoso para Drag and Drop -->
        <div class="drop-zone-overlay" id="drag-drop-overlay">
            <div class="drop-zone-icon">☁️+</div>
            <div class="drop-zone-text">Solte seus Arquivos ou Pastas Aqui para fazer Upload!</div>
            <div style="color: var(--text-muted); margin-top: 8px; font-size: 13px;">O envio e estruturação automática começará instantaneamente</div>
        </div>
    </div>
</main>
