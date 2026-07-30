<!-- Carregamento Modular de Scripts Javascript Vanilla -->
<script src="app/api.js?v=<?= time() ?>"></script>
<script src="app/state.js?v=<?= time() ?>"></script>
<script src="app/explorer.js?v=<?= time() ?>"></script>
<script src="app/view.js?v=<?= time() ?>"></script>
<script src="app/details.js?v=<?= time() ?>"></script>
<script src="app/viewer.js?v=<?= time() ?>"></script>
<script src="app/init.js?v=<?= time() ?>"></script>

<script>
// Registro do Service Worker PWA
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('sw.js')
            .then(reg => console.log('Service Worker PWA ativado:', reg.scope))
            .catch(err => console.warn('Erro na ativação SW:', err));
    });
}
</script>
