<?php

namespace App\Enums;

/** Drives authorization: employees submit payments; finance reviews and provisions accounts. */
enum UserRole: string
{
    case Employee = 'employee';
    case Finance = 'finance';
}
