<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Spatie\Permission\Models\Permission as BasePermission;

class Permission extends BasePermission
{
    use LogsModelActivity;
}
