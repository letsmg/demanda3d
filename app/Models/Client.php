<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'doc',
    'address',
    'number',
    'state',
    'zipcode',
    'city',
    'phone1',
    'phone2',
    'contact1',
    'contact2',
])]
class Client extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'phone1' => 'string',
            'phone2' => 'string',
            'contact1' => 'string',
            'contact2' => 'string',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
