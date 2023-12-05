<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentValidator;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(): Renderable
    {
        $student = Student::findOrFail(auth()->user()->student->id);
        return view('student.edit', compact('student'));
    }

    public function update(Student $student, Request $request)
    {
        if ($request->has('replace_award_letter')){
            $attributes = $request->validate([
                'award_letter' => ['required', 'file', 'mimes:png,jpg,pdf', 'max:300'],
            ]);
            if (!empty($attributes['award_letter']) && is_file($attributes['award_letter'])) {
                if ($student->award_letter) {
                    $file = $student->app_id . "/" . $student->award_letter;
                    deleteFiles($file);
                }
                $file = $attributes['award_letter'];
                $fileName = 'award_letter__' . rand(1, 99) . '.' . $file->getClientOriginalExtension();
                $file->storeAs($student->app_id, $fileName, 'public');
                $student->award_letter = $fileName;
                $student->is_updated = 2;
                $student->save();
            }
            return  redirect()->back()->with('status', 'Data has been successfully updated. Please double check the file.');
        }
        $attributes = (new StudentValidator())->validate($student, $request->all());
        updateStudent($student, $attributes);
        return back()->with('status', 'Data has been successfully updated.');
    }
}
