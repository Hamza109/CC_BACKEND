<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dlsa extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'tbl_dlsa';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'dlsa_id';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = true;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'dlsa_id',
        'office',
        'name',
        'name_dlsa',
        'mobile_no',
        'alternate_no',
        'lat',
        'lng',
        'designation',
    ];
}
