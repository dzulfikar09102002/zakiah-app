<?php

namespace App\Enums;

enum PaymentMethodKindEnum: string
{
    case Cash = 'cash';
    case Debit = 'debit';
    case CreditCard = 'credit_card';
    case Qris = 'qris';
    case OnlinePayment = 'online_payment';
    case VA = 'va';
}
