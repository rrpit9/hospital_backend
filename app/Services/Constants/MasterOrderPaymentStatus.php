<?php

namespace App\Services\Constants;

class MasterOrderPaymentStatus
{
    const INITIATED             = 0;
    const PAYMENT_FAILED        = 1;
    const PAYMENT_COMPLETE      = 2;
    const FULFILLED             = 3; // This is Not Being Used in Payment Table Because it Does not mean for Payment Table
    const REFUND_INITIATED      = 4;
    const PAYMENT_REFUNDED      = 5;

    /** below is the List where not allowed Re-Payment For any Order */
    const NOT_ALLOWED_RE_PAYEMENT = [
        self::PAYMENT_COMPLETE,
        self::FULFILLED,
        self::REFUND_INITIATED,
        self::PAYMENT_REFUNDED
    ];
}
