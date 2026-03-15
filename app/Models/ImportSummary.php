<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportSummary extends Model
{
    use HasFactory;
    protected $table = 'import_summaries';
    protected $fillable = ['file_name', 'total_rows', 'inserted', 'duplicates', 'skipped'];
}
