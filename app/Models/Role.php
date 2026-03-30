<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Spatie\Permission\Models\Role as BaseRole;

class Role extends BaseRole
{
    use LogsModelActivity;
}
