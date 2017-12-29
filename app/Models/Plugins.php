<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Plugins
 */
class Plugins extends Model
{
    protected $table = 'plugins';

    protected $primaryKey = 'code';

    public $timestamps = false;

    protected $fillable = [
        'version',
        'library',
        'assign',
        'install_date'
    ];

    protected $guarded = [];

        
}