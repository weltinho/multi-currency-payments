<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pending payment expiration
    |--------------------------------------------------------------------------
    |
    | Buzzvel brief: pending requests that finance never acted on within this
    | window are marked expired by the scheduled payments:expire-pending command.
    |
    | A single cutoff hour works well with the batch-command approach; changing
    | this value does not require rescheduling individual queue jobs.
    |
    */

    'pending_expiration_hours' => (int) env('PAYMENT_PENDING_EXPIRATION_HOURS', 48),

];
