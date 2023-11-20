<?php

namespace Tv2regionerne\StatamicEvents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Handler extends Model
{
    use SoftDeletes;
    
    public $table = 'event_handlers';

    protected $casts = [
        'config' => 'array',
    ];
}
