<!-- 4. MODAL DE OPÇÕES E SOBRE (COM AUTO-UPDATE GITHUB) -->
<div class="modal-overlay" id="modal-options">
    <div class="modal-window" style="max-width: 550px;">
        <div class="modal-header">
            <div class="modal-title">Opções</div>
            <button class="modal-close" onclick="closeModal('modal-options')">✕</button>
        </div>
        
        <div class="modal-tabs">
            <button class="modal-tab-btn active" id="tab-btn-workspace" onclick="switchOptionsTab('workspace')">Ambiente</button>
            <button class="modal-tab-btn" id="tab-btn-security" onclick="switchOptionsTab('security')">Usuário</button>
            <button class="modal-tab-btn" id="tab-btn-about" onclick="switchOptionsTab('about')">Sobre</button>
        </div>

        <div class="modal-body">
            <!-- ABA 1: WORKSPACE E AMBIENTE -->
            <div id="tab-view-workspace">
                <form onsubmit="saveWorkspaceSettings(event)">
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #fff;">Pasta Raiz do Ambiente</label>
                        <input type="text" id="opt-workspace-path" class="form-input" required style="width: 100%; padding: 10px; background: var(--bg-input); color: #fff; border: 1px solid var(--border-color); border-radius: 6px;">
                        <span style="font-size: 11px; color: var(--accent-success); display: block; margin-top: 4px;">Defina qual pasta será explorada como raiz.</span>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 20px;">
                        <input type="checkbox" id="opt-is-local" style="width: 16px; height: 16px; accent-color: var(--accent);">
                        <label for="opt-is-local" style="cursor: pointer; font-size: 13px;">Ambiente Local (Desenvolvimento e SSL permissivo)</label>
                    </div>

                    <button type="submit" class="btn-action" style="background: var(--accent); color: #fff; width: 100%; justify-content: center; padding: 10px;">Salvar Alterações</button>
                </form>
            </div>

            <!-- ABA 2: SEGURANÇA E SENHAS -->
            <div id="tab-view-security" style="display: none;">
                <form onsubmit="updateAdminCredentials(event)">
                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-size: 13px; margin-bottom: 4px;">Usuário</label>
                        <input type="text" id="sec-username" class="form-input" value="<?= htmlspecialchars($current_username) ?>" required style="width: 100%; padding: 10px; background: var(--bg-input); color: #fff; border: 1px solid var(--border-color); border-radius: 6px;">
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 13px; margin-bottom: 4px;">Nova Senha (deixe em branco para não alterar)</label>
                        <input type="password" id="sec-password" class="form-input" placeholder="Digite uma senha forte..." required style="width: 100%; padding: 10px; background: var(--bg-input); color: #fff; border: 1px solid var(--border-color); border-radius: 6px;">
                    </div>

                    <button type="submit" class="btn-action" style="background: var(--accent); color: #fff; font-weight: 700; width: 100%; justify-content: center; padding: 10px;">Salvar Usuário</button>
                </form>
            </div>

            <!-- ABA 3: SOBRE & AUTO-ATUALIZAÇÃO GITHUB -->
            <div id="tab-view-about" style="display: none; text-align: center; padding: 10px;">
                <img src="logo.svg" alt="KodeWeb Explorer Logo" style="width: 76px; height: 76px; margin-bottom: 12px; filter: drop-shadow(0 0 10px rgba(189,0,255,0.4));">
                <h3 style="color: #fff; font-size: 20px; font-weight: 700;">KodeWeb Explorer</h3>
                <p style="color: #fff; font-weight: 600; font-size: 13px; margin: 4px 0 12px 0;">
                    <?= htmlspecialchars($app_version) ?> - 2026 <a href="https://laralabs.dev" target="_blank" style="color: var(--accent); text-decoration: none; font-weight: 600;">Laralabs</a>
                </p>
                <p style="font-size: 12px; margin-bottom: 24px;">
                    <a href="https://github.com/laraantunes/kodewebexplorer" target="_blank" style="color: var(--accent); text-decoration: none; font-weight: 600;">https://github.com/laraantunes/kodewebexplorer</a>
                </p>
                <p style="font-size: 12px; margin-bottom: 24px;">
                    <a href="https://kodeweb.app.br" target="_blank" style="color: var(--accent); text-decoration: none; font-weight: 600;">https://kodeweb.app.br</a>
                </p>
                
                <button class="btn-action" id="btn-github-update" onclick="checkGithubUpdate()" style="background: linear-gradient(135deg, #bd00ff, #8b00dd); color: #fff; font-weight: 600; width: 60%; justify-content: center; padding: 12px; box-shadow: 0 4px 15px rgba(189,0,255,0.4);">
                    Buscar Atualizações
                </button>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-detail" onclick="closeModal('modal-options')">Fechar</button>
        </div>
    </div>
</div>
