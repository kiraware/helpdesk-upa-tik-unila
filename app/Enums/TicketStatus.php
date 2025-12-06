<?php

namespace App\Enums;

enum TicketStatus: string
{
    case WAITING = 'waiting';
    case PROGRESS = 'progress';
    case DONE = 'done';
    case REJECT = 'reject';
}
