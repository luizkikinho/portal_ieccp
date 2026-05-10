<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

header('Content-Type: application/json; charset=utf-8');

// --- SISTEMA DE CACHE ---
$arquivoCache = __DIR__ . '/../cache/pastoral.json';
$tempoCache = 3600; // 1 hora

if (file_exists($arquivoCache) && (time() - filemtime($arquivoCache)) < $tempoCache) {
    echo file_get_contents($arquivoCache);
    exit;
}
// ------------------------

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/repositorio_pastoral.php';

try {
    // Se o seu repositório exigir o limite de 4 como estava no index, basta colocar listarPastorais($pdo, 4)
    $pastoral = listarPastorais($pdo);

    $json = json_encode(
        $pastoral,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    if (!is_dir(__DIR__ . '/../cache')) {
        mkdir(__DIR__ . '/../cache', 0777, true);
    }
    file_put_contents($arquivoCache, $json);

    echo $json;

} catch (Throwable $e) {
    http_response_code(500);

    echo json_encode([
        'erro' => true,
        'mensagem' => 'Erro ao carregar pastoral.'
    ], JSON_UNESCAPED_UNICODE);
}