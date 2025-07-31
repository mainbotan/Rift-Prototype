<?php

namespace App\Core\Tenant;

use PDO;
use Rift\Core\Databus\Result;
use Rift\Core\Databus\ResultType;
use Rift\Core\Repositories\Repository;

class TenantRepository extends Repository
{
    public function createTenant(array $data): ResultType
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
    public function getTenantVerifyStatusByUid(string $uid): ResultType 
    {
        $stmt = $this->pdo->prepare("SELECT uid, verify_status FROM tenants WHERE uid=:uid");
        $stmt->bindValue(':uid', $uid, PDO::PARAM_STR);
        return $this->executeQuery($stmt);
    }
    public function getTenantUidAndHashByEmail(string $email): ResultType 
    {
        $stmt = $this->pdo->prepare("SELECT uid, hash FROM tenants WHERE email=:email");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        return $this->executeQuery($stmt);
    }
    public function getTenantUidByEmail(string $email): ResultType 
    {
        $stmt = $this->pdo->prepare("SELECT uid FROM tenants WHERE email=:email");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        return $this->executeQuery($stmt);
    }
    public function updateVerifyStatus(string $uid, string $verify_status): ResultType 
    {
        $stmt = $this->pdo->prepare("UPDATE tenants SET verify_status=:verify_status WHERE uid=:uid");
        $stmt->bindValue(':verify_status', $verify_status, PDO::PARAM_STR);
        $stmt->bindValue(':uid', $uid, PDO::PARAM_STR);
        return $this->executeQuery($stmt);
    }
}