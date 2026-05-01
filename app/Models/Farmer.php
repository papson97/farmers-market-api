<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Farmer extends Model
{
    protected $fillable = [
        'identifier',
        'firstname',
        'lastname',
        'phone',
        'credit_limit_fcfa'
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function debts()
    {
        return $this->hasMany(Debt::class);
    }

    public function repayments()
    {
        return $this->hasMany(Repayment::class);
    }

    // Calcule la dette totale actuelle
    public function totalDebt()
    {
        return $this->debts()
            ->whereIn('status', ['open', 'partial'])
            ->sum('remaining_fcfa');
    }
}