<?php

namespace Paymenter\Extensions\Gateways\MultiBankQR\Models;

use Illuminate\Database\Eloquent\Model;

class MultiBankAccount extends Model
{
    protected $table = 'multibank_accounts';

    protected $fillable = [
        'name',
        'badge',
        'account_title',
        'account_number',
        'routing_code',
        'qr_payload',
        'qr_image_url',
        'instructions',
        'currency',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Scope to only active accounts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order', 'asc');
    }

    /**
     * Check if this account matches the invoice currency or is universal.
     */
    public function matchesCurrency(?string $currency): bool
    {
        if (empty($this->currency) || strtoupper($this->currency) === 'ALL') {
            return true;
        }

        return strtoupper($this->currency) === strtoupper($currency ?? '');
    }
}
