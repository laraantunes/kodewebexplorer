// app/terminal.js - Lógica do Terminal Web

let terminalHistory = [];
let terminalHistoryIdx = -1;
let terminalCwd = '/';
let autocompleteList = [];

function initTerminal() {
    const input = document.getElementById('terminal-cmd-input');
    if (!input) return;

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            const cmd = input.value.trim();
            if (cmd === 'clear') {
                document.getElementById('terminal-output-area').innerHTML = 'KodeWeb Explorer Terminal - Digite os comandos abaixo...';
                input.value = '';
                return;
            }
            if (cmd === 'exit') {
                if (typeof closeModal === 'function') {
                    closeModal('modal-terminal');
                }
                input.value = '';
                return;
            }
            if (cmd !== '') {
                terminalHistory.push(cmd);
                terminalHistoryIdx = terminalHistory.length;
            }
            runTerminalCommand(cmd);
            input.value = '';
            hideAutocomplete();
        }
        
        // History Navigation
        if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (terminalHistory.length > 0 && terminalHistoryIdx > 0) {
                terminalHistoryIdx--;
                input.value = terminalHistory[terminalHistoryIdx];
            }
        }
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (terminalHistory.length > 0 && terminalHistoryIdx < terminalHistory.length - 1) {
                terminalHistoryIdx++;
                input.value = terminalHistory[terminalHistoryIdx];
            } else {
                terminalHistoryIdx = terminalHistory.length;
                input.value = '';
            }
        }

        // Auto-complete trigger Tab key
        if (e.key === 'Tab') {
            e.preventDefault();
            handleTerminalTabComplete();
        }
    });
}

function runTerminalCommand(cmd, resetCwd = false, initialPath = null) {
    const outputArea = document.getElementById('terminal-output-area');
    if (cmd !== '') {
        outputArea.innerHTML += `\n<span style="color:var(--accent);">➔</span> ${cmd}\n`;
    }

    let formData = new FormData();
    formData.append('cmd', cmd);
    formData.append('terminal_id', 'explorer');
    if (resetCwd) {
        formData.append('reset', 'true');
    }
    if (initialPath !== null) {
        formData.append('initial_path', initialPath);
    }

    fetch('api/terminal.php?action=terminal_cmd', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (cmd !== '') {
                // Remove HTML injection vectors
                const sanitizedOutput = data.output.replace(/</g, "&lt;").replace(/>/g, "&gt;");
                outputArea.innerHTML += sanitizedOutput + '\n';
            }
            terminalCwd = data.cwd;
            let displayPath = data.cwd.split(/[\/\\]/).pop();
            if (!displayPath || displayPath === '') displayPath = '/';
            
            document.getElementById('terminal-path-indicator').innerText = displayPath + ' $';
            document.getElementById('terminal-path-indicator').title = data.cwd;
            autocompleteList = data.autocomplete_list || [];
            
            // Scroll to bottom
            outputArea.scrollTop = outputArea.scrollHeight;
        } else {
            outputArea.innerHTML += `<span style="color:red;">Error: ${data.message}</span>\n`;
        }
    }).catch(err => {
        outputArea.innerHTML += `<span style="color:red;">Error: Falha na comunicação com api/terminal.php</span>\n`;
    });
}

function handleTerminalTabComplete() {
    const input = document.getElementById('terminal-cmd-input');
    const value = input.value;
    const words = value.split(' ');
    const lastWord = words.pop();

    if (lastWord === '') return;

    // Filter autocomplete items
    const matches = autocompleteList.filter(item => item.toLowerCase().startsWith(lastWord.toLowerCase()));
    
    if (matches.length === 1) {
        words.push(matches[0]);
        input.value = words.join(' ');
    } else if (matches.length > 1) {
        // Render autocomplete floating dropdown
        showAutocomplete(matches, lastWord);
    }
}

function showAutocomplete(matches, query) {
    const dropdown = document.getElementById('terminal-autocomplete');
    dropdown.innerHTML = '';
    dropdown.style.display = 'block';

    matches.forEach(match => {
        const item = document.createElement('div');
        item.className = 'autocomplete-item';
        item.innerText = match;
        item.onclick = () => {
            const input = document.getElementById('terminal-cmd-input');
            const words = input.value.split(' ');
            words.pop(); // remove query
            words.push(match);
            input.value = words.join(' ');
            hideAutocomplete();
            input.focus();
        };
        dropdown.appendChild(item);
    });
}

function hideAutocomplete() {
    const ac = document.getElementById('terminal-autocomplete');
    if (ac) ac.style.display = 'none';
}

function openTerminalModal(initialPath) {
    const modal = document.getElementById('modal-terminal');
    if (!modal) return;
    
    // Clear terminal history and output for new session
    document.getElementById('terminal-output-area').innerHTML = 'KodeWeb Explorer Terminal - Conectado. Digite "clear" para limpar.<br>';
    const input = document.getElementById('terminal-cmd-input');
    input.value = '';
    
    runTerminalCommand('', true, initialPath);
    
    modal.classList.add('active');
    setTimeout(() => {
        input.focus();
    }, 100);
}

// Inicializar na carga
document.addEventListener('DOMContentLoaded', () => {
    initTerminal();
});
