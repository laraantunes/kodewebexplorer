<?php
// api/files.php - Gerenciamento completo de pastas e arquivos no servidor
require_once __DIR__ . '/base.php';

try {
    switch ($action) {
        case 'list_files':
            $relativePath = $_GET['path'] ?? $_POST['path'] ?? '';
            $absPath = get_absolute_path($relativePath);
            
            if (!is_dir($absPath)) {
                if (is_file($absPath)) {
                    $parentPath = dirname($absPath);
                    echo json_encode([
                        'success' => true,
                        'is_file_redirect' => true,
                        'parent_path' => get_relative_path($parentPath),
                        'file_to_select' => get_relative_path($absPath)
                    ]);
                    exit;
                }
                throw new Exception("Diretório não encontrado: " . htmlspecialchars($relativePath));
            }
            
            $items = [];
            $scans = @scandir($absPath);
            if ($scans === false) {
                throw new Exception("Sem permissão de leitura neste diretório.");
            }
            
            foreach ($scans as $file) {
                if ($file === '.' || $file === '..') continue;
                
                $fullPath = $absPath . '/' . $file;
                $isDir = is_dir($fullPath);
                $itemRelPath = get_relative_path($fullPath);
                $size = $isDir ? 0 : @filesize($fullPath);
                $mtime = @filemtime($fullPath);
                
                $items[] = [
                    'name' => $file,
                    'path' => $itemRelPath,
                    'is_dir' => $isDir,
                    'size' => $size,
                    'size_formatted' => format_size($size),
                    'modified' => $mtime ? date('d/m/Y H:i', $mtime) : '-',
                    'timestamp' => $mtime ?: 0,
                    'ext' => $isDir ? '' : strtolower(pathinfo($file, PATHINFO_EXTENSION)),
                    'perms' => get_file_permissions($fullPath)
                ];
            }
            
            // Ordenar: pastas primeiro, depois ordem alfabética do nome
            usort($items, function($a, $b) {
                if ($a['is_dir'] && !$b['is_dir']) return -1;
                if (!$a['is_dir'] && $b['is_dir']) return 1;
                return strcasecmp($a['name'], $b['name']);
            });
            
            echo json_encode([
                'success' => true, 
                'current_path' => $relativePath, 
                'files' => $items,
                'total_items' => count($items)
            ]);
            break;

        case 'list_tree':
            // Carrega subpastas para o Explorer à esquerda (Lazy loading por nó)
            $relativePath = $_GET['path'] ?? '';
            $absPath = get_absolute_path($relativePath);
            
            if (!is_dir($absPath)) {
                echo json_encode(['success' => true, 'folders' => []]);
                break;
            }
            
            $folders = [];
            $scans = @scandir($absPath);
            if ($scans !== false) {
                foreach ($scans as $f) {
                    if ($f === '.' || $f === '..') continue;
                    $full = $absPath . '/' . $f;
                    if (is_dir($full)) {
                        $folders[] = [
                            'name' => $f,
                            'path' => get_relative_path($full),
                            'has_children' => count(array_filter(@scandir($full) ?: [], fn($x) => $x !== '.' && $x !== '..' && is_dir($full.'/'.$x))) > 0
                        ];
                    }
                }
            }
            usort($folders, fn($a, $b) => strcasecmp($a['name'], $b['name']));
            echo json_encode(['success' => true, 'folders' => $folders]);
            break;

        case 'create_folder':
            $parent = $_POST['parent'] ?? '';
            $name = trim($_POST['name'] ?? '');
            if (empty($name)) throw new Exception("Nome da pasta não pode ser vazio.");
            
            $absParent = get_absolute_path($parent);
            $target = $absParent . '/' . $name;
            
            if (file_exists($target)) throw new Exception("Já existe uma pasta ou arquivo com este nome.");
            
            if (@mkdir($target, 0755, true)) {
                echo json_encode(['success' => true, 'message' => "Pasta '$name' criada com sucesso!"]);
            } else {
                throw new Exception("Falha ao criar pasta. Verifique permissões.");
            }
            break;

        case 'create_file':
            $parent = $_POST['parent'] ?? '';
            $name = trim($_POST['name'] ?? '');
            if (empty($name)) throw new Exception("Nome do arquivo não pode ser vazio.");
            
            $absParent = get_absolute_path($parent);
            $target = $absParent . '/' . $name;
            
            if (file_exists($target)) throw new Exception("O arquivo '$name' já existe nesta pasta.");
            
            if (@file_put_contents($target, "") !== false) {
                echo json_encode(['success' => true, 'message' => "Arquivo '$name' criado!"]);
            } else {
                throw new Exception("Falha ao criar arquivo de texto.");
            }
            break;

        case 'read_file':
            $path = $_POST['path'] ?? $_GET['path'] ?? '';
            $absPath = get_absolute_path($path);
            
            if (!file_exists($absPath) || is_dir($absPath)) {
                throw new Exception("Arquivo não encontrado ou é uma pasta.");
            }
            
            $content = file_get_contents($absPath);
            echo json_encode(['success' => true, 'content' => $content, 'path' => $path]);
            break;

        case 'save_file':
            $path = $_POST['path'] ?? '';
            $content = $_POST['content'] ?? '';
            $absPath = get_absolute_path($path);
            
            if (empty($path)) throw new Exception("Caminho inválido.");
            
            if (@file_put_contents($absPath, $content) !== false) {
                echo json_encode(['success' => true, 'message' => "Arquivo salvo com sucesso!"]);
            } else {
                throw new Exception("Não foi possível salvar o arquivo no disco.");
            }
            break;

        case 'rename':
            $path = $_POST['path'] ?? '';
            $newName = trim($_POST['new_name'] ?? '');
            if (empty($path) || empty($newName)) throw new Exception("Dados insuficientes para renomear.");
            
            $absOld = get_absolute_path($path);
            if (!file_exists($absOld)) throw new Exception("Item de origem não existe.");
            
            $parent = dirname($absOld);
            $absNew = $parent . '/' . $newName;
            
            if (file_exists($absNew)) throw new Exception("Já existe outro arquivo ou pasta com o nome '$newName'.");
            
            if (@rename($absOld, $absNew)) {
                echo json_encode(['success' => true, 'message' => "Renomeado para '$newName'!", 'new_path' => get_relative_path($absNew)]);
            } else {
                throw new Exception("Erro ao tentar renomear o item.");
            }
            break;

        case 'delete':
            $items = $_POST['items'] ?? [];
            if (is_string($items)) $items = json_decode($items, true) ?: [$items];
            
            if (empty($items)) throw new Exception("Nenhum item selecionado para excluir.");
            
            $deleted = 0;
            foreach ($items as $relPath) {
                $abs = get_absolute_path($relPath);
                if (file_exists($abs)) {
                    if (is_dir($abs)) {
                        // Exclusão recursiva de diretório
                        delete_dir($abs);
                    } else {
                        @unlink($abs);
                    }
                    $deleted++;
                }
            }
            echo json_encode(['success' => true, 'message' => "$deleted item(ns) excluído(s) com sucesso!"]);
            break;

        case 'copy':
        case 'move':
            $items = $_POST['items'] ?? [];
            $destFolder = $_POST['destination'] ?? '';
            if (is_string($items)) $items = json_decode($items, true) ?: [$items];
            
            $absDest = get_absolute_path($destFolder);
            if (!is_dir($absDest)) throw new Exception("A pasta de destino não foi encontrada.");
            
            $processed = 0;
            foreach ($items as $rel) {
                $absSrc = get_absolute_path($rel);
                if (!file_exists($absSrc)) continue;
                
                $itemName = basename($absSrc);
                $targetPath = $absDest . '/' . $itemName;
                
                // Se já existir no destino, acrescenta prefixo cópia
                if (file_exists($targetPath) && $absSrc !== $targetPath) {
                    $targetPath = $absDest . '/copia_' . time() . '_' . $itemName;
                }
                
                if ($action === 'move') {
                    if (@rename($absSrc, $targetPath)) $processed++;
                } else {
                    if (is_dir($absSrc)) {
                        copy_dir($absSrc, $targetPath);
                        $processed++;
                    } else {
                        if (@copy($absSrc, $targetPath)) $processed++;
                    }
                }
            }
            
            $lbl = $action === 'move' ? 'movido(s)' : 'copiado(s)';
            echo json_encode(['success' => true, 'message' => "$processed item(ns) $lbl com sucesso!"]);
            break;

        default:
            throw new Exception("Ação '$action' não reconhecida em files.php");
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

// Funções utilitárias locais de exclusão/cópia recursiva
function delete_dir($dir) {
    if (!is_dir($dir)) return;
    $files = @array_diff(@scandir($dir) ?: [], ['.', '..']);
    foreach ($files as $file) {
        $path = "$dir/$file";
        is_dir($path) ? delete_dir($path) : @unlink($path);
    }
    @rmdir($dir);
}

function copy_dir($src, $dst) {
    @mkdir($dst, 0755, true);
    $files = @array_diff(@scandir($src) ?: [], ['.', '..']);
    foreach ($files as $file) {
        $srcPath = "$src/$file";
        $dstPath = "$dst/$file";
        is_dir($srcPath) ? copy_dir($srcPath, $dstPath) : @copy($srcPath, $dstPath);
    }
}
