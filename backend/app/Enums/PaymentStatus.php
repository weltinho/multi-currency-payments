<?php

namespace App\Enums;

/** Payment lifecycle — expired status will be set by a scheduled command (Phase 3). */
enum PaymentStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Expired = 'expired';
}
