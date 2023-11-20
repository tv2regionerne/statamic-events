<?php

namespace Tv2regionerne\StatamicEvents\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Handler extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'event_handlers';

    protected $casts = [
        'config' => 'array',
    ];
}
