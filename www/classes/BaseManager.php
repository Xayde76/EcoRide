<?php

abstract class BaseManager {
    protected PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    protected function prepare(string $sql): \PDOStatement {
        return $this->pdo->prepare($sql);
    }

    protected function execute(\PDOStatement $stmt, array $params = []): bool {
        try {
            return $stmt->execute($params);
        } catch (\PDOException $e) {
            LoggerService::error('Database execute error', [
                'sql' => $stmt->queryString,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    protected function fetch(\PDOStatement $stmt): mixed {
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    protected function fetchAll(\PDOStatement $stmt): array {
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    protected function fetchColumn(\PDOStatement $stmt): mixed {
        return $stmt->fetchColumn();
    }

    protected function lastInsertId(): string {
        return $this->pdo->lastInsertId();
    }

    protected function beginTransaction(): void {
        $this->pdo->beginTransaction();
    }

    protected function commit(): void {
        $this->pdo->commit();
    }

    protected function rollBack(): void {
        $this->pdo->rollBack();
    }

    protected function inTransaction(): bool {
        return $this->pdo->inTransaction();
    }
}
