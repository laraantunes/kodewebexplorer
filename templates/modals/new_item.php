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
