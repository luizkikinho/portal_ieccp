<?php

declare(strict_types=1);

function mapearEvento(array $row): array
{
    return [
        'id' => isset($row['id']) ? (int)$row['id'] : 0,
        'slug' => $row['slug'] ?? '',
        'titulo' => $row['titulo'] ?? '',
        'local' => $row['local'] ?? '',
        'texto' => $row['texto'] ?? '',
        'img' => $row['imagem'] ?? '',
        'data_inicio' => !empty($row['data_inicio'])
            ? date('d/m/Y', strtotime((string)$row['data_inicio']))
            : '',
        'hora_inicio' => !empty($row['hora_inicio'])
            ? substr((string)$row['hora_inicio'], 0, 5)
            : '',
        'data_fim' => !empty($row['data_fim'])
            ? date('d/m/Y', strtotime((string)$row['data_fim']))
            : '',
        'hora_fim' => !empty($row['hora_fim'])
            ? substr((string)$row['hora_fim'], 0, 5)
            : '',
        'data' => !empty($row['data_inicio'])
            ? date('d/m/Y', strtotime((string)$row['data_inicio']))
            : ''
    ];
}

function listarEventos(PDO $pdo, ?int $limite = null): array
{
    $sql = "
        SELECT id, slug, titulo, local, texto, imagem, data_inicio, hora_inicio, data_fim, hora_fim
        FROM agenda
        ORDER BY data_inicio ASC NULLS LAST, hora_inicio ASC NULLS LAST, id DESC
    ";

    if ($limite !== null && $limite > 0) {
        $sql .= " LIMIT " . (int)$limite;
    }

    $stmt = $pdo->query($sql);
    return array_map('mapearEvento', $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
}

function buscarEventoPorSlug(PDO $pdo, string $slug): ?array
{
    $stmt = $pdo->prepare("
        SELECT id, slug, titulo, local, texto, imagem, data_inicio, hora_inicio, data_fim, hora_fim
        FROM agenda
        WHERE slug = :slug
        LIMIT 1
    ");

    $stmt->execute([':slug' => $slug]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? mapearEvento($row) : null;
}

function buscarEventoPorId(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare("
        SELECT id, slug, titulo, local, texto, imagem, data_inicio, hora_inicio, data_fim, hora_fim
        FROM agenda
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? mapearEvento($row) : null;
}
