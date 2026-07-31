<!-- Painel Esquerdo (Árvore de Exploração do Servidor) -->
<aside class="panel" id="panel-left">
    <div class="panel-header">
        <span>Pastas</span>
        <div class="panel-actions">
            <button class="icon-btn" onclick="openNewItemModal('folder')" title="Criar Pasta na seleção">+📁</button>
            <button class="icon-btn" onclick="openNewItemModal('file')" title="Criar Arquivo na seleção">+📄</button>
            <button class="icon-btn" onclick="loadTreeRoot()" title="Recarregar Árvore Completa">🔄</button>
        </div>
    </div>
    
    <div style="padding: 6px 12px; background: rgba(0,0,0,0.2); border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
        <span id="root-label-display" style="font-size: 11px; color: var(--text-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 190px;" title="Workspace Ativo">
            📂 <?= htmlspecialchars(basename($resolved_ws)) ?> (Root)
        </span>
        <button onclick="openOptionsModal('workspace')" style="background: transparent; border: none; color: var(--accent); cursor: pointer; font-size: 11px; text-decoration: underline;">Alterar</button>
    </div>

    <div class="tree-container" id="tree-root-container">
        <!-- Estrutura da árvore montada via Javascript Vanilla -->
        <ul class="tree-list root-list" id="main-tree-ul">
            <li style="text-align: center; color: var(--text-muted); font-size: 12px; padding: 20px;">Carregando diretórios...</li>
        </ul>
    </div>
</aside>
