<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Shop;
use App\Models\Account;
use App\Models\AccountMaster;
use App\Models\AccountBalance;
use App\Models\FinancialYear;
use App\Models\User;
use App\Models\CounterMaster;
use App\Models\PosShift;
use App\Services\Accounting\BankReconciliationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BankReconciliationServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected $shop;
    protected $brsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->brsService = app(BankReconciliationService::class);
        $user = User::first() ?: User::create(['name' => 'BRS User', 'email' => 'brsuser@test.com', 'password' => bcrypt('password')]);
        $this->shop = Shop::first() ?: Shop::create(['name' => 'BRS Test Shop', 'user_id' => $user->id, 'email' => 'brs@test.com', 'phone' => '123']);
    }

    public function test_generate_brs_statement_and_reconcile_shift_drawer()
    {
        $sysAccount = Account::first() ?: Account::create(['code' => 'ACC-BANK-01', 'name' => 'Bank Account Ledger']);
        $finYear = FinancialYear::first() ?: FinancialYear::create(['year' => '2026-2027', 'start_date' => '2026-04-01', 'end_date' => '2027-03-31', 'is_active' => 1]);

        $account = AccountMaster::first() ?: AccountMaster::create([
            'shop_id' => $this->shop->id,
            'account_id' => $sysAccount->id,
            'accountName' => 'HDFC Bank Account',
            'accountshortcode' => 'HDFC',
            'cities_id' => 1,
            'contperson' => 'Bank Manager',
            'contpincode' => '380001',
            'contaddress' => 'AHMEDABAD',
            'country_id' => 1,
            'state_id' => 24,
            'city_id' => 1,
        ]);

        AccountBalance::create([
            'shop_id' => $this->shop->id,
            'financial_year_id' => $finYear->id,
            'account_id' => $account->id,
            'opening_balance' => 50000.00,
        ]);

        $brs = $this->brsService->generateBrs($this->shop->id, $account->id, 50000.00);
        $this->assertEquals(50000.00, $brs['balance_as_per_cash_book']);
        $this->assertTrue($brs['is_fully_reconciled']);

        $user = User::first() ?: User::create(['name' => 'Shift User', 'email' => 'shift@test.com', 'password' => bcrypt('password')]);
        $counter = CounterMaster::first() ?: CounterMaster::create(['shop_id' => $this->shop->id, 'counter_name' => 'C1']);

        $shift = PosShift::create([
            'shop_id' => $this->shop->id,
            'user_id' => $user->id,
            'counter_id' => $counter->id,
            'opening_cash' => 1000.00,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        $shiftRecon = $this->brsService->reconcileShiftDrawer($shift->id, 1000.00);
        $this->assertEquals(1000.00, $shiftRecon['expected_cash']);
        $this->assertEquals('EXACT', $shiftRecon['status']);
    }
}
