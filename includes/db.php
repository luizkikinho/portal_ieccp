<?php
date_default_timezone_set('America/Sao_Paulo');
$caminhoEnv = __DIR__ . '/../../config.env';

if (!file_exists($caminhoEnv)) {
    die("Erro Crítico: Arquivo de configuração (.env) não encontrado.");
}

$env = parse_ini_file($caminhoEnv);

if (!$env) {
    die("Erro Crítico: Falha ao ler o arquivo de configuração (.env).");
}

try {
    $dsn = "pgsql:host={$env['DB_HOST']};port={$env['DB_PORT']};dbname={$env['DB_NAME']}";

    $pdo = new PDO($dsn, $env['DB_USER'], $env['DB_PASS']);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    die("Não foi possível conectar ao banco de dados no momento: " . $e->getMessage());
}
