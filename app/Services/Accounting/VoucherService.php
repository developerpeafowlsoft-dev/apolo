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
                'reverses_voucher_id' => $data['reverses_voucher_id'] ?? null,
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

            if ($voucher->status === 'reversed') {
                throw new Exception('A reversed voucher cannot be edited. Post a new voucher instead.');
            }

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
     * Reverse a voucher by posting a mirrored contra voucher.
     *
     * The previous implementation adjusted account_balances directly and flipped
     * a status flag, leaving the original entries in place. Because no report
     * filters on status - 43 aggregation sites across 5 services - a reversed
     * voucher vanished from balances but still counted in the trial balance, P&L
     * and balance sheet.
     *
     * A contra voucher nets the original to zero in anything that sums entries,
     * needs no report changes, and leaves both sides visible for audit. This is
     * also how reversal is normally done in double-entry practice: you never
     * un-post an entry, you post its opposite.
     *
     * Returns the CONTRA voucher, not the original.
     */
    public function reverse(int $voucherId, ?string $date = null, ?string $narration = null): Voucher
    {
        return DB::transaction(function () use ($voucherId, $date, $narration) {
            $original = Voucher::with('entries')->lockForUpdate()->findOrFail($voucherId);

            if ($original->status === 'reversed') {
                $existing = Voucher::where('reverses_voucher_id', $original->id)->first();
                if ($existing) {
                    return $existing;   // idempotent
                }
                throw new Exception(
                    "Voucher {$original->voucher_no} is marked reversed but has no contra voucher. "
                    . 'It was reversed under the old balance-adjusting scheme; correct it manually.'
                );
            }

            if ($original->entries->isEmpty()) {
                throw new Exception("Voucher {$original->voucher_no} has no entries to reverse.");
            }

            $entries = [];
            foreach ($original->entries as $e) {
                $entries[] = [
                    'account_id' => $e->account_id,
                    'type' => $this->inverse($e->type),
                    'amount' => (float)$e->amount,
                    'description' => trim("Reversal of {$original->voucher_no}" . ($e->description ? " - {$e->description}" : '')),
                ];
            }

            // Same voucher_type, so the contra takes the next number in the normal
            // series. Deliberately no seq_prefix override: upsertSequence() keys on
            // type rather than prefix, so passing one would rewrite the prefix for
            // every future voucher of this type.
            $contra = $this->create([
                'voucher_type' => $original->voucher_type,
                'date' => $date ?? now()->toDateString(),
                'narration' => $narration ?? "Reversal of {$original->voucher_no}",
                'shop_id' => $original->shop_id,
                'financial_year_id' => $original->financial_year_id,
                'branch_id' => $original->branch_id,
                'reverses_voucher_id' => $original->id,
                'entries' => $entries,
            ]);

            $original->status = 'reversed';
            $original->save();

            $this->audit($original->id, 'reversed', [
                'voucher_no' => $original->voucher_no,
                'contra_voucher_id' => $contra->id,
                'contra_voucher_no' => $contra->voucher_no,
            ]);

            return $contra;
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

            // Deleting the original would leave its contra dangling, and the contra
            // still carries live entries - the books would go out by that amount.
            $contra = Voucher::where('reverses_voucher_id', $voucher->id)->first();
            if ($contra) {
                throw new Exception(
                    "Cannot delete {$voucher->voucher_no}: contra voucher {$contra->voucher_no} reverses it. "
                    . 'Delete the contra first, or leave both for audit.'
                );
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
        // Compare with a tolerance rather than strict float equality: two sums that
        // both represent the same rupee value can land on adjacent doubles, which
        // would reject a perfectly valid voucher.
        if (abs($dr - $cr) > 0.005) {
            throw new Exception(sprintf(
                'Debit and Credit totals do not match (Dr %.2f vs Cr %.2f).', $dr, $cr
            ));
        }
    }

    protected function upsertSequence(array $data): VoucherSequence
    {
        $branchContext = app(\App\Services\BranchContext::class);
        $branchId = $branchContext->getCurrentBranchId();
        $branch = $branchContext->getCurrentBranch();

        $key = [
            'shop_id' => $data['shop_id'],
            'financial_year_id' => $data['financial_year_id'],
            'voucher_type' => $data['voucher_type'],
            'branch_id' => $branchId,
        ];

        VoucherSequence::firstOrCreate($key, [
            'prefix' => $data['seq_prefix'] ?? null,   // e.g. BR01-FY2526-PAY-
            'padding' => $data['seq_padding'] ?? 6,    // digits
            'current_no' => $branch ? ($branch->voucher_range_start - 1) : 0,
            'range_start' => $branch ? $branch->voucher_range_start : null,
            'range_end' => $branch ? $branch->voucher_range_end : null,
            'reset_policy' => 'yearly',
        ]);

        // Re-read under a row lock. Without this two tills billing at the same
        // instant both read the same current_no, both claim the next number, and
        // the second hits the unique index on vouchers.voucher_no - a failed sale
        // at the counter. The caller always runs inside a transaction.
        $seq = VoucherSequence::where($key)->lockForUpdate()->first();

        // Allow override of prefix/padding per call (optional)
        if (!empty($data['seq_prefix']))  $seq->prefix  = $data['seq_prefix'];
        if (!empty($data['seq_padding'])) $seq->padding = (int)$data['seq_padding'];

        return $seq;
    }

    protected function applyToBalance(int $accountId, int $shopId, int $fyId, string $type, float $amount): void
    {
        $bal = AccountBalance::firstOrCreate(
            [
                'account_id' => $accountId,
                'shop_id' => $shopId,
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