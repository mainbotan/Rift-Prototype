<?php

namespace App\Core\Tenant;

use PDO;
use Rift\Core\Databus\OperationOutcome;
use Rift\Core\Repositories\Repository;

class TenantRepository extends Repository
{
    public function createTenant(array $data): OperationOutcome
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO tenants (uid, email, hash) 
            VALUES (:uid, :email, :hash)
        ");
        
        $stmt->bindValue(':uid', $data['uid'], PDO::PARAM_STR);
        $stmt->bindValue(':email', $data['email'], PDO::PARAM_STR);
        $stmt->bindValue(':hash', $data['hash'], PDO::PARAM_STR);
        
        return $this->executeQuery($stmt);
    }
    public function getTenantUidAndHashByEmail(string $email): OperationOutcome 
    {
        $stmt = $this->pdo->prepare("SELECT uid, hash FROM tenants WHERE email=:email");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        return $this->executeQuery($stmt);
    }
    public function getTenantUidByEmail(string $email): OperationOutcome 
    {
        $stmt = $this->pdo->prepare("SELECT uid FROM tenants WHERE email=:email");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        return $this->executeQuery($stmt);
    }
}