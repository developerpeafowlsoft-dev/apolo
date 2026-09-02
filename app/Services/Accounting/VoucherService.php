<?php

namespace App\Services\Accounting;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

use App\Models\{Voucher,VoucherEntry,VoucherSequence,VoucherAuditLog,AccountBalance};
class VoucherService
{
    public function previewNumber(array $data): array
    {
        $branchContext = app(\App\Services\BranchContext::class);
        $branchId = $branchContext->getCurrentBranchId();
        $branch = $branchContext->getCurrentBranch();

        $seq = VoucherSequence::firstOrCreate(
            [
                'shop_id' => $data['shop_id'],
                'financial_year_id' => $data['financial_year_id'],
                'voucher_type' => $data['voucher_type'],
                'branch_id' => $branchId,
            ],
            [
                'prefix' => $data['seq_prefix'] ?? null,
                'padding' => $data['seq_padding'] ?? 6,
                'current_no' => $branch ? ($branch->voucher_range_start - 1) : 0,
                'range_start' => $branch ? $branch->voucher_range_start : null,
                'range_end' => $branch ? $branch->voucher_range_end : null,
                'reset_policy' => 'yearly',
            ]
        );

        $next = $seq->current_no + 1;
        if ($seq->range_end && $next > $seq->range_end) {
            throw new Exception("Voucher sequence exceeded the range limit of {$seq->range_end} for this branch.");
        }

        $preview = ($seq->prefix ?? '') . str_pad((string)$next, $seq->padding, '0', STR_PAD_LEFT);

        return ['preview' => $preview, 'next' => $next, 'prefix' => $seq->prefix, 'padding' => $seq->padding];
    }

