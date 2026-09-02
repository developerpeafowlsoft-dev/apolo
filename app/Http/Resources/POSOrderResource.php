<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class POSOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_code' => $this->order_code,
            'prefix' => $this->prefix,
            'total_amount' => (float)$this->total_amount,
            'coupon_discount' => (float)$this->coupon_discount,
            'tax_amount' => (float)$this->tax_amount,
            'payable_amount' => (float)$this->payable_amount,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'invoice_url' => url('/shop/pos/' . ($this->uuid ?? $this->id) . '/invoice'),
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'customer_name' => $this->customer?->user?->name ?? 'Walk-in Customer',
            'customer_phone' => $this->customer?->user?->phone ?? '9999999999',
            'e_invoice_irn' => $this->e_invoice_irn,
            'e_invoice_ack_no' => $this->e_invoice_ack_no,
            'e_invoice_ack_date' => $this->e_invoice_ack_date ? $this->e_invoice_ack_date->toIso8601String() : null,
        ];
    }
}
