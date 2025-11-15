<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class TeamMember extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'team_members';

    protected $fillable = [
        'name',
        'title',
        'description',
        'profile_image',
        'telegram_link',
        'facebook_link',
        'phone_number',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

