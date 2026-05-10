<?php

declare(strict_types=1);

function mapearPastoral(array $row): array
{
    return [
        'id' => isset($row['id']) ? (int)$row['id'] : 0,
        'slug' => $row['slug'] ?? '',
        'titulo' => $row['titulo'] ?? '',
        'texto' => $row['texto'] ?? '',
        'img' => $row['imagem'] ?? '',
        'data' => !empty($row['data_publicacao'])
            ? date('d/m/Y', strtotime((string)$row['data_publicacao']))
            : ''
    ];
}

function listarPastorais(PDO $pdo, ?int $limite = null): array
{
    $sql = "
        SELECT id, slug, titulo, texto, imagem, data_publicacao
        FROM pastoral
        ORDER BY id DESC
    ";

    if ($limite !== null && $limite > 0) {
        $sql .= " LIMIT " . (int)$limite;
    }

    $stmt = $pdo->query($sql);
    return array_map('mapearPastoral', $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
}

function buscarPastoralPorSlug(PDO $pdo, string $slug): ?array
{
    $stmt = $pdo->prepare("
        SELECT id, slug, titulo, texto, imagem, data_publicacao
        FROM pastoral
        WHERE slug = :slug
        LIMIT 1
    ");

    $stmt->execute([':slug' => $slug]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? mapearPastoral($row) : null;
}

function buscarPastoralPorId(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare("
        SELECT id, slug, titulo, texto, imagem, data_publicacao
        FROM pastoral
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? mapearPastoral($row) : null;
}
