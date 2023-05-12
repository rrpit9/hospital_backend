<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessPlanOrder extends Model
{
    use HasFactory, SoftDeletes;

    const IN_ACTIVE     = 0;
    const ACTIVE        = 1;
    const FAILED        = 2;
    const EXPIRED       = 3;
}
