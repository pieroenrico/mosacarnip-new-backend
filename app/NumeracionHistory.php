<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NumeracionHistory extends Model
{
    protected $table = 'history_numeraciones';
    protected $guarded = ['id'];
}
