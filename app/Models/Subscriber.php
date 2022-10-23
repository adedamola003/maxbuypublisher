<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $array)
 * @method static where(array $array)
 * @method static select(string[] $array)
 */
class Subscriber extends Model
{
    use HasFactory;
    protected  $guarded = [];

    public function topic(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Topic', 'topic_id', 'id');
    }
}
