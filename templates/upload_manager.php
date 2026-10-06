<!-- Gerenciador de Uploads (Progresso) -->
<div id="upload-manager" class="upload-manager" style="display: none;">
    <div class="upload-manager-header" onclick="toggleUploadManager()">
        <span class="upload-title">
            <span class="upload-icon">📤</span>
            Uploads (<span id="upload-count-done">0</span>/<span id="upload-count-total">0</span>)
        </span>
        <button class="icon-btn" onclick="event.stopPropagation(); hideUploadManager()" title="Fechar">❌</button>
    </div>
    <div class="upload-manager-body" id="upload-manager-body">
        <!-- Itens de upload injetados aqui -->
    </div>
</div>

<!-- Ícone flutuante Mobile para Uploads -->
<div id="mobile-upload-fab" class="mobile-upload-fab" onclick="showUploadManager()">
    📤
    <span class="upload-badge" id="upload-badge">0</span>
</div>
