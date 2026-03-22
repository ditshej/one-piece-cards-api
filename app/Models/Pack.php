<?php

namespace App\Models;

use Database\Factories\PackFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['id', 'name'])]
class Pack extends Model
{
    /** @use HasFactory<PackFactory> */
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }
}
