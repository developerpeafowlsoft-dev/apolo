<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Shop;
use App\Models\InwardInvoice;
use App\Services\Accounting\Gstr2bReconciliationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class Gstr2bReconciliationTest extends TestCase
{
    use DatabaseTransactions;

    protected $shop;
    protected $gstr2bService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gstr2bService = app(Gstr2bReconciliationService::class);
        $this->shop = Shop::first() ?: Shop::create(['name' => 'GSTR2B Test Shop', 'email' => 'gstr2b@test.com', 'phone' => '123']);
    }

    public function test_reconcile_gstr2b_matched_and_missing_categories()
    {
        InwardInvoice::create([
            'shop_id' => $this->shop->id,
            'inward_voucher_no' => 'INV-RECON-001',
            'inward_challan_no' => 'INV-RECON-001',
            'inward_date' => now()->toDateString(),
            'inward_day_name' => 'Friday',
            'inward_time' => now()->toTimeString(),
            'inward_acc_lr_no' => 'LR-001',
            'inward_acc_lr_date' => now()->toDateString(),
            'inward_acc_remark' => 'Recon test',
            'inward_total' => 1000.00,
            'inward_acc_net_amount' => 1000.00,
            'total_amount' => 1000.00,
        ]);

        $mockPayload = [
            'b2b' => [
                [
                    'gstin' => '24AAACV1234F1Z9',
                    'inv' => [
                        [
                            'inum' => 'INV-RECON-001',
                            'dt' => now()->toDateString(),
                            'val' => 1000.00,
                        ],
                        [
                            'inum' => 'INV-MISSING-002',
                            'dt' => now()->toDateString(),
                            'val' => 500.00,
                        ]
                    ]
                ]
            ]
        ];

        $result = $this->gstr2bService->reconcileGstr2b($this->shop->id, $mockPayload);

        $this->assertEquals(1, $result['summary']['total_matched']);
        $this->assertEquals(1, $result['summary']['total_missing_in_books']);
    }
}
