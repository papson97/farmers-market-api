<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Debt extends Model
{
    protected $fillable = [
        'transaction_id',
        'farmer_id',
        'amount_fcfa',
        'remaining_fcfa',
        'status'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }

    public function repayments()
    {
        return $this->belongsToMany(Repayment::class, 'repayment_debt')
            ->withPivot('amount_applied_fcfa');
    }
}