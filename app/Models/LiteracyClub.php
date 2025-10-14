<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiteracyClub extends Model
{
    use HasFactory;

    protected $table = 'tbl_literacy_clubs';
    protected $primaryKey = 'club_id';
    public $incrementing = true;

    protected $fillable = [
        'club_id',
        'name',
        'lat',
        'lng',
        'district_name',
    ];
}
