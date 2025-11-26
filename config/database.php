<?php

declare(strict_types=1);
/**
 * ZelaLar - Funções Globais do Banco de Dados (Padronizado 2024)
 */

define('DB_DSN', 'mysql:host=localhost;dbname=zelalar_db;charset=utf8mb4');
define('DB_USER', 'root');
define('DB_PASS', '2341');
define('DB_OPTIONS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
]);

/**
 * Singleton PDO connection
 */
function getDatabase(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    try {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, DB_OPTIONS);
        return $pdo;
    } catch (PDOException $e) {
        error_log('DB connection error: ' . $e->getMessage());
        throw new RuntimeException('Erro na conexão com banco de dados.');
    }
}

/**
 * SELECT que retorna múltiplas linhas (array de arrays)
 */
function dbQuery(string $sql, array $params = []): array
{
    $stmt = getDatabase()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * SELECT que retorna uma linha
 */
function dbFetchOne(string $sql, array $params = []): ?array
{
    $stmt = getDatabase()->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row ? $row : null;
}

/**
 * Retorna um valor escalar (coluna única)
 */
function dbFetchValue(string $sql, array $params = []) {
    $stmt = getDatabase()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

/**
 * INSERT/UPDATE/DELETE - retorna linhas afetadas
 */
function dbExecute(string $sql, array $params = []): int
{
    $stmt = getDatabase()->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}

/**
 * Transação com callback seguro
 */
function dbTransaction(callable $callback)
{
    $pdo = getDatabase();
    try {
        $pdo->beginTransaction();
        $result = $callback($pdo);
        $pdo->commit();
        return $result;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}
// Fim do utilitário DB
