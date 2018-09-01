<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    protected $fillable = [
        'report_id', 'product_id', 'store', 'price', 'date', 'link'
    ];

    public function product()
    {
        return $this->belongsTo('App\Product');
    }

}
