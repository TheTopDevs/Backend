<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Building extends Model
{
    use HasFactory;

    protected $guarded = ['id'];


    public function getRouteKeyName(): string
    {
        return 'id';
    }

    /**
     * if you would like to customize the value that is placed in the route parameter  (route('profile', [$user]) == profile/{id})
     */
    public function getRouteKey()
    {
        return $this->id;
    }

    /**
     * @return HasOne
     */
    public function financialInformation(): HasOne
    {
        return $this->hasOne(FinancialInformation::class);
    }

    /**
     * @return HasOne
     */
    public function systems(): HasOne
    {
        return $this->hasOne(Systems::class);
    }

    /**
     * @return HasOne
     */
    public function saleDetails(): HasOne
    {
        return $this->hasOne(SaleDetails::class);
    }

    /**
     * @return HasOne
     */
    public function attributes(): HasOne
    {
        return $this->hasOne(Attributes::class);
    }

    /**
     * @return HasOne
     */
    public function contactInformation(): HasOne
    {
        return $this->hasOne(ContactInformation::class);
    }

    /**
     * @return HasMany
     */
    public function propertyFilesCategories(): HasMany
    {
        return $this->hasMany(PropertyFilesCategories::class);
    }

    /**
     * @return HasMany
     */
    public function propertyPhotos(): HasMany
    {
        return $this->hasMany(PropertyPhoto::class);
    }

    public function mainPhoto()
    {
        return $this->hasOne(PropertyPhoto::class)
            ->where('is_main', "=", 1)
            ->first();
    }
}
