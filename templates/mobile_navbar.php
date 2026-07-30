<!-- Barra Inferior de Navegação Flutuante Específica para Smartphones e Tablets (PWA) -->
<nav class="mobile-bottom-nav">
    <button class="mobile-nav-item" id="mobile-nav-tree" onclick="toggleMobileDrawer('left')">
        <span class="mobile-nav-icon">📁</span>
        <span>Pastas</span>
    </button>
    <button class="mobile-nav-item active" id="mobile-nav-files" onclick="closeMobileDrawers()">
        <span class="mobile-nav-icon">📂</span>
        <span>Arquivos</span>
    </button>
    <button class="mobile-nav-item" id="mobile-nav-details" onclick="toggleMobileDrawer('right')">
        <span class="mobile-nav-icon">ℹ️</span>
        <span>Detalhes <span id="mobile-badge-count" style="display:none; background:var(--accent-success); color:#000; padding:1px 5px; border-radius:10px; font-size:10px;">0</span></span>
    </button>
</nav>
