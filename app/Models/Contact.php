<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $table = 'tbl_contact';
    protected $primaryKey = 'id';
    public $incrementing = true;

    // Table has created_at but not updated_at (based on screenshot)
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'id',
        'reg_no',
        'name',
        'email',
        'mobile_no',
        'present_state',
        'present_district',
        'description',
        'category',
        'status',
        'comment',
        'created_at',
    ];
}
