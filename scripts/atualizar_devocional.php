<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../gerenciador_ieccp/funcoes.php';

header('Content-Type: text/html; charset=utf-8');

// 1. Baixar os textos do JSON da RTM
$anoAtual = date('Y');
$hoje = date('Y-m-d');
$jsonRTM = "https://presentediario.rtmbrasil.org.br/js/{$anoAtual}.json";

echo "Iniciando verificação para: <strong>$hoje</strong>...<br>";

// Verifica no Supabase se já existe
$stmt = $pdo->prepare("SELECT COUNT(*) FROM presente_diario WHERE data_publicacao = :data");
$stmt->execute([':data' => $hoje]);
if ($stmt->fetchColumn() > 0) {
    die("O devocional de hoje já está salvo no banco. Nenhuma ação necessária.");
}

echo "Baixando textos da RTM... <br>";
$conteudo = @file_get_contents($jsonRTM);
if (!$conteudo) die("Erro ao acessar o site da RTM.");

$mensagens = json_decode($conteudo, true);
$msgHoje = null;
foreach ($mensagens as $msg) {
    if (isset($msg['publishedAt']) && $msg['publishedAt'] === $hoje) {
        $msgHoje = $msg;
        break;
    }
}
if (!$msgHoje) die("JSON baixado, mas não encontrei a mensagem de hoje ($hoje).");

// 2. BUSCAR IMAGEM E ÁUDIO NA OMNY.FM
echo "Buscando mídia na Omny.fm... <br>";
$orgId = "f895e4af-2068-409d-a7a7-aa9201219358";
$playlistId = "22ed1706-858e-4e7d-b456-ab18012ec1dd";
$urlOmny = "https://omny.fm/api/orgs/{$orgId}/playlists/{$playlistId}/clips?pageSize=1&cursor=1";

$imgUrl = '';
$audioUrl = '';
$conteudoOmny = @file_get_contents($urlOmny);
if ($conteudoOmny) {
    $dadosOmny = json_decode($conteudoOmny, true);
    if (!empty($dadosOmny['Clips'][0])) {
        $imgUrl = $dadosOmny['Clips'][0]['ImageUrl'] ?? '';
        $audioUrl = $dadosOmny['Clips'][0]['AudioUrl'] ?? '';
    }
}
if (empty($imgUrl)) echo "<span style='color: orange;'>Aviso: Imagem Omny.fm não capturada.</span><br>";

// 3. BUSCAR VERSÍCULO NA API.BIBLE (Usando a chave segura do .env)
echo "Buscando versículo na api.bible... <br>";
$textoApi = '';
$refApi = '';
$apiKey = $env['BIBLE_API_KEY']; // Puxa do db.php
$bibleId = "41a6caa722a21d88-01";

$bibleMap = [
    "Gn" => "GEN",
    "Êx" => "EXO",
    "Lv" => "LEV",
    "Nm" => "NUM",
    "Dt" => "DEU",
    "Josué" => "JOS",
    "Juízes" => "JDG",
    "Rute" => "RUT",
    "1Sm" => "1SA",
    "2Sm" => "2SA",
    "1Rs" => "1KI",
    "2Rs" => "2KI",
    "1Cr" => "1CH",
    "2Cr" => "2CH",
    "Ed" => "EZR",
    "Neemias" => "NEH",
    "Ester" => "EST",
    "Jó" => "JOB",
    "Sl" => "PSA",
    "Salmo" => "PSA",
    "Salmos" => "PSA",
    "Pv" => "PRO",
    "Provérbios" => "PRO",
    "Ec" => "ECC",
    "Eclesiastes" => "ECC",
    "Cânticos" => "SNG",
    "Is" => "ISA",
    "Jr" => "JER",
    "Lamentações" => "LAM",
    "Ezequiel" => "EZK",
    "Dn" => "DAN",
    "Daniel" => "DAN",
    "Oseias" => "HOS",
    "Jl" => "JOL",
    "Joel" => "JOL",
    "Am" => "AMO",
    "Obadias" => "OBA",
    "Jonas" => "JON",
    "Miqueias" => "MIC",
    "Naum" => "NAM",
    "Habacuque" => "HAB",
    "Sofonias" => "ZEP",
    "Ageu" => "HAG",
    "Zacarias" => "ZEC",
    "Malaquias" => "MAL",
    "Mt" => "MAT",
    "Mc" => "MRK",
    "Lc" => "LUK",
    "Lucas" => "LUK",
    "Jo" => "JHN",
    "João" => "JHN",
    "At" => "ACT",
    "Rm" => "ROM",
    "Romanos" => "ROM",
    "1Co" => "1CO",
    "2Co" => "2CO",
    "Gl" => "GAL",
    "Ef" => "EPH",
    "Fp" => "PHP",
    "Cl" => "COL",
    "1Ts" => "1TH",
    "2Ts" => "2TH",
    "1Tm" => "1TI",
    "2Tm" => "2TI",
    "Tt" => "TIT",
    "Fm" => "PHM",
    "Hb" => "HEB",
    "Tg" => "JAS",
    "1Pe" => "1PE",
    "2Pe" => "2PE",
    "1Jo" => "1JN",
    "2Jo" => "2JN",
    "3Jo" => "3JN",
    "Jd" => "JUD",
    "Ap" => "REV"
];

