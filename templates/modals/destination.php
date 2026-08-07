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
