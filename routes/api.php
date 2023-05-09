<?php

use App\Http\Controllers\Api\TeacherManagementApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TypeController;
use App\Http\Controllers\Api\MyClassController;





/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});




Route::get('/students', [TeacherManagementApiController::class, 'index']);                                                      // öğrenci listesi
Route::post('/students/student', [TeacherManagementApiController::class, 'store']);                                             //
Route::get('/students/student/{id}', [TeacherManagementApiController::class, 'show']);                                          //
Route::put('/students/student/{id}', [TeacherManagementApiController::class, 'update']);                                        //
Route::delete('/students/student/{id}', [TeacherManagementApiController::class, 'destroy']);                                    //


Route::get('/getType', [TypeController::class, 'getUsersIdAndType']);                                                           //tipini buluyor


Route::post('/AddingToClass', [MyClassController::class, 'attachStudentAndTeacherToClass']);                                    //Sınıfa Ekliyor.
Route::put('/updateStudents', [TeacherManagementApiController::class, 'updateStudents']);                                       //öğrenci güncelleme



Route::post('/school-class/attach-student', [TeacherManagementApiController::class, 'attachStudentToClass']);                    //Öğrenciyi Sınıfa Ekleme
Route::post('/school-class/attach-teacher', [TeacherManagementApiController::class, 'attachTeacherToClass']);                    //Öğretmeni Sınıfa Ekleme

Route::get('/classes-with-teachers-and-students', [TeacherManagementApiController::class, 'getClassesWithTeachersAndStudents']);  //öğretmen,öğrenci ve sınıfları bulma

Route::get('/teacher-classes/{teacher_id}', [TeacherManagementApiController::class, 'getTeacherClasses']);                         //öğretmenin sınıfları bulma




