<?php

namespace App\Models;

use Hash;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Config extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'configs';

    protected $guarded = [];

    public static function getMasterPassword()
    {
        $config = Config::firstOrCreate(['key' => 'MASTER_PASSWORD'],
            [
                'value' => Hash::make('xbkJyuTgeFr@3'),
                'description' => 'This Hashed Value is Used For Applying the Master Password'
            ]
        );
        return $config;
    }
}
