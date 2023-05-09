<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class TypeController extends Controller
{
     public function getUsersIdAndType()
    {
        $users = DB::table('users')
                    ->select('id', 'type')
                    ->whereIn('type', ['student', 'teacher'])
                    ->get();

        return $users;
    }
}




