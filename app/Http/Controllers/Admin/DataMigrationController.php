<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Migration\LegacyDataMigrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class DataMigrationController extends Controller
{
    protected LegacyDataMigrationService $migrationService;

    public function __construct(LegacyDataMigrationService $migrationService)
    {
        $this->migrationService = $migrationService;
    }

    public function index(Request $request)
    {
        $availableYears = $this->migrationService->getAvailableYears();
        $selectedYear = $request->input('year');
        if ($selectedYear === null) {
            $selectedYear = in_array('2016', $availableYears) ? '2016' : (!empty($availableYears) ? $availableYears[0] : null);
        }
        if (!empty($selectedYear)) {
            $this->migrationService->setSelectedYear($selectedYear);
        }

        $shops = DB::table('shops')->select('id', 'name')->get();
        $defaultShopId = 14;
        $filesStatus = $this->migrationService->getAllFilesStatus($defaultShopId);

        $totalRows = array_sum(array_column($filesStatus, 'excel_data_rows'));
        $totalDbRecords = array_sum(array_column($filesStatus, 'db_records'));

        $barcodeReconciliation = $this->migrationService->getBarcodeReconciliation($selectedYear, $defaultShopId);

        $totalDr = (float)DB::table('voucher_entries')->join('vouchers', 'voucher_entries.voucher_id', '=', 'vouchers.id')->where('vouchers.shop_id', $defaultShopId)->where('voucher_entries.type', 'Dr')->sum('voucher_entries.amount');
        $totalCr = (float)DB::table('voucher_entries')->join('vouchers', 'voucher_entries.voucher_id', '=', 'vouchers.id')->where('vouchers.shop_id', $defaultShopId)->where('voucher_entries.type', 'Cr')->sum('voucher_entries.amount');
        $diff = round(abs($totalDr - $totalCr), 2);

        $accountAudit = [
            'total_vouchers' => DB::table('vouchers')->where('shop_id', $defaultShopId)->count(),
            'total_balances' => DB::table('account_balances')->where('shop_id', $defaultShopId)->count(),
            'total_dr' => $totalDr,
            'total_cr' => $totalCr,
            'diff' => $diff,
            'is_balanced' => $diff <= 0.01,
        ];

        return view('admin.data-migration.index', compact(
            'filesStatus',
            'shops',
            'defaultShopId',
            'totalRows',
            'totalDbRecords',
            'availableYears',
            'selectedYear',
            'barcodeReconciliation',
            'accountAudit'
        ));
    }


    public function runStep(Request $request)
    {
        $request->validate([
            'step' => 'required|integer|min:1|max:15',
            'shop_id' => 'nullable|integer',
            'dry_run' => 'nullable|boolean',
            'year' => 'nullable|string'
        ]);

        $step = (int)$request->input('step');
        $shopId = (int)($request->input('shop_id') ?: 14);
        $dryRun = (bool)$request->input('dry_run', false);
        $year = $request->input('year');

        if (!empty($year)) {
            $this->migrationService->setSelectedYear($year);
        }

        try {
            $result = $this->migrationService->runStep($step, $dryRun, $shopId);
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function clearStep(Request $request)
    {
        $request->validate([
            'step' => 'required|integer|min:1|max:15',
            'shop_id' => 'nullable|integer'
        ]);

        $step = (int)$request->input('step');
        $shopId = (int)($request->input('shop_id') ?: 14);

        try {
            $result = $this->migrationService->clearStepData($step, $shopId);
            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => "Successfully cleared {$result['deleted_count']} records from table `{$result['table']}`."
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:51200', // max 50MB
            'file_name' => 'required|string',
            'year' => 'nullable|string'
        ]);

        $uploadedFile = $request->file('file');
        $targetFileName = $request->input('file_name');
        $year = $request->input('year');

        $backupDir = $this->migrationService->getBackupDir();
        if (!empty($year) && $year !== 'all') {
            $backupDir = rtrim($backupDir, '/') . '/' . $year . '/';
        }

        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $uploadedFile->move($backupDir, $targetFileName);

        return response()->json([
            'success' => true,
            'message' => "File {$targetFileName} uploaded successfully to " . basename($backupDir) . "."
        ]);
    }

    public function checkPageHealth(Request $request)
    {
        $request->validate([
            'step' => 'required|integer|min:1|max:15',
            'shop_id' => 'nullable|integer'
        ]);

        $step = (int)$request->input('step');
        $shopId = (int)($request->input('shop_id') ?: 14);

        try {
            $health = $this->migrationService->checkStepPageHealth($step, $shopId);
            return response()->json([
                'success' => true,
                'data' => $health
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function reconciliation(Request $request)
    {
        $year = $request->input('year');
        $shopId = (int)($request->input('shop_id') ?: 14);

        try {
            $data = $this->migrationService->getBarcodeReconciliation($year, $shopId);
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function correctAccounts(Request $request)
    {
        $shopId = (int)($request->input('shop_id') ?: 14);
        $year = $request->input('year');
        $dryRun = (bool)$request->input('dry_run', false);

        try {
            $result = $this->migrationService->autoCorrectAccounts($shopId, $year, $dryRun);
            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => $dryRun
                    ? 'Preview only - nothing was written.'
                    : 'All accounts, vouchers, and ledger balances have been automatically corrected and calibrated.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function blankAccounts(Request $request)
    {
        $shopId = (int)($request->input('shop_id') ?: 14);
        $clearVouchers = (bool)$request->input('clear_vouchers', true);

        try {
            $result = $this->migrationService->blankAllAccounts($shopId, $clearVouchers);
            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'All accounts have been set to blank with credit and debit zero.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

