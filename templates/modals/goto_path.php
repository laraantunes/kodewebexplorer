<!-- MODAL IR PARA CAMINHO (GO TO PATH) -->
<div class="modal-overlay" id="modal-goto-path">
    <div class="modal-window" style="max-width: 450px;">
        <div class="modal-header">
            <div class="modal-title">🧭 Ir para Caminho</div>
            <button class="modal-close" onclick="closeModal('modal-goto-path')">✕</button>
        </div>
        <form onsubmit="submitGoToPath(event)">
            <div class="modal-body">
                <label style="display: block; font-size: 13px; margin-bottom: 8px; color: var(--text-primary);">Digite ou cole o caminho relativo a partir da raiz (ex: dev/projeto1):</label>
                <input type="text" class="form-input" id="goto-path-input" required placeholder="Caminho..." style="width: 100%; padding: 10px; background: var(--bg-input); border: 1px solid var(--border-color); color: #fff; border-radius: 6px;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-detail" onclick="closeModal('modal-goto-path')">Cancelar</button>
                <button type="submit" class="btn-action" style="background: var(--accent); color: #fff; font-weight: 600;">Ir</button>
            </div>
        </form>
    </div>
</div>
