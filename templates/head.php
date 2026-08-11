<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>KodeWeb Explorer</title>
    <link rel="icon" type="image/svg+xml" href="logo.svg">
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    
    <!-- PWA Configuration -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#140523">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="logo.svg">

    <!-- CDNs: Ace Editor (com Tema Dracula e Ferramentas) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.32.7/ace.js" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.32.7/ext-language_tools.min.js" referrerpolicy="no-referrer"></script>
    
    <!-- CDN: SheetJS para Excel / Planilhas (XLSX, XLS, CSV) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js" referrerpolicy="no-referrer"></script>

    <!-- CDN: Mammoth.js para Documentos Word (DOCX) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.8.0/mammoth.browser.min.js" referrerpolicy="no-referrer"></script>

    <!-- CDN: JSZip, Chart.js e PptxViewJS para PowerPoint (PPTX) -->
    <script src="https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/pptxviewjs@1.1.9/dist/PptxViewJS.min.js"></script>

    <script>
        const APP_VERSION = <?= json_encode($app_version) ?>;
        const CURRENT_USERNAME = <?= json_encode($current_username) ?>;
        const INITIAL_WORKSPACE = <?= json_encode($resolved_ws) ?>;
        const IS_LOCAL = <?= json_encode($local) ?>;
    </script>
</head>
