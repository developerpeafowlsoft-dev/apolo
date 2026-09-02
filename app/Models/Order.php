<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Scopes\PosOrderFalse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'order_status' => OrderStatus::class,
        'payment_method' => PaymentMethod::class,
        'payment_status' => PaymentStatus::class,
        'e_invoice_ack_date' => 'datetime',
        'billing_started_at' => 'datetime',
        'shiprocket_tracking_data' => 'array',
    ];

    /**
     * Get all of the products for the Order.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_products')
            ->withPivot([
                'barcode_number',
                'quantity',
                'color',
                'size',
                'unit',
                'salesman_id',

                'price',
                'mrp',
                'discount',
                'discount_amount',

                'tax_percentage',
                'tax_amount',
                'vat_tax_name',

                'inward_invoice_id',
                'inward_product_id',
            ]);
    }

    /**
     * Get all of the vat taxes for the Order.
     */
    public function vatTaxes(): HasMany
    {
        return $this->hasMany(OrderVatTax::class, 'order_id');
    }

    /**
     * Get the customer that owns the Order.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Get the shop for the Order.
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    /**
     * Get the coupon for the Order.
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class, 'coupon_id')->withTrashed();
    }

    /**
     * Get the address for the Order.
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    /**
     * Get the payments for the Order.
     */
    public function payments(): BelongsToMany
    {
        return $this->belongsToMany(Payment::class, 'order_payments');
    }

    /**
     * Get the driver order for the Order.
     */
    public function driverOrder(): BelongsTo
    {
        return $this->belongsTo(DriverOrder::class, 'id', 'order_id');
    }

    /**
     * Get the counter position for the Order.
     */
    public function counter(): BelongsTo
    {
        return $this->belongsTo(CounterMaster::class, 'counter_id');
    }

    /**
     * Get the branch for the Order.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function posReturns(): HasMany
    {
        return $this->hasMany(POSReturn::class, 'original_order_id');
    }

    /**
     * Get the salesman (floor attendant) who sold this invoice.
     */
    public function salesman(): BelongsTo
    {
        return $this->belongsTo(Salesman::class, 'salesman_id');
    }

    /**
     * Get the cashier (employee/user) who collected the payment for this invoice.
     */
    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    /**
     * Format billing duration in human readable time (e.g. '45s', '1m 20s').
     */
    public function getBillingSpeedFormattedAttribute(): string
    {
        $sec = (int)($this->billing_duration_seconds ?? 0);
        if ($sec <= 0) {
            return 'N/A';
        }
        if ($sec < 60) {
            return "{$sec}s";
        }
        $mins = floor($sec / 60);
        $remSec = $sec % 60;
        return $remSec > 0 ? "{$mins}m {$remSec}s" : "{$mins}m";
    }

    /**
     * Get performance speed badge info.
     */
    public function getBillingPerformanceBadgeAttribute(): array
    {
        $sec = (int)($this->billing_duration_seconds ?? 0);
        if ($sec <= 0) {
            return ['label' => 'N/A', 'class' => 'badge-secondary', 'icon' => 'fa-clock'];
        }
        if ($sec <= 45) {
            return ['label' => 'Super Fast (⚡ ' . $this->billing_speed_formatted . ')', 'class' => 'bg-success text-white', 'icon' => 'fa-bolt'];
        }
        if ($sec <= 120) {
            return ['label' => 'Normal (' . $this->billing_speed_formatted . ')', 'class' => 'bg-info text-white', 'icon' => 'fa-gauge-high'];
        }
        return ['label' => 'Slow (' . $this->billing_speed_formatted . ')', 'class' => 'bg-warning text-dark', 'icon' => 'fa-hourglass-half'];
    }

    /**
     * apply global scope
     */
    protected static function booted()
    {
        static::addGlobalScope(new PosOrderFalse);
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function () {
            self::clearOrderCache();
        });

        static::updated(function () {
            self::clearOrderCache();
        });

        static::deleted(function () {
            self::clearOrderCache();
        });
    }

    protected static function clearOrderCache()
    {
        $cacheKeys = [
            'admin_all_orders',
            'shop_all_orders',
        ];

        foreach (OrderStatus::cases() as $status) {
            $cacheKeys[] = 'admin_status_'.Str::camel($status->value);
            $cacheKeys[] = 'shop_status_'.Str::camel($status->value);
        }

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }
}
