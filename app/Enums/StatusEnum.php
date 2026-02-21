<?php

namespace App\Enums;

enum StatusEnum: string
{
    case Active = 'active';
    case InActive = 'in_active';
    case Archived = 'archived';
    case Requested = 'requested';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
    case Void = 'void';
}
