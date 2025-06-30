<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerProductTranslation extends Model
{
    protected $table = 'customer_product_translations';

    use HasFactory;

    protected $fillable = ['customer_product_id', 'name', 'lang'];

    public function customer_product()
    {
        return $this->belongsTo(CustomerProduct::class);
    }
}
