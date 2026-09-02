<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\VoucherEntry;

class VoucherEntryRepository extends Repository
{
    public static function model()
    {
        return VoucherEntry::class;    
    }
}