<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyFiles extends Model
{
    use HasFactory;
    protected $table = "property_files";
    protected $guarded = ["id"];
    public $timestamps = false;

    /**
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PropertyFilesCategories::class, 'property_files_category_id');
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
