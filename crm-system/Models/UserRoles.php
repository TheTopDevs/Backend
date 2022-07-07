<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRoles extends Model
{
    use HasFactory;

    protected $table = "user_roles";
    protected $fillable = ["name"];
    public $timestamps = false;

    public const MANAGER = "manager";
    public const ADMIN = "admin";
    public const JV_PARTNER = "jv_partner";
    public const JV_PARTNER_OWNER = "jv_partner_owner";

    public static function getBaseRolesList(): array
    {
        return [
            self::MANAGER,
            self::ADMIN,
            self::JV_PARTNER,
            self::JV_PARTNER_OWNER,
        ];
    }
}
