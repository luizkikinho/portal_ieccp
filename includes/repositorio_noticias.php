<?php

declare(strict_types=1);

/**
 * Repositório de Notícias
 * Centraliza a leitura das notícias no banco.
 */

function mapearNoticia(array $row): array
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

/**
 * Lista notícias ordenadas da mais recente para a mais antiga.
 */
function listarNoticias(PDO $pdo, ?int $limite = null): array
{
    $sql = "
        SELECT
            id,
            slug,
            titulo,
            texto,
            imagem,
            data_publicacao
        FROM noticias
        ORDER BY id DESC
    ";

    if ($limite !== null && $limite > 0) {
        $sql .= " LIMIT " . (int)$limite;
    }

    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map('mapearNoticia', $rows ?: []);
}

/**
 * Busca uma notícia pelo ID.
 */
function buscarNoticiaPorId(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare("
        SELECT
            id,
            slug,
            titulo,
            texto,
            imagem,
            data_publicacao
        FROM noticias
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? mapearNoticia($row) : null;
}

function buscarNoticiaPorSlug(PDO $pdo, string $slug): ?array
{
    $stmt = $pdo->prepare("
        SELECT id, slug, titulo, texto, imagem, data_publicacao
        FROM noticias
        WHERE slug = :slug
        LIMIT 1
    ");

    $stmt->execute([':slug' => $slug]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? mapearNoticia($row) : null;
}
