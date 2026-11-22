<?php

namespace Paymenter\Extensions\Gateways\MultiBankQR\Models;

use Illuminate\Database\Eloquent\Model;

class MultiBankTransaction extends Model
{
    protected $table = 'multibank_transactions';

    // Status lifecycle: Pending Payment -> Pending Verification -> Verified
    public const STATUS_PENDING_PAYMENT      = 'pending_payment';
    public const STATUS_PENDING_VERIFICATION = 'pending_verification';
    public const STATUS_VERIFIED             = 'verified';
    public const STATUS_DECLINED             = 'declined';
    public const STATUS_FLAGGED              = 'flagged';

    // Backward-compatibility aliases
    public const STATUS_PENDING  = 'pending_verification';
    public const STATUS_APPROVED = 'verified';

    protected $fillable = [
        'invoice_id',
        'user_id',
        'bank_account_name',
        'amount',
        'currency',
        'transaction_id',
        'sender_name',
        'sender_account',
        'proof_image',
        'customer_notes',
        'status',
        'admin_notes',
        'decline_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'reviewed_at' => 'datetime',
    ];

    public function isPendingPayment(): bool
    {
        return $this->status === self::STATUS_PENDING_PAYMENT;
    }

    public function isPendingVerification(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING_VERIFICATION, 'pending', 'under_review']);
    }

    public function isPending(): bool
    {
        return $this->isPendingVerification();
    }

    public function isVerified(): bool
    {
        return in_array($this->status, [self::STATUS_VERIFIED, 'approved', 'paid']);
    }

    public function isApproved(): bool
    {
        return $this->isVerified();
    }

    public function isDeclined(): bool
    {
        return $this->status === self::STATUS_DECLINED;
    }

    public function isFlagged(): bool
    {
        return $this->status === self::STATUS_FLAGGED;
    }

    /**
     * Exact lifecycle status label.
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_PAYMENT      => 'Pending Payment',
            self::STATUS_PENDING_VERIFICATION => 'Pending Verification',
            'pending'                         => 'Pending Verification',
            self::STATUS_VERIFIED             => 'Verified',
            'approved'                        => 'Verified',
            self::STATUS_DECLINED             => 'Payment Declined',
            self::STATUS_FLAGGED              => 'Action Required / Under Review',
            default                           => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    /**
     * Status badge Tailwind CSS classes.
     */
    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_PAYMENT      => 'bg-slate-100 text-slate-800 border-slate-300 dark:bg-slate-800 dark:text-slate-200',
            self::STATUS_PENDING_VERIFICATION => 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-900/40 dark:text-amber-300',
            'pending'                         => 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-900/40 dark:text-amber-300',
            self::STATUS_VERIFIED             => 'bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-900/40 dark:text-emerald-300',
            'approved'                        => 'bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-900/40 dark:text-emerald-300',
            self::STATUS_DECLINED             => 'bg-rose-100 text-rose-800 border-rose-300 dark:bg-rose-900/40 dark:text-rose-300',
            self::STATUS_FLAGGED              => 'bg-sky-100 text-sky-800 border-sky-300 dark:bg-sky-900/40 dark:text-sky-300',
            default                           => 'bg-gray-100 text-gray-800 border-gray-300',
        };
    }

    /**
     * Relationship to Paymenter Invoice model if available.
     */
    public function invoice()
    {
        $invoiceModel = '\App\Models\Invoice';
        if (class_exists($invoiceModel)) {
            return $this->belongsTo($invoiceModel, 'invoice_id');
        }

        return null;
    }

    /**
     * Relationship to User model if available.
     */
    public function user()
    {
        $userModel = '\App\Models\User';
        if (class_exists($userModel)) {
            return $this->belongsTo($userModel, 'user_id');
        }

        return null;
    }
}
