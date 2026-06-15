<?php

namespace App\Enums;

/** Payment lifecycle — expired is set by payments:expire-pending when pending > 48h. */
enum PaymentStatus: string
{
    /** Awaiting finance review (amber badge in UI). */
    case Pending = 'pending';

    /** Finance approved — counted in approved EUR total (green badge). */
    case Approved = 'approved';

    /** Finance rejected (red badge). */
    case Rejected = 'rejected';

    /** Auto-expired after 48h without finance action (muted badge). */
    case Expired = 'expired';
}