if (preg_match('/\(([^)]+)\)[^()]*$/', $msgHoje['keyVerse'], $matches)) {
    $referenciaPura = trim($matches[1]);
    $espacoIndex = strrpos($referenciaPura, ' ');

    if ($espacoIndex !== false) {
        $nomeLivro = trim(substr($referenciaPura, 0, $espacoIndex));
        $capVerso = trim(substr($referenciaPura, $espacoIndex + 1));

        $bookId = $bibleMap[$nomeLivro] ?? '';

        if ($bookId) {
            $versoInicio = explode('-', $capVerso)[0];
            $versoInicio = preg_replace('/[a-zA-Z]/', '', $versoInicio);

            $verseId = "{$bookId}.{$versoInicio}";
            $urlBible = "https://rest.api.bible/v1/bibles/{$bibleId}/verses/{$verseId}?content-type=text&include-chapter-numbers=false&include-verse-numbers=false";

            $context = stream_context_create(["http" => ["header" => "api-key: {$apiKey}\r\n"]]);
            $resBible = @file_get_contents($urlBible, false, $context);

            if ($resBible) {
                $dadosBible = json_decode($resBible, true);
                if (!empty($dadosBible['data']['content'])) {
                    $textoApi = trim(strip_tags($dadosBible['data']['content']));
                    $refApi = $referenciaPura;
                    echo "<span style='color: green;'>Versículo carregado da API.BIBLE!</span><br>";
                }
            }
        }
    }
}

if (empty($textoApi)) {
    echo "<span style='color: orange;'>Aviso: API falhou. Usando versículo local do PD.</span><br>";
    $textoApi = preg_replace('/\s*\([^)]+\)[^()]*$/', '', $msgHoje['keyVerse']);
    $refApi = preg_match('/\(([^)]+)\)[^()]*$/', $msgHoje['keyVerse'], $matches) ? $matches[1] : "Versículo do Dia";
}

// 4. SALVAR TUDO NO BANCO (Supabase)
try {
    $sqlInsert = "INSERT INTO presente_diario 
        (data_publicacao, titulo, referencia_bilbica, versiculo_chave, conteudo, autor, frase_destaque, imagem, audio, youversionLink, texto_api, ref_api) 
        VALUES 
        (:data, :titulo, :ref, :verso, :conteudo, :autor, :frase, :img, :audio, :youversion, :texto_api, :ref_api)";

    $stmt = $pdo->prepare($sqlInsert);
    $stmt->execute([
        ':data'       => $msgHoje['publishedAt'],
        ':titulo'     => $msgHoje['title'],
        ':ref'        => $msgHoje['reference'],
        ':verso'      => $msgHoje['keyVerse'],
        ':conteudo'   => $msgHoje['content'],
        ':autor'      => $msgHoje['author'],
        ':frase'      => $msgHoje['excerpt'],
        ':img'        => $imgUrl,
        ':audio'      => $audioUrl,
        ':youversion' => $msgHoje['youversionLink'] ?? '',
        ':texto_api'  => $textoApi,
        ':ref_api'    => $refApi
    ]);

    if (function_exists('enviarNotificacaoOneSignal')) {
        $slug = gerarSlug($msgHoje['title']);
        enviarNotificacaoOneSignal('Novo Presente Diário!', $msgHoje['title'], "https://ieccp.com.br/pd/" . $slug);
    }
    echo "✅ Sucesso! Devocional salvo com versículo dinâmico no Supabase.";
} catch (PDOException $e) {
    echo "❌ Erro ao gravar no banco Supabase: " . $e->getMessage();
}
