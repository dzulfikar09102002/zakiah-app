<?php

namespace App\Enums;

enum CustomerPointTypeEnum: string
{
    //
    case Reserve = 'reserve';
    case Earn = 'earn';
    case Redeem = 'redeem';
    case EarnRefund = 'earn_refund';
    case RedeemRefund = 'redeem_refund';
    case EarnVoid = 'earn_void';
    case RedeemVoid = 'redeem_void';
    case EarnRefundVoid = 'earn_refund_void';
    case RedeemRefundVoid = 'redeem_refund_void';
    case SystemEarn = 'system_earn';

    public static function toArray(): array
    {
        return array_column(CustomerPointTypeEnum::cases(), 'value');
    }
}
