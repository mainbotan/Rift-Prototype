<?php

namespace App\Tenant\Clients;

use PDO;
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
        $stmt = $this->pdo->prepare("INSERT INTO clients (uid, full_name, email, phone) VALUES (:uid, :full_name, :email, :phone)");
        $stmt->bindValue(':uid', $data['uid'], PDO::PARAM_STR);
        $stmt->bindValue(':full_name', $data['full_name'], PDO::PARAM_STR);
        $stmt->bindValue(':email', $data['email'], PDO::PARAM_STR);
        $stmt->bindValue(':phone', $data['phone'], PDO::PARAM_STR);
        return $this->executeQuery($stmt);
    }
}