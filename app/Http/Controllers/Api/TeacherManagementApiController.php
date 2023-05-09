<?php

namespace App\Http\Controllers\Api;

use App\Models\SchoolClass;
use App\Models\ClassStudent;
use App\Models\ClassTeacher;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;



class TeacherManagementApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $students = User::where('type', 'student')->get();
        return $students;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $student = new User();
        $student->name = $request->name;
        $student->surname = $request->surname;
        $student->type = 'student';

        $student->save();

        return response()->json(['message' => 'Öğrenci Eklendi!']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $student = User::find($id);
        return response()->json($student);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $student = User::findOrFail($id);
        $student->name = $request->name;
        $student->surname = $request->surname;

        $student->save();

        return response()->json(['message' => 'Öğrenci güncellendi!']);
    }

    /**
     * Update multiple students.
     */
    

    public function updateStudents(Request $request)
{
    $validator = Validator::make($request->all(), [
        'school_class_id' => 'required',
        'new_school_class_id' => 'nullable',
        'students_to_add' => 'nullable|array',
        'students_to_add.*.id' => 'required|integer',
        'students_to_remove' => 'nullable|array',
        'students_to_remove.*.id' => 'required|integer',
    ]);

    if ($validator->fails()) {
        return response()->json(['message' => 'Öğrenci(ler) sınıfa eklenirken bir hata oluştu.'], 500);
    }

    $students_to_add = $request->students_to_add ?? [];
    $students_to_remove = $request->students_to_remove ?? [];
    $school_class_id = $request->school_class_id;
    $new_school_class_id = $request->new_school_class_id;

    if (is_null($school_class_id)) {
        return response()->json(['message' => 'Sınıf belirtilmemiş.'], 400);
    }


    //! Öğrenci çıkarma işlemi
    $students_to_remove_data = [];
    $removed_students = [];

    foreach ($students_to_remove as $key => $value) {
        $user_id = $value['id'];
        $user_type = User::find($user_id)->type;

        if ($user_type == 'student') {
            $exists = DB::table('class_student')->where('user_id', $user_id)
                ->where('school_class_id', $school_class_id)
                ->exists();
            if ($exists) {
                $student_data = ['user_id' => $user_id, 'school_class_id' => $school_class_id];
                array_push($students_to_remove_data, $student_data);
                array_push($removed_students, $value);
            }
        }
    }

    // Öğrenci bilgilerine erişmek için birleştirme işlemi 
    $removed_students = collect($removed_students)->map(function ($item) {
        $user = User::find($item['id']);
        $item['name'] = $user->name;
        $item['surname'] = $user->surname;
        return $item;
    })->toArray();

    // Veritabanından çıkarma işlemi
    if (count($students_to_remove_data) > 0) {
        DB::table('class_student')->where('school_class_id', $school_class_id)->whereIn('user_id', array_column($students_to_remove_data, 'user_id'))->delete();
    } 


    //! Öğrenci ekleme işlemi
    $students_to_add_data = [];
    $new_students = [];

    foreach ($students_to_add as $key => $value) {
        $user_id = $value['id'];
        $user_type = User::find($user_id)->type;

        if ($user_type == 'student') {
            $exists = DB::table('class_student')->where('user_id', $user_id)
                ->where('school_class_id', $school_class_id)
                ->exists();
            if (!$exists) {
                $student_data = ['user_id' => $user_id, 'school_class_id' => $school_class_id];
                // Öğrencinin zaten ekli olup olmadığına bakıyoruz.
                $already_added = collect($students_to_add_data)->where('user_id', $user_id)->first();
                if (!$already_added) {
                    array_push($students_to_add_data, $student_data);
                    array_push($new_students, $value);
                }
            }
        }
    }

    // Öğrenci bilgilerine erişmek için birleştirme işlemi 
    $new_students = collect($new_students)->map(function ($item) {
        $user = User::find($item['id']);
        $item['name'] = $user->name;
        $item['surname'] = $user->surname;
        return $item;
    })->toArray();

    // Veritabanına ekleme işlemi
    if ((count($students_to_add_data) > 0) OR (count($students_to_remove_data) > 0) ) {
        DB::table('class_student')->insert($students_to_add_data);
        return response()->json(['message' => 'işlem başarılı', 'new_students' => $new_students, "removed_students" => $removed_students]);
    } else {
        return response()->json(['message' => 'işlem başarısız.'], 400);
    }

}

    /**
     * Student, Teacher and Class display.
     */

    public function getClassesWithTeachersAndStudents()
    {
        $classes = DB::table('classes')
            ->join('users', 'classes.user_id', '=', 'users.id')
            ->select('classes.id', 'classes.class_name', 'classes.user_id', 'users.name as teacher_name', 'users.surname as teacher_surname')
            ->get();

        $result = [];

        foreach ($classes as $class) {
            $class_members = DB::table('users')
                ->leftJoin('class_student', function ($join) use ($class) {
                    $join->on('users.id', '=', 'class_student.user_id')
                        ->where('class_student.school_class_id', '=', $class->id);
                })
                ->leftJoin('class_teacher', function ($join) use ($class) {
                    $join->on('users.id', '=', 'class_teacher.user_id')
                        ->where('class_teacher.school_class_id', '=', $class->id);
                })
                ->select('users.id', 'users.name', 'users.surname', 'class_student.id as student_id', 'class_teacher.id as teacher_id')
                ->get();

            $students = [];
            $teachers = [];
            foreach ($class_members as $member) {
                if (!empty($member->student_id)) {
                    $student_info = [
                        'id' => $member->id,
                        'name' => $member->name,
                        'surname' => $member->surname
                    ];
                    $students[] = $student_info;
                }

                if (!empty($member->teacher_id)) {
                    $teacher_info = [
                        'id' => $member->id,
                        'name' => $member->name,
                        'surname' => $member->surname
                    ];
                    $teachers[] = $teacher_info;
                }
            }

            $class_info = [
                'id' => $class->id,
                'name' => $class->class_name,
                'teacher_id' => $class->user_id,
                'teacher_name' => $class->teacher_name,
                'teacher_surname' => $class->teacher_surname,
                // 'created_at' => $class->created_at,
                // 'updated_at' => $class->updated_at,
                'students' => $students,
                'teachers' => $teachers
            ];

            $result[] = $class_info;
        }

        // return response()->json(['classes' => $result]);

        $response = ['classes' => $result];

        if ($response) {
            return response()->json($response);
        } else {
            return response()->json(['error' => 'Sınıflar alınırken bir hata oluştu.']);
        }
    }


    /**
     * teachers' classrooms
     */

    // public function getTeacherClasses()
    // {
    //     $teachers = User::where('type', 'teacher')->get();
    //     $teacherClasses = [];
    //     foreach ($teachers as $teacher) {
    //         $classes = DB::table('classes')
    //             ->join('class_teacher', 'classes.id', '=', 'class_teacher.school_class_id')
    //             ->select('classes.id', 'classes.class_name')
    //             ->where('class_teacher.user_id', $teacher->id)
    //             ->get();
    //         $teacherClasses[$teacher->name] = $classes;
    //     }
    //     return response()->json(['teacher_classes' => $teacherClasses]);
    // }

        public function getTeacherClasses($teacher_id)
        {
            $teacher = User::find($teacher_id);
            if (!$teacher || $teacher->type != 'teacher') {
                return response()->json(['message' => 'Invalid teacher id'], 404);
            }

            $classes = [];
            $class_teacher = DB::table('class_teacher')
                ->where('user_id', $teacher_id)
                ->get();

            foreach ($class_teacher as $ct) {
                $class = DB::table('classes')
                    ->where('id', $ct->school_class_id)
                    ->select('id', 'class_name')
                    ->first();
                if ($class) {
                    $classes[] = $class;
                }
            }

           return response()->json([
                'id' => $teacher->id,
                'name' => $teacher->name,
                'surname' => $teacher->surname,
                'classes' => $classes
    ]);

}


    /**
     * Attach students to a school class.
     */

    public function attachStudentToClass(Request $request)
    {
    $school_class = SchoolClass::findOrFail($request->school_class_id);

    foreach ($request->students as $student) {
        $user_id = $student['id'];
        $user = User::find($user_id);
        if (!$user || $user->type != 'student') {
            return response()->json(['message' => 'işlem başarısız.'], 400);
        }
        $class_student = new ClassStudent([
            'user_id' => $user_id,
            'school_class_id' => $request->school_class_id,
        ]);
        $class_student->save();
    }
    return response()->json(['message' => 'İşlem başarılı.']);
}


    /**
     * Attach teachers to a school class.
     */

    public function attachTeacherToClass(Request $request)
    {
    $school_class = SchoolClass::findOrFail($request->school_class_id);

    foreach ($request->teachers as $teacher) {
        $user_id = $teacher['id'];
        $user = User::find($user_id);
        if (!$user || $user->type != 'teacher') {
            return response()->json(['message' => 'İşlem başarısız.'], 400);
        }
        $class_teacher = ClassTeacher::where('user_id', $user_id)->where('school_class_id', $request->school_class_id)->first();
        if ($class_teacher) {
            return response()->json(['message' => 'İşlem başarısız.'], 400);
        }
        $class_teacher = new ClassTeacher([
            'user_id' => $user_id,
            'school_class_id' => $request->school_class_id,
        ]);
        $class_teacher->save();
    }
    return response()->json(['message' => 'İşlem başarılı.']);
}



   /**
 * Remove the specified resource from storage.
 */
public function destroy(string $id)
{
    $deleted = User::destroy($id);

    if ($deleted) {
        return response()->json(['message' => 'Silme Başarili!']);
    } else {
        return response()->json(['message' => 'Silme Başarisiz!']);
    }
}

}
