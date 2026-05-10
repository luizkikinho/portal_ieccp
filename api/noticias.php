<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

header('Content-Type: application/json; charset=utf-8');

// --- SISTEMA DE CACHE ---
$arquivoCache = __DIR__ . '/../cache/noticias.json';
$tempoCache = 3600; // 1 hora de validade

// Se o arquivo existe e o tempo de vida dele for menor que 1 hora, devolve o cache (milissegundos)
if (file_exists($arquivoCache) && (time() - filemtime($arquivoCache)) < $tempoCache) {
    echo file_get_contents($arquivoCache);
    exit;
}
// ------------------------

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/repositorio_noticias.php';

try {
    // Só chega aqui se o cache expirou ou não existe (Primeiro acesso)
    $noticias = listarNoticias($pdo);

    $json = json_encode(
        $noticias,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    // Salva o JSON no arquivo de cache para os próximos acessos
    if (!is_dir(__DIR__ . '/../cache')) {
        mkdir(__DIR__ . '/../cache', 0777, true);
    }
    file_put_contents($arquivoCache, $json);

    echo $json;

} catch (Throwable $e) {
    http_response_code(500);

    echo json_encode([
        'erro' => true,
        'mensagem' => 'Erro ao carregar notícias.'
    ], JSON_UNESCAPED_UNICODE);
}