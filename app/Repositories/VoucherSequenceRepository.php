<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\VoucherSequence;

class VoucherSequenceRepository extends Repository
{
    public static function model()
    {
        return VoucherSequence::class;    
    }
}