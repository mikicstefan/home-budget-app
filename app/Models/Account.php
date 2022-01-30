<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'balance', 'name'];

    /**
     * Return user for account
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Returns all expanses for an account
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function expanses()
    {
        return $this->hasMany(Expense::class);
    }
}
