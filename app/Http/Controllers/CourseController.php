<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
  public function index()
  {
    return view('courses.index');
  }
  public function show(Course $course)
  {
    $states = ['Borrador', 'Revision', 'Publicado'];

    $similares = Course::where('category_id', $course->category_id)
      ->where('id', '!=', $course->id)
      ->where('status', 3)
      ->latest('id')
      ->take(5)
      ->get();
    return view('courses.show', compact('course', 'states', 'similares'));
  }
  public function enrolled(Course $course)
  {
    $course->students()->attach(auth()->user()->id);
    return redirect()->route('courses.status', $course);
  }
}
