<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Branch;

class RegisterBranch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'branch:register {code} {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register a new physical store branch and allocate its non-overlapping voucher ranges.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $code = $this->argument('code');
        $name = $this->argument('name');

        if (Branch::where('branch_code', $code)->exists()) {
            $this->error("Branch with code '{$code}' is already registered.");
            return 1;
        }

        $start = app(\App\Services\BranchContext::class)->nextVoucherRangeStart();
        $end = $start + 100000 - 1;
        $billPrefix = strtoupper($code) . '-';

        $branch = Branch::create([
            'branch_code' => $code,
            'name' => $name,
            'voucher_range_start' => $start,
            'voucher_range_end' => $end,
            'bill_prefix' => $billPrefix,
            'is_active' => true,
        ]);

        $this->info("Branch '{$name}' registered successfully!");
        $this->line("Code: {$branch->branch_code}");
        $this->line("Bill Prefix: {$branch->bill_prefix}");
        $this->line("Voucher Range Start: {$branch->voucher_range_start}");
        $this->line("Voucher Range End: {$branch->voucher_range_end}");

        return 0;
    }
}
