<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

class TestController extends Controller
{
  public function index()
  {
    $usuario = User::find(2);
    $courses = Course::has('students')
      ->with('students')
      ->select('id', 'title')
      ->get();


    /* $courses = $courses->students->contains($user); */

    return view('tests.show', compact('courses', 'usuario'));
  }
}
