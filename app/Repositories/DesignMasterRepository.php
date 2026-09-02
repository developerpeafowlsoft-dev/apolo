<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\DesignMaster;

class DesignMasterRepository extends Repository
{
    public static function model()
    {
        return DesignMaster::class;    
    }

    public static function designMasterCreate($request)
    {
        $designMaster = self::create([
            'shop_id' => $request?->shop_id,
            'design_number' => $request->design_number,
            'product_id' => $request->itemname,
            'quantity' => $request->quantity ?? 0,
            'min_stock' => $request->minStock,
            'max_stock' => $request->maxStock,
            'buy_price' => $request->buy_price ?? 0,
            'price' => $request->price,
            'discount_percentage' => $request->discount_percentage,
            'mrp' => $request->mrp,
            'mark_up' => $request->mark_up,
            'mark_down' => $request->mark_down,
            'account_master_id' => $request->account_master,
            'account_master_name' => $request->account_master_name,
            'remark' => $request->remark,
        ]);

        return $designMaster;
    }

    public static function designMasterupdate($request,$designMaster)
    {
        $designMaster = self::update($designMaster,[
            'design_number' => $request->design_number,
            'product_id' => $request->itemname,
            'quantity' => $request->quantity ?? 0,
            'min_stock' => $request->minStock,
            'max_stock' => $request->maxStock,
            'buy_price' => $request->buy_price ?? 0,
            'price' => $request->price,
            'discount_percentage' => $request->discount_percentage,
            'mrp' => $request->mrp,
            'mark_up' => $request->mark_up,
            'mark_down' => $request->mark_down,
            'account_master_id' => $request->account_master,
            'account_master_name' => $request->account_master_name,
            'remark' => $request->remark,
        ]);

        return $designMaster;
    }


    public static function designMasterByInwardProductupdate($row,$designMasterId)
    {
        $designMaster = DesignMaster::find($designMasterId);

        if (!$designMaster) {
            return false;
        }

        $designMaster->update([
            'quantity' => $row['qty'],
            'buy_price' => $row['purcRate'],
            'price' => $row['amount'],
            'discount_percentage' => $row['disc'],
            'mrp' => $row['mrp'],
            'mark_up' => $row['mark_up'],
            'mark_down' => $row['mark_down'],
        ]);

        return $designMaster;
    }
}