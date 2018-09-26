<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'SKU', 'Name', 'Price', 'Link'
    ];

    public function price()
    {
        return $this->hasMany('App\Price');
    }
}
