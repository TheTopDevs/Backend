<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyPhoto extends Model
{
    use HasFactory;

    public const TYPE_EXTERIOR = 1;
    public const TYPE_INTERIOR_ROOMS = 2;
    public const TYPE_INTERIOR_COMMON_AREAS = 3;
    public const TYPE_AMENTIES = 4;
    public const TYPE_BUILDING_SYSTEM = 5;
    public const TYPE_SIGNANGE = 6;
    public const TYPE_AERIALS = 7;

    /** @var array $fillable */
    protected $fillable = [
        'building_id', 'image', 'is_main', 'type', 'thumbnail'
    ];


    public function getRouteKeyName(): string
    {
        return 'id';
    }

    /**
     * @return BelongsTo
     */
    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    /**
     * @return array
     */
    public static function getTypesList(): array
    {
        return [
            self::TYPE_EXTERIOR,
            self::TYPE_INTERIOR_ROOMS,
            self::TYPE_INTERIOR_COMMON_AREAS,
            self::TYPE_AMENTIES,
            self::TYPE_BUILDING_SYSTEM,
            self::TYPE_SIGNANGE,
            self::TYPE_AERIALS,
        ];
    }
}
