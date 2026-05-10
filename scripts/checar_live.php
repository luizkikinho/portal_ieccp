<?php
$apiKey = 'hihi achou que ia ver a minha API né >:)';
$channelId = 'UCTYtvExf1Wh-V7t9UAjYJtw';
$arquivoCache = __DIR__ . '/live_status.json';

date_default_timezone_set('America/Sao_Paulo');

$hoje = date('w');
$hora = (int)date('H:i');

$manha = ($agora >= '08:40' && $agora <= '12:30');
$noite = ($agora >= '08:40' && $agora <= '21:00');

$ehDomingo = ($hoje == 0);
$ehHorarioCulto = $ehDomingo && ($manha || $noite);

$force = isset($_GET['force']) && $_GET['force'] == 'true';

if (!$ehHorarioCulto && !$force) {
    echo "Fora do horário de monitoramento.";
    exit;
}

$statusAtual = ['is_live' => false, 'video_id' => null];
if (file_exists($arquivoCache)) {
    $conteudo = file_get_contents($arquivoCache);
    $statusAtual = json_decode($conteudo, true) ?? $statusAtual;
}

function buscarUrl($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $resultado = curl_exec($ch);
    curl_close($ch);
    return $resultado;
}

$novoStatus = [
    'is_live' => false,
    'video_id' => null,
    'titulo' => null,
    'ultima_verificacao' => date('d/m/Y H:i:s')
];

if ($statusAtual['is_live'] && !empty($statusAtual['video_id'])) {
    
    $videoId = $statusAtual['video_id'];
    echo "Modo MONITORAMENTO (Verificando ID: $videoId)... <br>";
    
    $url = "https://www.googleapis.com/youtube/v3/videos?part=snippet&id={$videoId}&key={$apiKey}";
    
    $response = buscarUrl($url);
    $data = json_decode($response, true);

    if (isset($data['items']) && count($data['items']) > 0) {
        $video = $data['items'][0];
        $broadcastStatus = $video['snippet']['liveBroadcastContent']; // 'live', 'none', 'upcoming'
        
        if ($broadcastStatus === 'live') {
            // A live ainda está rolando! Mantém tudo igual.
            $novoStatus['is_live'] = true;
            $novoStatus['video_id'] = $videoId;
            $novoStatus['titulo'] = $video['snippet']['title'];
            echo "A live continua ativa! (Custo: 1 unidade)";
        } else {
            echo "A live acabou. Voltando a buscar novas.";
        }
    } else {
        echo "Vídeo não encontrado ou removido.";
    }

} 
else {
    
    echo "Modo BUSCA (Procurando novas lives)... <br>";
    
    $url = "https://www.googleapis.com/youtube/v3/search?part=snippet&channelId={$channelId}&type=video&eventType=live&key={$apiKey}";
    
    $response = buscarUrl($url);
    $data = json_decode($response, true);
    
    if (isset($data['items']) && count($data['items']) > 0) {
        $video = $data['items'][0];
        $novoStatus['is_live'] = true;
        $novoStatus['video_id'] = $video['id']['videoId'];
        $novoStatus['titulo'] = $video['snippet']['title'];
        echo "Nova live encontrada! (Custo: 100 unidades)";
    } else {
        echo "Nenhuma live encontrada.";
    }
}

file_put_contents($arquivoCache, json_encode($novoStatus));
?>