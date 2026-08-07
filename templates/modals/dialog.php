<!-- 5. MODAL GENÉRICO CONFIRM/ALERT/PROMPT -->
<div class="modal-overlay" id="modal-dialog">
    <div class="modal-window" style="max-width: 420px; z-index: 9999;">
        <div class="modal-header">
            <div class="modal-title" id="dialog-title">Confirmação</div>
            <button class="modal-close" onclick="closeModal('modal-dialog')">✕</button>
        </div>
        <div class="modal-body" id="dialog-body" style="padding-top: 15px;">
            <p id="dialog-message" style="margin-bottom: 15px; font-size: 14px; color: var(--text-primary); white-space: pre-wrap; line-height: 1.4;"></p>
            <div id="dialog-prompt-container" style="display: none;">
                <input type="text" class="form-input" id="dialog-prompt-input" style="width: 100%; padding: 10px; background: var(--bg-input); color: #fff; border: 1px solid var(--border-color); border-radius: 6px;">
            </div>
        </div>
        <div class="modal-footer" id="dialog-buttons">
            <button class="btn-detail" id="dialog-btn-cancel" onclick="closeModal('modal-dialog')">Cancelar</button>
            <button class="btn-action" id="dialog-btn-confirm" style="background: var(--accent); color: #fff;">Confirmar</button>
        </div>
    </div>
</div>
