<?php

namespace App\Tenant\Clients;

use PDO;
use PDOStatement;
use PHPUnit\Framework\Constraint\Operator;
use Rift\Core\Databus\Operation;
use Rift\Core\Databus\OperationOutcome;
use Rift\Core\Repositories\Repository;

class ClientRepository extends Repository
{
    public function getClients(array $data): OperationOutcome 
    {
        $stmt = $this->pdo->prepare("SELECT * FROM clients ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $data['limit'], PDO::PARAM_INT);
        $stmt->bindValue(':offset', $data['offset'], PDO::PARAM_INT);
        return $this->executeQuery($stmt);
    }
    public function createClient(array $data): OperationOutcome 
    {
        return $this->buildInsertQuery($data)
            ->then(fn($stmt) => $this->executeQuery($stmt));
    }
    public function getClientByUid(string $uid): OperationOutcome 
    {
        $stmt = $this->pdo->prepare("SELECT * FROM clients WHERE uid=:uid");
        $stmt->bindValue(':uid', $uid, PDO::PARAM_STR);
        return $this->executeQuery($stmt)
            ->then(function($result) use ($uid) {
                if ($result[0] === null) {
                    return Operation::error(Operation::HTTP_NOT_FOUND, "Client {$uid} not found.");
                } 
                return Operation::success($result[0]);
            });
    }
    public function checkClientByUid(string $uid): OperationOutcome 
    {
        $stmt = $this->pdo->prepare("SELECT uid FROM clients WHERE uid=:uid");
        $stmt->bindValue(':uid', $uid, PDO::PARAM_STR);
        return $this->executeQuery($stmt)
            ->then(function($result) use ($uid) {
                if (!isset($result[0])) {
                    return Operation::success(false);
                } 
                return Operation::success(true);
            });
    }
    public function deleteClientByUid(string $uid): OperationOutcome 
    {
        $stmt = $this->pdo->prepare("DELETE FROM clients WHERE uid=:uid");
        $stmt->bindValue(':uid', $uid, PDO::PARAM_STR);
        return $this->executeQuery($stmt);
    }
    public function updateClient(array $data) {
        $uid = $data['uid'];
        unset($data['uid']);
        return $this->buildUpdateQuery($data, ['uid' => $uid])
            ->then(fn($stmt) => $this->executeQuery($stmt));
    }
}