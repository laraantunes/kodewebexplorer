<?php
// config.php - Configurações vitais do KodeWeb Explorer

$app_version = "v1.1.0-ex";

$local = false;
$env_file = __DIR__ . '/.env';
$env = [];

if (file_exists($env_file)) {
    $env_data = @parse_ini_file($env_file);
    if (is_array($env_data)) {
        $env = $env_data;
        if (isset($env['LOCAL_ENV']) && $env['LOCAL_ENV'] == '1') {
            $local = true;
        }
    }
}
