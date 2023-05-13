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

    protected $guarded = [];

    public function businessPlan()
    {
        return $this->belongsTo(BusinessPlan::class, 'business_plan_id', 'id')->latest('id');
    }

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'id');
    }

    /**
     * Get the master order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function masterOrder()
    {
        return $this->morphOne(MasterOrder::class, 'orderable')->latest('id');
    }

    /**
     * Get the master payment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function masterPayment()
    {
        return $this->morphOne(MasterPayment::class, 'orderable')->latest('id');
    }
}
