<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourtMap extends Model
{
    /**
     * If this table resides in a different MySQL database than your default,
     * create a connection in config/database.php (e.g., 'mysql_citizen') and
     * uncomment the following line to point the model to that connection.
     */
    // protected $connection = 'mysql_citizen';

    protected $table = 'tbl_map';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'court_name',
        'address',
        'lat',
        'lng',
        'district',
    ];
}


