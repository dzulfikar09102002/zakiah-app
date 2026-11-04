<?php

namespace App\Enums;

/**
 * Status sesi tidak disimpan di database, dihitung dari start_at, review_at, dan end_at.
 */
enum OpnameSessionStatus: string
{
    case Scheduled = 'scheduled';
    case Started = 'started';
    case Review = 'review';
    case Finished = 'finished';
}
