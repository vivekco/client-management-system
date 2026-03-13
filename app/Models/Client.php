<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'company_name',
        'email',
        'phone_number',
        'signature',
        'duplicate_group_id'
    ];

    public function duplicateGroup()
    {
        return $this->belongsTo(DuplicateGroup::class);
    }
}
