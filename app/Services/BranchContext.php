<?php

namespace App\Services;

use App\Models\Branch;

class BranchContext
{
    protected string $mode;
    protected ?string $code;
    protected ?Branch $branchModel = null;
    protected bool $resolved = false;

    public function __construct(array $config)
    {
        $this->mode = $config['mode'] ?? 'central';
        $this->code = $config['code'] ?? null;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function getBranchCode(): ?string
    {
        return $this->code;
    }

    public function isCentral(): bool
    {
        return $this->mode === 'central';
    }

    public function isBranch(): bool
    {
        return $this->mode === 'branch';
    }

    public function getCurrentBranch(): ?Branch
    {
        if ($this->isCentral() || !$this->code) {
            return null;
        }

        if (!$this->resolved) {
            $this->branchModel = Branch::where('branch_code', $this->code)->first();
            $this->resolved = true;
        }

        return $this->branchModel;
    }

    public function getCurrentBranchId(): ?int
    {
        $branch = $this->getCurrentBranch();
        return $branch ? (int)$branch->id : null;
    }

    public function nextVoucherRangeStart(): int
    {
        $maxEnd = Branch::max('voucher_range_end');
        if ($maxEnd) {
            return (int)($maxEnd + 1);
        }
        return 500000;
    }
}
