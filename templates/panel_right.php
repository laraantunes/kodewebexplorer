<!-- Painel Direito (Inspetor de Metadados, Miniaturas e Ações Rápida) -->
<aside class="panel" id="panel-right">
    <!-- Puxador superior para uso em modo Bottom Sheet (Mobile) -->
    <span class="bottom-sheet-handle"></span>

    <div class="panel-header">
        <span>Informações e Propriedades</span>
        <div class="panel-actions">
            <button class="icon-btn" onclick="closePropertiesPanel()" style="font-size: 18px;" title="Fechar Painel de Propriedades">✕</button>
        </div>
    </div>

    <div class="details-content" id="details-pane-content">
        <!-- Miniatura Prévias -->
        <div class="details-preview" id="detail-preview-box">
            <div class="details-preview-icon" id="detail-big-icon">📁</div>
            <img src="" class="details-preview-img" id="detail-preview-img" style="display: none;">
            <div class="details-title" id="detail-title">Raiz</div>
            <div class="details-subtitle" id="detail-subtitle">Pasta atual</div>
        </div>

        <!-- Lista de Metadados Técnicos -->
        <div class="meta-list" id="detail-meta-list">
            <div class="meta-item"><span class="meta-label">Tipo:</span> <span class="meta-value" id="detail-type">Pasta no Servidor</span></div>
            <div class="meta-item"><span class="meta-label">Tamanho:</span> <span class="meta-value" id="detail-size">-</span></div>
            <div class="meta-item"><span class="meta-label">Modificação:</span> <span class="meta-value" id="detail-date">-</span></div>
            <div class="meta-item"><span class="meta-label">Permissão:</span> <span class="meta-value" id="detail-perms">0755</span></div>
            <div class="meta-item" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                <span class="meta-label">Caminho Completo:</span>
                <span class="meta-value" id="detail-path" style="text-align: left; font-size: 11px; color: var(--text-muted); background: var(--bg-primary); padding: 6px; border-radius: 4px; width: 100%;">/</span>
            </div>
        </div>

        <!-- Grade de Botões de Ação na Seleção -->
        <div class="details-actions-grid" id="detail-actions-grid">
            <button class="btn-detail btn-full" id="detail-btn-open" onclick="handleAction('open')" style="background: var(--accent); color: #fff; font-weight: 600;">
                👁️ Abrir / Visualizar
            </button>
            <button class="btn-detail" onclick="handleAction('download')">📥 Baixar ZIP</button>
            <button class="btn-detail" onclick="handleAction('rename')">✏️ Renomear</button>
            <button class="btn-detail" onclick="handleAction('share')">📤 Compartilhar</button>
            <button class="btn-detail" onclick="handleAction('get_link')">🔗 Gerar Link</button>
            <button class="btn-detail" onclick="handleAction('copy')">📋 Copiar para</button>
            <button class="btn-action" onclick="handleAction('move')" style="padding: 8px 12px; font-size: 12px;">📦 Mover para</button>
            <button class="btn-detail btn-danger btn-full" onclick="handleAction('delete')" style="margin-top: 5px;">
                🗑️ Excluir Item
            </button>
        </div>
    </div>
</aside>
