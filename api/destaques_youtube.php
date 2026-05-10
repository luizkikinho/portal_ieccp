<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Permite requisições locais

$destaques = [];

try {
    $urlProxy = 'https://proxy-ieccp.onrender.com/api/v1/destaques';
    
    // Timeout curto para não travar o servidor se o Render estiver em cold start
    $ctx = stream_context_create([
        'http' => ['timeout' => 8, 'ignore_errors' => true]
    ]);
    
    $respostaProxy = @file_get_contents($urlProxy, false, $ctx);

    if ($respostaProxy) {
        $jsonRes = json_decode($respostaProxy, true);
        if (isset($jsonRes['status']) && $jsonRes['status'] === 'success') {
            // Pegamos os 4 primeiros
            $destaques = array_slice($jsonRes['data'], 0, 4);
        }
    }
} catch (Exception $e) {
    // Silencioso para o front-end, mas logado no servidor
    error_log("Erro API Youtube: " . $e->getMessage());
}

echo json_encode(['data' => $destaques]);
exit;