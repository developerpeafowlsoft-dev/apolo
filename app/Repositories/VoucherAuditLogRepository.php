<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\VoucherAuditLog;

class VoucherAuditLogRepository extends Repository
{
    public static function model()
    {
        return VoucherAuditLog::class;    
    }
}