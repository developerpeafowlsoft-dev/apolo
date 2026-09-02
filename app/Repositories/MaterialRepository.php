<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\Material;

class MaterialRepository extends Repository
{
    public static function model()
    {
        return Material::class;    
    }

    public static function materialrCreate($request)
    {
        return self::create($request->all());
    }

    public static function materialUpdate($request,$material)
    {
        $material->update($request->all());
        return $material;
    }
}