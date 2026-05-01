<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Repayment extends Model
{
    protected $fillable = [
        'farmer_id',
        'operator_id',
        'kg_received',
        'commodity_rate_fcfa',
        'total_fcfa_credited',
    ];

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function debts()
    {
        return $this->belongsToMany(Debt::class, 'repayment_debt')
            ->withPivot('amount_applied_fcfa');
    }
}