    public function create(array $data): Voucher
    {
        return DB::transaction(function () use ($data) {
            $this->ensureBalanced($data['entries'] ?? []);

            $branchContext = app(\App\Services\BranchContext::class);
            $branchId = $data['branch_id'] ?? $branchContext->getCurrentBranchId();

            if (isset($data['voucher_no'])) {
                $voucherNo = $data['voucher_no'];
                $seq = $this->upsertSequence($data);
                $seqId = $seq->id;
            } else {
                // Numbering (locked inside the same transaction)
                $seq = $this->upsertSequence($data);
                $next = $seq->current_no + 1;

                if ($seq->range_end && $next > $seq->range_end) {
                    throw new Exception("Voucher sequence exceeded the range limit of {$seq->range_end} for this branch.");
                }

                $seq->current_no = $next;
                $seq->save();

                $voucherNo = ($seq->prefix ?? '') . str_pad((string)$next, $seq->padding, '0', STR_PAD_LEFT);
                $seqId = $seq->id;
            }

            // Create voucher
            $voucher = Voucher::create([
                'voucher_no' => $voucherNo,
                'voucher_type' => $data['voucher_type'],
                'status' => 'posted', // we post immediately; extend for "draft" if needed
                'date' => $data['date'],
                'narration' => $data['narration'] ?? null,
                'shop_id' => $data['shop_id'],
                'financial_year_id' => $data['financial_year_id'],
                'sequence_id' => $seqId,
                'branch_id' => $branchId,
                'original_id' => $data['original_id'] ?? null,
            ]);

            // Entries + balances
            foreach ($data['entries'] as $e) {
                VoucherEntry::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $e['account_id'],
                    'type'       => $e['type'], // Dr or Cr
                    'amount'     => round((float)$e['amount'], 2),
                    'description'=> $e['description'] ?? null,
                    'branch_id' => $branchId,
                ]);

                $this->applyToBalance(
                    $e['account_id'],
                    $voucher->shop_id,
                    $voucher->financial_year_id,
                    $e['type'],
                    (float)$e['amount']
                );
            }

            $this->audit($voucher->id, 'created', [
                'voucher_type' => $voucher->voucher_type,
                'total_entries' => count($data['entries']),
            ]);

            return $voucher->load('entries');
        });
    }

    /**
     * Update a voucher: reverse old effect, replace with new entries.
     * Safe only if you allow edits; otherwise prefer explicit reverse+new.
     */
    public function update(int $voucherId, array $data): Voucher
    {
        return DB::transaction(function () use ($voucherId, $data) {
            $voucher = Voucher::with('entries')->lockForUpdate()->findOrFail($voucherId);

            // Reverse previous balances
            foreach ($voucher->entries as $e) {
                $this->applyToBalance(
                    $e->account_id,
                    $voucher->shop_id,
                    $voucher->financial_year_id,
                    $this->inverse($e->type),
                    (float)$e->amount
                );
            }

            // Delete old entries
            VoucherEntry::where('voucher_id', $voucher->id)->delete();

            // Validate new set & re-apply
            $this->ensureBalanced($data['entries'] ?? []);

            // Update voucher header (do not renumber here)
            $voucher->update([
                'voucher_type' => $data['voucher_type'] ?? $voucher->voucher_type,
                'date' => $data['date'] ?? $voucher->date,
                'narration' => $data['narration'] ?? $voucher->narration,
            ]);

            // Insert new entries + apply balances
            foreach ($data['entries'] as $e) {
                VoucherEntry::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $e['account_id'],
                    'type'       => $e['type'],
                    'amount'     => round((float)$e['amount'], 2),
                    'description'=> $e['description'] ?? null,
                ]);

                $this->applyToBalance(
                    $e['account_id'],
                    $voucher->shop_id,
                    $voucher->financial_year_id,
                    $e['type'],
                    (float)$e['amount']
                );
            }

            $this->audit($voucher->id, 'updated', [
                'voucher_type' => $voucher->voucher_type,
                'total_entries' => count($data['entries']),
            ]);

            return $voucher->load('entries');
        });
    }

    /**
     * Reverse a voucher (no hard delete) and undo balances.
     */
    public function reverse(int $voucherId): Voucher
    {
        return DB::transaction(function () use ($voucherId) {
            $voucher = Voucher::with('entries')->lockForUpdate()->findOrFail($voucherId);
            if ($voucher->status === 'reversed') return $voucher;

            foreach ($voucher->entries as $e) {
                $this->applyToBalance(
                    $e->account_id,
                    $voucher->shop_id,
                    $voucher->financial_year_id,
                    $this->inverse($e->type),
                    (float)$e->amount
                );
            }

            $voucher->status = 'reversed';
            $voucher->save();

            $this->audit($voucher->id, 'reversed', [
                'voucher_no' => $voucher->voucher_no,
            ]);

            return $voucher;
        });
    }

    /**
     * (Optional) Hard delete a reversed voucher only.
     */
    public function destroy(int $voucherId): void
    {
        DB::transaction(function () use ($voucherId) {
            $voucher = Voucher::lockForUpdate()->findOrFail($voucherId);
            if ($voucher->status !== 'reversed') {
                throw new Exception('Delete allowed only for reversed vouchers.');
            }
            VoucherEntry::where('voucher_id', $voucher->id)->delete();
            $voucher->delete();

            $this->audit($voucherId, 'deleted', null);
        });
    }

    /* ===================== Helpers (business rules) ===================== */

    protected function ensureBalanced(array $entries): void
    {
        if (count($entries) < 2) {
            throw new Exception('At least two entries (Dr & Cr) are required.');
        }
        $dr = 0.0; $cr = 0.0;
        foreach ($entries as $e) {
            if (!in_array($e['type'], ['Dr','Cr'], true)) {
                throw new Exception('Entry type must be Dr or Cr.');
            }
            $amt = round((float)($e['amount'] ?? 0), 2);
            if ($amt <= 0) throw new Exception('Entry amount must be > 0.');
            if ($e['type'] === 'Dr') $dr += $amt; else $cr += $amt;
        }
        if (round($dr,2) !== round($cr,2)) {
            throw new Exception('Debit and Credit totals do not match.');
        }
    }

    protected function upsertSequence(array $data): VoucherSequence
    {
        $branchContext = app(\App\Services\BranchContext::class);
        $branchId = $branchContext->getCurrentBranchId();
        $branch = $branchContext->getCurrentBranch();

        $seq = VoucherSequence::firstOrCreate(
            [
                'shop_id' => $data['shop_id'],
                'financial_year_id' => $data['financial_year_id'],
                'voucher_type' => $data['voucher_type'],
                'branch_id' => $branchId,
            ],
            [
                'prefix' => $data['seq_prefix'] ?? null,   // e.g. BR01-FY2526-PAY-
                'padding' => $data['seq_padding'] ?? 6,    // digits
                'current_no' => $branch ? ($branch->voucher_range_start - 1) : 0,
                'range_start' => $branch ? $branch->voucher_range_start : null,
                'range_end' => $branch ? $branch->voucher_range_end : null,
                'reset_policy' => 'yearly',
            ]
        );

        // Allow override of prefix/padding per call (optional)
        if (!empty($data['seq_prefix']))  $seq->prefix  = $data['seq_prefix'];
        if (!empty($data['seq_padding'])) $seq->padding = (int)$data['seq_padding'];

        return $seq;
    }

    protected function applyToBalance(int $accountId, int $branchId, int $fyId, string $type, float $amount): void
    {
        $bal = AccountBalance::firstOrCreate(
            [
                'account_id' => $accountId,
                'shop_id' => $branchId,
                'financial_year_id' => $fyId,
            ],
            [
                'opening_balance' => 0,
                'closing_balance' => 0,
            ]
        );

        if ($type === 'Dr') {
            $bal->closing_balance = $bal->closing_balance + $amount;
        } else {
            $bal->closing_balance = $bal->closing_balance - $amount;
        }
        $bal->save();
    }

    protected function inverse(string $type): string
    {
        return $type === 'Dr' ? 'Cr' : 'Dr';
    }

    protected function audit(int $voucherId, string $action, ?array $meta): void
    {
        if (!class_exists(VoucherAuditLog::class)) return; // optional table
        VoucherAuditLog::create([
            'voucher_id' => $voucherId,
            'user_id' => Auth::id(),
            'action' => $action,
            'meta' => $meta,
        ]);
    }
}