<?php

namespace App\Http\Controllers\Api;

use App\Models\SchoolClass;
use App\Models\ClassStudent;
use App\Models\ClassTeacher;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

class MyClassController extends Controller
{
    public function attachStudentAndTeacherToClass(Request $request)
    {
        $validator = Validator::make($request->all(), [
        'id' => 'required|array',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'The given data was invalid.',
            'errors' => $validator->errors(),
        ], 422);
    }

    // Veriler geçerliyse, veritabanına kaydedebilirsiniz.
    // ...

    return response()->json([
        'success' => true,
        'message' => 'Data has been saved successfully.',
    ], 200);

    }
}


