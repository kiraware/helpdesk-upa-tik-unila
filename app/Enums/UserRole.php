<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPERUSER = 'superuser';
    case ADMIN = 'admin';
    case USER = 'user';
}
