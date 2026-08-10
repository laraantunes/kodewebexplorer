<!-- Modal do Terminal -->
<div class="modal-overlay" id="modal-terminal">
    <div class="modal-window" style="max-width: 900px; width: 95%; background-color: #030006; border: 1px solid #333; display: flex; flex-direction: column; height: 80vh; padding: 0;">
        <div class="modal-header" style="padding: 15px 20px; border-bottom: 1px solid #222; background-color: #0a0510;">
            <div class="modal-title" id="terminal-modal-title" style="margin: 0; color: #fff; font-size: 16px; display: flex; align-items: center; gap: 10px;">
                🖥️ Terminal
                <span id="terminal-status-badge" style="font-size: 11px; padding: 3px 8px; border-radius: 12px; background: #00ff8822; color: #00ff88;">Pronto</span>
            </div>
            <button class="modal-close" onclick="closeModal('modal-terminal')" title="Fechar">✕</button>
        </div>
        <div class="modal-body" style="flex: 1; display: flex; flex-direction: column; overflow: hidden; padding: 0;">
            <div class="terminal-view-container" style="flex: 1; border: none; border-radius: 0;">
                <div class="terminal-output" id="terminal-output-area" style="flex: 1; padding: 15px;">KodeWeb Explorer Terminal - Digite os comandos abaixo...</div>
                <div class="terminal-prompt-line" style="border-top: 1px solid #222; padding: 10px 15px; background: #05010a;">
                    <span class="terminal-path" id="terminal-path-indicator">/</span>
                    <input type="text" class="terminal-input" id="terminal-cmd-input" placeholder="Digite o comando..." autocomplete="off">
                    
                    <div class="autocomplete-dropdown" id="terminal-autocomplete" style="bottom: 100%; top: auto;">
                        <!-- Autocomplete items -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
