<?php

namespace App\Models;

use App\Models\ContractHistory;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($supplier) {
            if (empty($supplier->slug)) {
                $supplier->slug = \Illuminate\Support\Str::slug($supplier->supplier_name ?: $supplier->name);
            }
            if (empty($supplier->supplier_name)) {
                $supplier->supplier_name = $supplier->name;
            }
            if (empty($supplier->name)) {
                $supplier->name = $supplier->supplier_name;
            }
        });
    }

    protected $fillable = [
        'slug', 'supplier_name', 'name', 'category', 'email', 'phone', 'address_id', 'status',
        'supplier_id', 'supplier_code', 'description', 'rating', 'since', 'location', 'last_transaction',
        'contact_name', 'contact_role', 'contact_phone', 'contact_email',
        'total_orders', 'total_spent', 'avg_order_value', 'on_time_rate',
        'contract_start', 'contract_end', 'contract_duration', 'payment_terms', 'auto_renewal',
        'contract_document', 'contract_document_size', 'contract_scope',
        'avg_rating_delta', 'on_time_delta', 'quality_score', 'quality_delta', 'total_orders_delta',
        'blacklist_reason', 'blacklisted_since', 'risk_level',
    ];

    protected $casts = [
        'contract_scope' => 'array',
        'auto_renewal' => 'boolean',
        'total_spent' => 'float',
        'avg_order_value' => 'float',
        'rating' => 'float',
        'since' => 'date',
        'last_transaction' => 'date',
        'blacklisted_since' => 'date',
        'contract_start' => 'date',
        'contract_end' => 'date',
    ];

    protected $appends = ['supplier_id', 'products_list', 'contract', 'performance', 'name', 'address'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // Alias 'name' to 'supplier_name' to support legacy views
    public function getNameAttribute($value): string
    {
        return $value ?: ($this->supplier_name ?? '');
    }

    // Format full address for legacy views
    public function getAddressAttribute(): string
    {
        if ($this->addressRelation) {
            return $this->addressRelation->street . ', ' . $this->addressRelation->city;
        }
        return '';
    }

    // Map legacy supplier_code column to `supplier_id` expected by views
    public function getSupplierIdAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }

        return $this->attributes['supplier_code'] ?? null;
    }

    // Return products_list if set; otherwise create from products relation
    public function getProductsListAttribute($value): string
    {
        if (!empty($value)) {
            return $value;
        }

        $names = $this->products()->pluck('name')->toArray();
        if (!empty($names)) {
            return implode(', ', $names);
        }

        return '';
    }

    public function getSinceAttribute($value): ?string
    {
        return $value ? \Illuminate\Support\Carbon::parse($value)->format('M d, Y') : null;
    }

    public function getLastTransactionAttribute($value): ?string
    {
        return $value ? \Illuminate\Support\Carbon::parse($value)->format('M d, Y') : null;
    }

    // Accessor: format products list to match views structure
    public function getProductsAttribute()
    {
        return $this->productsRelation()->with(['category', 'uom'])->get()->map(function ($p) {
            $code = strtoupper($p->pivot->supplier_sku ?? $p->sku ?? '');
            $unitPrice = (float) ($p->pivot->unit_price ?? $p->base_price ?? 0);
            $moq = (float) ($p->min_quantity_threshold ?? 10);
            $leadTime = (int) ($p->pivot->lead_time_days ?? $p->lead_time_days ?? 3);

            return [
                'id' => $p->id,
                'name' => $p->name,
                'code' => $code,
                'category' => $p->category ? $p->category->category_name : 'Grains',
                'unit' => $p->uom ? $p->uom->uom_code : 'Sack',
                'price' => '₱' . number_format($unitPrice, 2),
                'raw_unit_price' => $unitPrice,
                'stock' => 'In Stock',
                'moq' => $moq . ' Units',
                'raw_moq' => $moq,
                'lead_time' => $leadTime . ' Days',
                'raw_lead_time' => $leadTime,
            ];
        })->toArray();
    }

    // Accessor: format purchase history list to match views structure
    public function getPurchaseHistoryAttribute()
    {
        return $this->purchaseOrders()->with(['poItems.product', 'poItems.uom', 'items'])->get()->flatMap(function ($po) {
            $date = $po->order_date ? $po->order_date->format('Y-m-d') : ($po->issued_at ? $po->issued_at->format('Y-m-d') : ($po->created_at ? $po->created_at->format('Y-m-d') : ''));
            $statusLabel = ucfirst($po->status ?? 'Active');

            if ($po->items && $po->items->count() > 0) {
                return $po->items->map(function ($item) use ($po, $date, $statusLabel) {
                    return [
                        'date' => $date,
                        'po_number' => $po->po_number,
                        'product' => $item->name,
                        'quantity' => $item->quantity . ' ' . ($item->unit ?? 'Unit'),
                        'amount' => $item->line_total ?? ($item->quantity * $item->unit_price),
                        'status' => $statusLabel,
                    ];
                });
            }

            return $po->poItems->map(function ($item) use ($po, $date, $statusLabel) {
                return [
                    'date' => $date,
                    'po_number' => $po->po_number,
                    'product' => $item->product ? $item->product->name : 'Item',
                    'quantity' => $item->quantity . ' ' . ($item->uom ? $item->uom->uom_code : 'Unit'),
                    'amount' => $item->quantity * $item->unit_price,
                    'status' => $statusLabel,
                ];
            });
        })->toArray();
    }

    public function getContractAttribute()
    {
        $daysRemaining = 0;
        if ($this->contract_end) {
            $end = \Illuminate\Support\Carbon::parse($this->contract_end)->startOfDay();
            $now = now()->startOfDay();
            $diff = (int) $now->diffInDays($end, false);
            $daysRemaining = $diff < 0 ? 0 : $diff;
        }

        return [
            'start' => $this->contract_start ? \Illuminate\Support\Carbon::parse($this->contract_start)->format('M. d, Y') : '',
            'end' => $this->contract_end ? \Illuminate\Support\Carbon::parse($this->contract_end)->format('M. d, Y') : '',
            'duration' => $this->contract_duration ?? '',
            'days_remaining' => (int) $daysRemaining . ' Days',
            'payment_terms' => $this->payment_terms ?? '',
            'document' => $this->contract_document ?? 'No Document',
            'document_size' => $this->contract_document_size ?? '0 KB',
            'scope' => $this->contract_scope ?? [],
            'history' => $this->contractHistoryEntries->map(function ($h) {
                return [
                    'date' => $h->date ? $h->date->format('Y-m-d') : '',
                    'action' => $h->action,
                    'by' => $h->performed_by,
                    'remarks' => $h->remarks,
                ];
            })->toArray(),
        ];
    }

    // Accessor: format performance insights
    public function getPerformanceAttribute()
    {
        return [
            'avg_rating' => number_format((float) $this->rating, 1),
            'avg_rating_delta' => $this->avg_rating_delta ?? 'No change',
            'on_time' => $this->on_time_rate . '%',
            'on_time_delta' => $this->on_time_delta ?? 'No change',
            'quality_score' => number_format((float) $this->quality_score, 1),
            'quality_delta' => $this->quality_delta ?? 'No change',
            'total_orders' => number_format($this->total_orders),
            'total_orders_delta' => $this->total_orders_delta ?? 'No change',
        ];
    }

    // Relations
    public function addressRelation()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function productsRelation()
    {
        return $this->belongsToMany(Product::class, 'product_suppliers', 'supplier_id', 'product_id')
            ->withPivot('supplier_sku', 'unit_price', 'lead_time_days', 'is_preferred');
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'supplier_id');
    }

    public function contractHistoryEntries()
    {
        return $this->hasMany(ContractHistory::class, 'supplier_id');
    }

    public function supplierInvoices()
    {
        return $this->hasMany(SupplierInvoice::class, 'supplier_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'supplier_id');
    }
}
