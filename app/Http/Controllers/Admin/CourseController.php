<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Livewire\WithPagination;

class CourseController extends Controller
{

  use WithPagination;

  public function index()
  {

    $courses = Course::where('status', 2)->paginate(6);

    return view('admin.courses.index', compact('courses'));
  }

  public function show(Course $course)
  {
    $this->authorize('revision', $course);

    return view('admin.courses.show', compact('course'));
  }

  public function approved(Course $course)
  {
    $this->authorize('revision', $course);

    if (!$course->lessons || !$course->goals || !$course->requirements || !$course->image) {
      return back()->with('info', 'No se puede publicar un curso que aún no este completo.');
    }

    $course->status = 3;
    $course->save();

    return redirect()->route('admin.courses.index')->with('info', 'Se ha publicado el curso correctamente.');
  }
}
