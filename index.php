<?php 
require_once('auth.php');
require_once('config.php'); 
require_once('encryption.php');

$current_username = $_SESSION['username'] ?? 'Admin';

// Resolução da Pasta Raiz Inicial do Workspace
$workspace_path = dirname(__DIR__);
if (isset($env['WORKSPACE_PATH']) && trim($env['WORKSPACE_PATH']) !== '') {
    $workspace_path = trim($env['WORKSPACE_PATH']);
}
$resolved_ws = realpath($workspace_path) ?: $workspace_path;
?>
<!DOCTYPE html>
<html lang="pt-br">

<?php require 'templates/head.php'; ?>

<body>

    <?php require 'templates/header.php'; ?>

    <!-- Container principal do Workspace de 3 painéis -->
    <div class="workspace" id="main-workspace">

        <!-- Painel Esquerdo: Explorador e Árvore -->
        <?php require 'templates/panel_left.php'; ?>

        <!-- Divisor Resizer Vertical (Esquerda <-> Centro) -->
        <div class="resizer" id="resizer-left"></div>

        <!-- Painel Central: Grade / Lista de Arquivos da Pasta Atual -->
        <?php require 'templates/panel_center.php'; ?>

        <!-- Divisor Resizer Vertical (Centro <-> Direita) -->
        <div class="resizer" id="resizer-right"></div>

        <!-- Painel Direito: Detalhes, Metadados e Ações da Seleção -->
        <?php require 'templates/panel_right.php'; ?>

    </div>

    <!-- Barra de Navegação Flutuante Exclusiva para Celulares e Tablets -->
    <?php require 'templates/mobile_navbar.php'; ?>

    <!-- Camada de escurecimento para menu gaveta mobile -->
    <div class="mobile-drawer-overlay" id="mobile-overlay" onclick="closeMobileDrawers()"></div>

    <!-- Modais (Visualizador Universal de Mídias/Docs, Editor Ace, Opções e Prompts) -->
    <?php require 'templates/modals.php'; ?>

    <!-- Menu de Contexto (Clique direito sobre arquivo ou pasta) -->
    <div class="context-menu" id="file-context-menu">
        <div class="context-menu-item" id="ctx-btn-open" onclick="handleAction('open')">👁️ Abrir / Visualizar</div>
        <div class="context-menu-item" id="ctx-btn-new-folder" onclick="openNewItemModal('folder')" style="display: none;">📁+ Nova Pasta</div>
        <div class="context-menu-item" id="ctx-btn-new-file" onclick="openNewItemModal('file')" style="display: none;">📄+ Novo Arquivo</div>
        <div class="context-menu-item" onclick="handleAction('download')">📥 Download <span id="ctx-dl-label"></span></div>
        <div class="context-menu-item" id="ctx-btn-share" onclick="handleAction('share')">📤 Compartilhar</div>
        <div class="context-menu-item" onclick="handleAction('get_link')">🔗 Gerar Link</div>
        <div class="context-menu-separator"></div>
        <div class="context-menu-item" onclick="handleAction('rename')">✏️ Renomear...</div>
        <div class="context-menu-item" onclick="handleAction('copy')">📋 Copiar para...</div>
        <div class="context-menu-item" onclick="handleAction('move')">📦 Mover para...</div>
        <div class="context-menu-separator"></div>
        <div class="context-menu-item" id="ctx-btn-delete" onclick="handleAction('delete')" style="color: var(--accent-danger);">🗑️ Excluir Seleção</div>
        <div class="context-menu-item" onclick="handleAction('details')">ℹ️ Propriedades</div>
    </div>

    <!-- Toast Notifications Container -->
    <div id="toast-container" style="position: fixed; bottom: 70px; right: 20px; z-index: 10000; display: flex; flex-direction: column; gap: 10px; pointer-events: none;"></div>

    <?php require 'templates/upload_manager.php'; ?>

    <!-- Inputs ocultos de Upload e Comunicação -->
    <input type="file" id="hidden-file-input" multiple style="display: none;" onchange="handleFilesSelected(this)">
    <input type="file" id="hidden-folder-input" webkitdirectory directory multiple style="display: none;" onchange="handleFolderSelected(this)">

    <?php require 'templates/scripts.php'; ?>

</body>
</html>
