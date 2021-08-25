<?php

namespace App\Http\Livewire;

use App\Models\Category;
use App\Models\Course;
use App\Models\Level;
use Livewire\Component;
use Livewire\WithPagination;

class CourseIndex extends Component
{
  use WithPagination;

  public $category_id;
  public $level_id;
  public $state_id;

  public function render()
  {
    $courses = Course::status($this->state_id)
      ->category($this->category_id)
      ->level($this->level_id)
      ->latest('id')
      ->paginate(8);

    $categories = Category::all();
    $levels = Level::all();
    $states = ['Borrador', 'Revision', 'Publicado'];

    return view('livewire.course-index', compact('courses', 'categories', 'levels', 'states'));
  }

  public function resetFilters()
  {
    $this->reset(['category_id', 'level_id', 'state_id']);
  }
}
