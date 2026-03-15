<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DuplicateGroup extends Model
{
    protected $fillable = [
        'signature'
    ];

    public function clients()
    {
        return $this->hasMany(Client::class, 'duplicate_group_id');
    }
}
