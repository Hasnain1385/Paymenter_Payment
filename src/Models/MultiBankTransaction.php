<?php

namespace Paymenter\Extensions\Gateways\MultiBankQR\Models;

use Illuminate\Database\Eloquent\Model;

class MultiBankTransaction extends Model
{
    protected $table = 'multibank_transactions';

    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_DECLINED = 'declined';
    public const STATUS_FLAGGED  = 'flagged';

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

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
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
     * Human-friendly status label.
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING  => 'Awaiting Admin Verification',
            self::STATUS_APPROVED => 'Payment Approved & Verified',
            self::STATUS_DECLINED => 'Payment Declined',
            self::STATUS_FLAGGED  => 'Action Required / Under Review',
            default               => ucfirst($this->status),
        };
    }

    /**
     * Status badge Tailwind CSS classes.
     */
    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING  => 'bg-amber-100 text-amber-800 border-amber-300',
            self::STATUS_APPROVED => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            self::STATUS_DECLINED => 'bg-rose-100 text-rose-800 border-rose-300',
            self::STATUS_FLAGGED  => 'bg-sky-100 text-sky-800 border-sky-300',
            default               => 'bg-gray-100 text-gray-800 border-gray-300',
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
