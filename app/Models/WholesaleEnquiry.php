<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WholesaleEnquiry extends Model
{
    protected $fillable = [
        'name',
        'business_name',
        'business_type',
        'email',
        'phone',
        'website',
        'business_address',
        'opening_order_value',
        'message',
        'is_read',
    ];

    protected function casts(): array
    {
        return ['is_read' => 'boolean'];
    }
}
