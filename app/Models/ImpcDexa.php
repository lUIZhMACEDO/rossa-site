<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImpcDexa extends Model
{
    protected $table = 'impc_dexa';

    protected $connection = 'impc';

    protected $primaryKey = 'specimen_id';
}
