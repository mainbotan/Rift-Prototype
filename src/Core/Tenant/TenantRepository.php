<?php

namespace App\Repositories\System;

use PDO;
use PDOStatement;
use Rift\Core\Databus\OperationOutcome;
use Rift\Core\Repositories\AbstractRepository;

class TenantsRepository extends AbstractRepository
{
    public function createTenant(array $data): OperationOutcome
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO tenants (id, email, password, status, plan_id) 
            VALUES (:id, :email, :password, :status, :plan_id)
        ");
        
        $stmt->bindValue(':id', $data['id'], PDO::PARAM_STR);
        $stmt->bindValue(':email', $data['email'], PDO::PARAM_STR);
        $stmt->bindValue(':password', $data['password'], PDO::PARAM_STR);
        $stmt->bindValue(':status', $data['status'], PDO::PARAM_STR);
        $stmt->bindValue(':plan_id', $data['plan_id'], PDO::PARAM_INT);
        
        return $this->executeQuery($stmt);
    }
    public function getTenantIdByEmail(string $email): OperationOutcome 
    {
        $stmt = $this->pdo->prepare("SELECT id FROM tenants WHERE email=:email");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        return $this->executeQuery($stmt);
    }
}