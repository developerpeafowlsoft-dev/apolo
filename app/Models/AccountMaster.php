<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountMaster extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'accountName',
        'account_id',
        'cities_id',
        'accountshortcode',
        'agentcomm',
        'agentcommremark',
        'referenceby',

        'contperson',
        'contpincode',
        'contaddress',
        'country_id',
        'state_id',
        'city_id',

        'cont_info_mobile1',
        'cont_info_mobile2',
        'cont_info_phone',
        'cont_info_send_sms',
        'cont_info_dndactivate',
        'cont_info_email',
        'cont_info_website_url',
        'cont_info_birth_date',

        'tax_info_gst_no',
        'tax_info_gst_reg_date',
        'tax_info_gst_cancel_date',
        'vat_tax_id',
        'tax_info_tan_no',
        'tax_info_pan_no',
        'tax_info_tds_deduct',
        'tax_info_tcs_deduct',

        'bank_info_bank_name',
        'bank_info_ac_no',
        'bank_info_swift_code',
        'bank_info_ifsc_code',
        'bank_info_branch',
        'bank_info_upi_id',
        'bank_info_payment_name',
        'bank_info_address',

        'other_info_discount',
        'other_info_discount_limit',
        'other_info_cash_disc',
        'other_info_special_disc',
        'other_info_bank_cs_disc',
        'other_info_credit_day',
        'other_info_act_limit',
        'other_info_adjustment_type',
        'other_info_delivery_type',

        'is_active',
        'is_party_code',
    ];

    protected $casts = [
        'cont_info_send_sms' => 'boolean',
        'cont_info_dndactivate' => 'boolean',
        'is_party_code' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopePartyCode($query)
    {
        return $query->where('is_party_code', 1);
    }

    public function scopeBasicFields($query)
    {
        return $query->select([
            'id',
            'shop_id',
            'accountName',
            'accountshortcode',
            'account_id',
            'city_id',
            'contperson',
            'cont_info_mobile1',
            'bank_info_bank_name',
            'is_active',
            'is_party_code',
        ]);
    }


    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function regionArea()
    {
        return $this->belongsTo(City::class,'cities_id','id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
    public function state()
    {
        return $this->belongsTo(State::class);
    }
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function vattax()
    {
        return $this->belongsTo(VatTax::class,'vat_tax_id','id');
    }

}