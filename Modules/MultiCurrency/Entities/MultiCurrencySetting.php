<?php

namespace Modules\MultiCurrency\Entities;

use Illuminate\Database\Eloquent\Model;

class MultiCurrencySetting extends Model
{
    // protected $fillable = [];
    
    protected $guarded = ['id'];

    public function business()
    {
        return $this->belongsTo(\App\Business::class, 'business_id');
    }
}
