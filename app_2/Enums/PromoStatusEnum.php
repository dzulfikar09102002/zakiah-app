<?php

namespace App\Enums;

enum PromoStatusEnum: string
{
    case Scheduled = 'scheduled';
    case ACTIVE = 'active';
    case complete = 'complete';
    case Cancelled = 'cancelled';
    case Archived = 'archived';
}
