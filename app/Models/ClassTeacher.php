<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassTeacher extends Model
{
    use HasFactory;

    protected $table = 'class_teacher';
    protected $fillable = [
        'user_id',
        'school_class_id',
    ];

    public function school_class()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }
}