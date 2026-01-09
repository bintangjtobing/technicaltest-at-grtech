<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Company extends Model
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'logo',
        'website',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function routeNotificationForMail($notification)
    {
        return $this->email;
    }
}
