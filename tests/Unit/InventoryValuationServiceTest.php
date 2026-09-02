<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Shop;
use App\Models\Product;
use App\Models\InwardProduct;
use App\Models\DesignMaster;
use App\Models\HsnMaster;
use App\Models\VatTax;
use App\Services\Accounting\InventoryValuationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class InventoryValuationServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected $shop;
    protected $valuationService;
    protected $hsn;
    protected $vat;
    protected $design;

    protected function setUp(): void
    {
        parent::setUp();
        $this->valuationService = app(InventoryValuationService::class);
        $this->shop = Shop::first() ?: Shop::create(['name' => 'Valuation Test Shop', 'email' => 'val@test.com', 'phone' => '123']);

        $this->hsn = HsnMaster::first() ?: HsnMaster::create(['hsn_code' => '6205', 'description' => 'Shirts']);
        $this->vat = VatTax::first() ?: VatTax::create(['name' => 'GST 9%', 'percentage' => 9.00]);
        $this->design = DesignMaster::first() ?: DesignMaster::create([
            'shop_id' => $this->shop->id,
            'name' => 'Design Variant A',
            'code' => 'DSG-001',
            'is_active' => 1,
        ]);
    }

    public function test_calculate_weighted_average_cost_rate()
    {
        $product = Product::create([
            'shop_id' => $this->shop->id,
            'name' => 'Cotton Shirt Variant',
            'price' => 500.00,
            'unit_price' => 500.00,
            'quantity' => 30,
        ]);

        InwardProduct::create([
            'shop_id' => $this->shop->id,
            'design_master_id' => $this->design->id,
            'hsn_master_id' => $this->hsn->id,
            'vat_tax_id' => $this->vat->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'price' => 300.00,
            'mrp' => 500.00,
            'net_purc_rate' => 300.00,
        ]);

        InwardProduct::create([
            'shop_id' => $this->shop->id,
            'design_master_id' => $this->design->id,
            'hsn_master_id' => $this->hsn->id,
            'vat_tax_id' => $this->vat->id,
            'product_id' => $product->id,
            'quantity' => 20,
            'price' => 360.00,
            'mrp' => 500.00,
            'net_purc_rate' => 360.00,
        ]);

        // Weighted Average Cost = (10*300 + 20*360) / 30 = (3000 + 7200) / 30 = 10200 / 30 = 340.00
        $avgCost = $this->valuationService->getWeightedAverageCost($product->id);
        $this->assertEquals(340.00, $avgCost);
    }

    public function test_calculate_shop_inventory_valuation()
    {
        $product = Product::create([
            'shop_id' => $this->shop->id,
            'name' => 'Denim Jeans Variant',
            'price' => 1000.00,
            'quantity' => 10,
        ]);

        InwardProduct::create([
            'shop_id' => $this->shop->id,
            'design_master_id' => $this->design->id,
            'hsn_master_id' => $this->hsn->id,
            'vat_tax_id' => $this->vat->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'price' => 600.00,
            'mrp' => 1000.00,
            'net_purc_rate' => 600.00,
        ]);

        $valuation = $this->valuationService->calculateShopInventoryValuation($this->shop->id);

        $this->assertEquals('Weighted Average Costing (InwardProduct rates)', $valuation['valuation_method']);
        $this->assertGreaterThanOrEqual(600.00, $valuation['total_inventory_asset_value']);
    }
}
