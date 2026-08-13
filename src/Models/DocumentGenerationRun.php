<?php

namespace Saccharine\Documents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentGenerationRun extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'status',
        'output_preference',
        'configuration',
    ];

    public function userId(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
