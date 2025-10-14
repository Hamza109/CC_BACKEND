<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistrictLitigationOfficer extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'district_litigation_offices';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = true;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'office_name',
        'contact_number',
        'lat',
        'lng',
        'district_name',
    ];
}

