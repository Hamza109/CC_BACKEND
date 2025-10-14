<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalAidClinic extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'tbl_legalaid_clinic';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'aid_id';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'aid_id',
        'name',
        'address',
        'lat',
        'lng',
        'district_name',
    ];
}


