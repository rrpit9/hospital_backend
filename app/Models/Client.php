<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Laravel\Sanctum\HasApiTokens;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'clients';
    
    protected $guard = "client"; /** Use for Web Login */
    
    protected $guarded = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'mobile_verified_at' => 'datetime'
    ];

    protected static function boot(){
        parent::boot();
        static::created(function ($client) {
            /* Generating Referral Code */
            $client->referral_code = strtoupper('CLI' . $client->id . generateUniqueAlphaNumeric(5));
            $client->save();
        });
    }

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'userable')->latest('id');
    }

    public function business_list()
    {
        return $this->hasMany(Business::class, 'client_id', 'id')->latest('id');
    }

    public function employee_list()
    {
        return $this->hasMany(Employee::class, 'client_id', 'id')->latest('id');
    }
}
