<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\Salesman;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class SalesmanRepository extends Repository
{
    public static function model()
    {
        return Salesman::class;    
    }

    public static function salesmanCreate($request)
    {
        if ($request->hasFile('src')) {
            $path = $request->file('src')->store('salesman/profile', 'public');
        }

        return self::create([
            'shop_id' => $request->shop_id,
            'name' => $request->first_name ?? $request->name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'src' => $path ?? null,
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth ?? null,
            'is_active' => true,
        ]);
    }

    public static function salesmanUpdate($request,$salesman)
    {
        $path = $salesman->src;

        if ($request->hasFile('src')) {
            $path = $request->file('src')->store('salesman/profile', 'public');
        }

        $name = $request->name ?? $request->first_name;
        $salesman->update([
            'shop_id' => $request->shop_id,
            'name' => $name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'src' => $path ?? null,
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
        ]);
    }
}