<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\FinancialYear;

class FinancialYearRepository extends Repository
{
    public static function model()
    {
        return FinancialYear::class;    
    }

    public static function financialAddorUpdate($request)
    {
        return self::query()->create(
            [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'name' => $request->name,
                'is_active' => 1,
            ]
        );
    }
}