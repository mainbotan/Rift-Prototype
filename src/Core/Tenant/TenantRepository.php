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
            INSERT INTO tenants (uid, email, finger) 
            VALUES (:uid, :email, :finger)
        ");
        
        $stmt->bindValue(':uid', $data['uid'], PDO::PARAM_STR);
        $stmt->bindValue(':email', $data['email'], PDO::PARAM_STR);
        $stmt->bindValue(':finger', $data['finger'], PDO::PARAM_STR);
        
        return $this->executeQuery($stmt);
    }
    public function getTenantIdByEmail(string $email): OperationOutcome 
    {
        $stmt = $this->pdo->prepare("SELECT id FROM tenants WHERE email=:email");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        return $this->executeQuery($stmt);
    }
}