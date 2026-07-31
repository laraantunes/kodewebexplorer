<!-- Cabeçalho Principal do KodeWeb Explorer -->
<header class="top-header">
    <div class="logo-section" onclick="navigateTo('')" title="Voltar à pasta Raiz">
        <img src="logo.svg" alt="Logo" class="logo-icon">
        <span class="app-title">KodeWeb Explorer</span>
    </div>

    <!-- Pão de Forma / Breadcrumb Flutuante -->
    <div class="breadcrumb-container" id="breadcrumb-bar" title="Caminho atual no servidor">
        <ul class="breadcrumb-list" id="breadcrumb-list">
            <li class="breadcrumb-item active">📁 Raiz</li>
        </ul>
    </div>

    <!-- Campo de Busca em Tempo Real e Seletor de Escopo -->
    <div class="search-box">
        <span class="search-icon">🔍</span>
        <input type="text" class="search-input" id="search-input" placeholder="Buscar arquivos..." oninput="debounceSearch(this.value)">
        <select class="search-filter-select" id="search-scope" onchange="debounceSearch(document.getElementById('search-input').value)" title="Escopo da Busca">
            <option value="current">Nesta pasta</option>
            <option value="all">Em toda a raiz</option>
        </select>
    </div>

    <!-- Ações de Topo -->
    <div class="top-actions">
        <span id="sync-spinner" style="display: none; align-items: center; gap: 5px; color: var(--accent-success); font-size: 12px;">
            🔄 <span style="font-size:11px;">Sincronizando...</span>
        </span>

        <button class="top-btn" onclick="openNewItemModal('folder')" title="Criar Nova Pasta Aqui">
            <span>📁+</span> <span class="btn-label">Pasta</span>
        </button>
        <button class="top-btn" onclick="openNewItemModal('file')" title="Criar Novo Arquivo de Texto / Código Aqui">
            <span>📄+</span> <span class="btn-label">Arquivo</span>
        </button>
        <button class="top-btn" onclick="openOptionsModal()" title="Opções">
            <span>⚙️</span> <span class="btn-label">Opções</span>
        </button>
        <button class="top-btn logout-btn" onclick="window.location.href='logout.php'" title="Sair com Segurança">
            <span>🚪</span> <span class="btn-label">Sair</span>
        </button>
    </div>
</header>
