<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Supplier;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'supplier_id', 'sku', 'name', 'description', 'category_id', 'uom_id', 'currency_id', 'base_price', 'min_quantity_threshold', 'lead_time_days'
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'uom_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'product_suppliers', 'product_id', 'supplier_id')
            ->withPivot('supplier_sku', 'unit_price', 'lead_time_days', 'is_preferred');
    }

    /**
     * Legacy formatted price string, e.g. "₱2,450.00", used by views via $p['price'].
     */
    public function getPriceAttribute($value): string
    {
        $val = $value ?? $this->base_price;
        return '₱' . number_format((float) $val, 2);
    }
}
