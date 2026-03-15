<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    use HasFactory;

    protected $fillable = ['row_number', 'data', 'errors', 'file_name'];
    protected $casts = [
        'data' => 'array',
        'errors' => 'array',
    ];
}
