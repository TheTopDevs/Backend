<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PropertyFilesCategories extends Model
{
    use HasFactory;
    protected $table = "property_files_categories";
    protected $guarded = ["id"];
    public $timestamps = false;
    /**
     * @return HasMany
     */
    public function propertyFiles(): HasMany
    {
        return $this->hasMany(PropertyFiles::class, 'property_files_category_id');
    }

    /**
     * @return int
     */
    public function countPropertyFiles(): int
    {
        return $this->hasMany(PropertyFiles::class, 'property_files_category_id')->count();
    }
}
