<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Suppliers
 */
class Suppliers extends Model
{
    protected $table = 'suppliers';

    protected $primaryKey = 'suppliers_id';

    public $timestamps = false;

    protected $fillable = [
        'suppliers_name',
        'suppliers_desc',
        'is_check'
    ];

    protected $guarded = [];

        
}