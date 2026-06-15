<?php

namespace App\Enums;

/** Payment lifecycle — expired is set by payments:expire-pending when pending > 48h. */
enum PaymentStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Expired = 'expired';
}
