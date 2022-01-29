<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * Returns all expanses for category
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function expanses()
    {
        return $this->hasMany(Expense::class);
    }
}
