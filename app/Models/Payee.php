<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payee extends Model
{
    use HasFactory;

    protected $fillable = ['id'];

    protected $casts = [
        'created_at'  => 'date:Y-m-d',
        ];

    public function account() {
        return $this->belongsTo(Account::class);
     }

     public function entry() {
        return $this->hasMany(Entry::class);
     }
}
