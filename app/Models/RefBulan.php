<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefBulan extends Model
{
    use HasFactory;

    protected $table = 'ref_bulans';

    protected $fillable = [
        'month_name',
        'month_code',
        'year',
        'working_days',
    ];
}

