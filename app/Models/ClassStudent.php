<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassStudent extends Model
{
    use HasFactory;

    protected $table = 'class_student';
    protected $fillable = [
        'user_id',
        'school_class_id',
    ];

    public function school_class()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }
}