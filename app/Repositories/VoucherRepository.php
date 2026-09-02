<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\Voucher;

class VoucherRepository extends Repository
{
    public static function model()
    {
        return Voucher::class;    
    }
}