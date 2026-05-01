<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'farmer_id',
        'operator_id',
        'total_fcfa',
        'payment_method',
        'interest_rate',
        'total_with_interest',
        'status'
    ];

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function debt()
    {
        return $this->hasOne(Debt::class);
    }
}