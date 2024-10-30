<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VariationTemplate extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Get the attributes for the variation.
     */
    public function values()
    {
        return $this->hasMany(\App\VariationValueTemplate::class);
    }

    public static function forDropdown($business_id)
    {
        // Ambil variation template dengan nama 'bisnis lokasi'
        $template = VariationTemplate::where('name', 'Bisnis Lokasi')->first();

        // Jika template ada, ambil variation values-nya
        $variation_values = [];
        if ($template) {
            $variation_values = VariationValueTemplate::where('variation_template_id', $template->id)
                ->pluck('name', 'id')
                ->toArray();
        }

        return collect($variation_values);
    }
}
