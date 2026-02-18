<?php

declare(strict_types=1);

namespace App\Enums;

enum SeatStatus: string
{
    case Available = 'available';
    case Locked = 'locked';
    case Booked = 'booked';
}
