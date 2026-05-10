<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Aqui você PRECISA do seu banco de dados funcionando (o .env precisa estar ok)
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$pdHoje = null;
try {
  $stmt = $pdo->query("SELECT * FROM presente_diario ORDER BY data_publicacao DESC LIMIT 1");
  $pdHoje = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  error_log('Erro ao buscar Presente Diário: ' . $e->getMessage());
}

// Valores padrão caso dê erro ou não tenha PD
$versoTexto = "Alegrei-me quando me disseram: vamos à casa do Senhor";
$versoRef = "SALMOS 122:1";
$slugPD = "presente-diario"; // Fallback para a página geral

if ($pdHoje) {
  // A lógica exata que você já tinha no index.php
  $slugPD = gerarSlug($pdHoje['titulo']);
  if (!empty($pdHoje['texto_api']) && !empty($pdHoje['ref_api'])) {
    $versoTexto = trim($pdHoje['texto_api']);
    $versoRef = mb_strtoupper($pdHoje['ref_api'], 'UTF-8');
  } else {
    preg_match('/\(([^)]+)\)[^()]*$/', $pdHoje['versiculo_chave'] ?? '', $matches);
    $versoRef = isset($matches[1]) ? mb_strtoupper($matches[1], 'UTF-8') : 'VERSÍCULO DO DIA';
    $versoTexto = trim(preg_replace('/\s*\([^)]+\)[^()]*$/', '', $pdHoje['versiculo_chave'] ?? ''));
  }
}

// Devolvemos um JSON limpinho
echo json_encode([
  'status' => 'success',
  'data' => [
    'slug' => $slugPD,
    'texto' => $versoTexto,
    'ref' => $versoRef
  ]
]);
exit;