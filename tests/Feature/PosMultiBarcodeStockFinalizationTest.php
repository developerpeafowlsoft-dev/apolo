<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Shop;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\InwardProduct;
use App\Models\InwardInvoice;
use App\Models\FinancialYear;
use App\Models\DesignMaster;
use App\Models\HsnMaster;
use App\Models\VatTax;
use App\Models\CounterMaster;
use App\Models\PosShift;
use App\Services\POS\PosOrderFinalizationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PosMultiBarcodeStockFinalizationTest extends TestCase
{
    use DatabaseTransactions;

    protected $shop;
    protected $user;
    protected $counter;
    protected $shift;
    protected $product;
    protected $barcode1;
    protected $barcode2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shop = Shop::first() ?: Shop::create(['name' => 'Stock Test Shop', 'email' => 'stock@test.com', 'phone' => '123', 'prefix' => 'STK']);
        $this->user = User::first() ?: User::create(['name' => 'Cashier User', 'email' => 'cashier@test.com', 'password' => bcrypt('password')]);

        $this->counter = CounterMaster::first() ?: CounterMaster::create([
            'shop_id' => $this->shop->id,
            'counter_name' => 'Counter 1',
            'voucher_prefix' => 'STK',
            'is_active' => 1,
        ]);

        $this->shift = PosShift::create([
            'shop_id' => $this->shop->id,
            'user_id' => $this->user->id,
            'counter_id' => $this->counter->id,
            'opening_cash' => 1000.00,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        $hsn = HsnMaster::first() ?: HsnMaster::create(['hsn_code' => '6205', 'description' => 'Shirts']);
        $vat = VatTax::first() ?: VatTax::create(['name' => 'GST 5%', 'percentage' => 5.00]);
        $design = DesignMaster::first() ?: DesignMaster::create([
            'shop_id' => $this->shop->id,
            'name' => 'Crocks Design',
            'code' => 'CM-596004',
            'is_active' => 1,
        ]);

        $inwardInvoice = InwardInvoice::first() ?: InwardInvoice::create([
            'shop_id' => $this->shop->id,
            'invoice_no' => 'INV-001',
            'date' => now()->toDateString(),
            'total_amount' => 1000.00,
        ]);

        $this->product = Product::create([
            'shop_id' => $this->shop->id,
            'name' => 'Crocks Mens Test',
            'price' => 199.00,
            'quantity' => 2,
        ]);

        $inward = InwardProduct::create([
            'shop_id' => $this->shop->id,
            'design_master_id' => $design->id,
            'hsn_master_id' => $hsn->id,
            'vat_tax_id' => $vat->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'price' => 199.00,
            'mrp' => 199.00,
            'net_purc_rate' => 120.00,
        ]);

        $this->barcode1 = ProductBarcode::create([
            'shop_id' => $this->shop->id,
            'inward_invoice_id' => $inwardInvoice->id,
            'product_id' => $this->product->id,
            'inward_product_id' => $inward->id,
            'barcode_number' => 'TEST0000000993',
            'mrp' => 199.00,
            'is_sold' => 0,
        ]);

        $this->barcode2 = ProductBarcode::create([
            'shop_id' => $this->shop->id,
            'inward_invoice_id' => $inwardInvoice->id,
            'product_id' => $this->product->id,
            'inward_product_id' => $inward->id,
            'barcode_number' => 'TEST0000000994',
            'mrp' => 199.00,
            'is_sold' => 0,
        ]);
    }

    public function test_finalize_order_with_multiple_barcodes_of_same_product_succeeds()
    {
        $finalizationService = app(PosOrderFinalizationService::class);
        $this->actingAs($this->user);

        $payload = [
            'counter_id' => $this->counter->id,
            'payment_type' => 'Cash',
            'payment_method' => 'Cash Payment',
            'customer_phone' => '9999999999',
            'customer_name' => 'Walk-in Customer',
            'cash_received' => 500.00,
            'items' => [
                [
                    'barcode' => 'TEST0000000993',
                    'qty' => 1,
                    'rate' => 199.00,
                    'disc_percent' => 0,
                ],
                [
                    'barcode' => 'TEST0000000994',
                    'qty' => 1,
                    'rate' => 199.00,
                    'disc_percent' => 0,
                ]
            ]
        ];

        $financialYear = FinancialYear::first() ?: FinancialYear::create(['year' => '2026-2027', 'start_date' => '2026-04-01', 'end_date' => '2027-03-31', 'is_active' => 1]);
        $order = $finalizationService->finalize($payload, $this->shop, $financialYear);

        $this->assertNotNull($order);
        $this->assertEquals(398.00, (float)$order->total_amount);

        // Verify both barcodes are marked as sold
        $this->assertEquals(1, ProductBarcode::find($this->barcode1->id)->is_sold);
        $this->assertEquals(1, ProductBarcode::find($this->barcode2->id)->is_sold);

        // Verify product stock is updated
        $this->product->refresh();
        $this->assertEquals(0, $this->product->available_stock);
    }
}
