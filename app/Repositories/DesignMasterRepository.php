<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\DesignMaster;
use App\Models\AccountMaster;

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


    public static function designMasterByInwardProductupdate($row, $designMasterId, $partyId = null, $partyName = null)
    {
        $designMaster = null;
        if (!empty($designMasterId)) {
            $designMaster = DesignMaster::find($designMasterId);
        }

        if (!$designMaster && !empty($row['designNo'])) {
            $shop = generaleSetting('shop');
            $designMaster = DesignMaster::where('design_number', trim($row['designNo']))
                ->when($shop?->id, fn($q) => $q->where('shop_id', $shop->id))
                ->first();
        }

        if (!$designMaster) {
            return false;
        }

        $updateData = [
            'quantity' => $row['qty'],
            'buy_price' => $row['purcRate'],
            'price' => $row['amount'],
            'discount_percentage' => $row['disc'],
            'mrp' => $row['mrp'],
            'mark_up' => $row['mark_up'],
            'mark_down' => $row['mark_down'],
        ];

        // If Design Master account is currently NULL/empty, fill it from the Inward Party
        if (!empty($partyId) && (is_null($designMaster->account_master_id) || $designMaster->account_master_id === '' || $designMaster->account_master_id == 0)) {
            $updateData['account_master_id'] = $partyId;

            if (!empty($partyName)) {
                $updateData['account_master_name'] = $partyName;
            } else {
                $account = AccountMaster::find($partyId);
                if ($account) {
                    $updateData['account_master_name'] = $account->accountName;
                }
            }
        } elseif (!empty($partyId) && $designMaster->account_master_id == $partyId && empty($designMaster->account_master_name)) {
            if (!empty($partyName)) {
                $updateData['account_master_name'] = $partyName;
            } else {
                $account = AccountMaster::find($partyId);
                if ($account) {
                    $updateData['account_master_name'] = $account->accountName;
                }
            }
        }

        $designMaster->update($updateData);

        return $designMaster;
    }
}