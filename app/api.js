// app/api.js - Adaptador de Requisições REST/JSON do KodeWeb Explorer

function setApiLoading(loading) {
    const spinner = document.getElementById('sync-spinner');
    if (spinner) spinner.style.display = loading ? 'inline-flex' : 'none';
}

async function apiGet(endpoint, params = {}) {
    setApiLoading(true);
    try {
        const url = new URL(`api/${endpoint}.php`, window.location.href);
        Object.keys(params).forEach(key => {
            if (params[key] !== undefined && params[key] !== null) {
                url.searchParams.append(key, params[key]);
            }
        });
        
        const res = await fetch(url);
        if (!res.ok) throw new Error(`HTTP Erro: ${res.status}`);
        
        const json = await res.json();
        setApiLoading(false);
        return json;
    } catch (err) {
        setApiLoading(false);
        console.error(`Falha no GET ${endpoint}:`, err);
        showToast("Erro na requisição: " + err.message, 'error');
        return { success: false, error: err.message };
    }
}

async function apiPost(endpoint, payload = {}) {
    setApiLoading(true);
    try {
        let body;
        if (payload instanceof FormData) {
            body = payload;
        } else {
            body = new FormData();
            Object.keys(payload).forEach(key => {
                let val = payload[key];
                if (typeof val === 'object' && !(val instanceof File) && !(val instanceof Blob)) {
                    val = JSON.stringify(val);
                }
                body.append(key, val);
            });
        }
        
        const res = await fetch(`api/${endpoint}.php`, {
            method: 'POST',
            body: body
        });
        
        if (!res.ok) throw new Error(`HTTP Erro: ${res.status}`);
        
        const json = await res.json();
        setApiLoading(false);
        return json;
    } catch (err) {
        setApiLoading(false);
        console.error(`Falha no POST ${endpoint}:`, err);
        showToast("Erro na comunicação: " + err.message, 'error');
        return { success: false, error: err.message };
    }
}
