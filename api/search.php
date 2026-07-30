<?php
// api/search.php - Busca recursiva por arquivos e pastas
require_once __DIR__ . '/base.php';

try {
    $query = trim($_GET['query'] ?? $_POST['query'] ?? '');
    $scope = $_GET['scope'] ?? 'current'; // 'current' ou 'all'
    $currentPath = $_GET['current_path'] ?? '';
    
    if (empty($query) || strlen($query) < 1) {
        echo json_encode(['success' => true, 'results' => []]);
        exit;
    }
    
    $baseDir = ($scope === 'current' && !empty($currentPath)) ? get_absolute_path($currentPath) : WORKSPACE_ROOT;
    
    if (!is_dir($baseDir)) {
        throw new Exception("Diretório de busca inacessível.");
    }
    
    $results = [];
    $maxResults = 250; // Teto para evitar travamento em hospedagens com milhões de arquivos
    
    search_recursive($baseDir, strtolower($query), $results, $maxResults);
    
    echo json_encode([
        'success' => true, 
        'query' => $query, 
        'scope' => $scope,
        'count' => count($results),
        'results' => $results
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function search_recursive($dir, $q, &$results, $max) {
    if (count($results) >= $max) return;
    
    $items = @scandir($dir);
    if ($items === false) return;
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $fullPath = $dir . '/' . $item;
        
        // Verifica correspondência no nome
        if (strpos(strtolower($item), $q) !== false) {
            $isDir = is_dir($fullPath);
            $size = $isDir ? 0 : @filesize($fullPath);
            $mtime = @filemtime($fullPath);
            $rel = get_relative_path($fullPath);
            $parentRel = dirname($rel) === '.' ? '' : dirname($rel);
            
            $results[] = [
                'name' => $item,
                'path' => $rel,
                'parent_path' => str_replace('\\', '/', $parentRel),
                'is_dir' => $isDir,
                'size_formatted' => format_size($size),
                'modified' => $mtime ? date('d/m/Y H:i', $mtime) : '-',
                'ext' => $isDir ? '' : strtolower(pathinfo($item, PATHINFO_EXTENSION))
            ];
            if (count($results) >= $max) break;
        }
        
        // Recursão para dentro das pastas
        if (is_dir($fullPath)) {
            // Não vasculha a própria pasta de dados do sistema se não solicitado
            if (basename($fullPath) !== '.git' && basename($fullPath) !== 'data') {
                search_recursive($fullPath, $q, $results, $max);
            }
        }
    }
}
