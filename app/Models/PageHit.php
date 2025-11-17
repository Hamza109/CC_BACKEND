<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageHit extends Model
{
    use HasFactory;

    protected $table = 'page_hits';

    protected $fillable = [
        'page_name',
        'ip_address',
        'browser',
        'latitude',
        'longitude',
        'district',
        'state',
        'country',
        'created_at',
    ];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;
}


