<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ItemUser extends Pivot
{
    protected $table = 'item_user';

    protected $casts = [
        // Add any custom casts if needed
    ];
}
