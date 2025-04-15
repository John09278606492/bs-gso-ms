<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class SyearSemesterCollection extends Pivot
{
    protected $table = 'syear_semester_collection';

    protected $fillable = ['syear_semester_id', 'collection_id'];
}
