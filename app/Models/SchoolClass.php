<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    use HasFactory;

    protected $table = 'classes';
    protected $fillable = [
        'user_id', 
        'class_name',
    ];

    public function students()
    {
        return $this->belongsToMany(User::class, 'class_student', 'school_class_id', 'user_id');
    }

    public function teachers()
    {
        return $this->belongsToMany(User::class, 'class_teacher', 'school_class_id', 'user_id');
    }
}