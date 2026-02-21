<?php

namespace App\Enums;

enum LocationKindEnum: string
{
    case MainOffice = 'main_office';
    case Outlet = 'outlet';
    case Warehouse = 'warehouse';
}
