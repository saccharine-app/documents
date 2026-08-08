<?php

namespace Saccharine\Documents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentCategory extends Model
{
    protected $guarded = [];

    /**
     * Get the templates within this category.
     */
    public function documentTemplates(): HasMany
    {
        return $this->hasMany(DocumentTemplate::class);
    }
}
