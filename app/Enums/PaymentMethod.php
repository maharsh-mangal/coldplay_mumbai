<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentMethod: string
{
    case Upi = 'upi';
    case CreditCard = 'credit_card';
    case DebitCard = 'debit_card';
    case NetBanking = 'net_banking';
}
