<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Stays as StaysModel;
use Illuminate\Http\Request;
use App\Models\Stays_Images;
use App\Models\Project;

class Stays extends Controller
{
  public function index()
  {
    $this->data->title('Stays');

    $lastProjectOpened = $this->getLastProjectOpened(Auth::id());
    if(!$lastProjectOpened){
      session()->flash('error', $this::NO_PROJECTS_YET);
      return redirect()->route('projects.creator');
    }
    
    $project = Project::where("id", $lastProjectOpened)->first() ?? false;
    if(!$project){
      session()->flash('error', $this::PROJECT_404);
      return redirect()->route('projects.creator');
    }
      
    $this->data->set('country', $project->country);
    
    $stays = StaysModel::where("country", $project->country)->get();
    for($i = 0; $i < count($stays); $i++)
    {
      $img_path = Stays_Images::where("stay", $stays[$i]->id)->first()->image_path ?? false;
      $stays[$i]->image = $this->image->get('stays/'.$img_path);
    }

    $this->data->set('staySelected', $project->stay);

    $this->data->set('stays', $stays);

    return $this->view('stays.index');
  }

  public function show($id)
  {
    $this->data->title('Stay');

    $stay = StaysModel::where("id", $id)->first() ?? false;
    if(!$stay){
      session()->flash('error', $this::STAY_404);
      return redirect()->route('stays.list');
    }
    $stay->images = array();
    
    $images_path = Stays_Images::where("stay", $stay->id)->get()->toArray() ?? false;
    if($images_path) {
      $images = array();
      foreach($images_path as $image)
        $images[] = $this->image->get('stays/'.$image['image_path']);
      $stay->images = $images;
    } else {
      $images = array();
      $images[] = $this->image->default();
      $stay->images = $images;
    }

    $this->data->set('stay', $stay);

    $this->data->set('backHref', url()->previous());

    return $this->view('stays.stay');
  }
  
  public function rent($id)
  {
    $stay = StaysModel::where("id", $id)->first() ?? false;
    if(!$stay){
      session()->flash('error', $this::STAY_404);
      return redirect()->route('stays.list');
    }

    $lastProjectOpened = $this->getLastProjectOpened(Auth::id());
    if(!$lastProjectOpened){
      session()->flash('error', $this::NO_PROJECTS_YET);
      return redirect()->route('projects.creator');
    }
    
    $project = Project::where("id", $lastProjectOpened)->first() ?? false;
    if(!$project){
      session()->flash('error', $this::PROJECT_404);
      return redirect()->route('projects.creator');
    }

    $this->data->title('Rent');
    $this->data->set("stay", $stay);
    $this->data->set("minDate", $project->start);
    $this->data->set("maxDate", $project->end);
    $this->data->set("maxHeadcount", $project->headcount);

    return $this->view('stays.rent');
  }

  public function list()
  {
    $this->data->title('Listing my Stays');

    $stays = StaysModel::where('owner', '=', Auth::id())->get()->toArray();
    $this->data->set('stays', $stays);

    return $this->view('stays.list');
  }

  public function enable($id)
  {
    $attempt = $this->stay_exists_and_ur_the_owner($id);
    if(!$attempt){
      session()->flash('error', $this::NOT_THE_STAY_OWNER);
      return redirect()->route('stays.list');
    }

    StaysModel::where("id", $id)->update(["status" => "available"]);
    return redirect()->back();
  }

  public function disable($id)
  {
    $attempt = $this->stay_exists_and_ur_the_owner($id);
    if(!$attempt){
      session()->flash('error', $this::NOT_THE_STAY_OWNER);
      return redirect()->route('stays.list');
    }

    StaysModel::where("id", $id)->update(["status" => "disabled"]);
    return redirect()->back();
  }

  public function delete($id)
  {
    $attempt = $this->stay_exists_and_ur_the_owner($id);
    if(!$attempt){
      session()->flash('error', $this::NOT_THE_STAY_OWNER);
      return redirect()->route('stays.list');
    }

    StaysModel::destroy($id);
    return redirect()->back();
  }
  
  public function editor($id)
  {
    $stay = StaysModel::where("id", $id)->first() ?? false;
    if(!$stay){
      session()->flash('error', $this::STAY_404);
      return redirect()->route('stays.list');
    }

    $belongs = $this->stay_exists_and_ur_the_owner($id);
    if(!$belongs){
      session()->flash('error', $this::NOT_THE_STAY_OWNER);
      return redirect()->route('stays.list');
    }
      
    $this->data->title('Edit Stay');
    $this->data->set('owner', Auth::id());
    $this->data->set('page_title', 'Edit Stay');
    $this->data->set('submit_button', 'Update');
    $this->data->set('form_route', route('stays.edit', ['id' => $id]));
    $this->data->set('editing_case', true);
    $countries = $this->countries->getAll();
    $this->data->set('possible_countries', $countries);
    $this->data->set('stay', $stay);
    
    return $this->view('stays.create_and_edit');
  }
  
  public function creator()
  {
    $this->data->title('Create Stay');
    $this->data->set('owner', Auth::id());
    $this->data->set('editing_case', false);
    $this->data->set('form_route', route('stays.create'));
    $this->data->set('submit_button', 'Create');
    $this->data->set('page_title', 'Create Stay');
    $countries = $this->countries->getAll();
    $this->data->set('possible_countries', $countries);
    return $this->view('stays.create_and_edit');
  }
  
  public function edit(Request $request, $id)
  {
    if(Auth::check()){
      session()->flash('error', $this::NOT_LOGGED);
      return redirect()->route('sign.index');
    }

    if(!$this->stay_exists_and_ur_the_owner($id)){
      session()->flash('error', $this::NOT_THE_STAY_OWNER);
      return redirect()->route('stays.list');
    }

    $validated = $request->validate([
      'owner' => 'required',
      'title' => 'required',
      'description' => 'required',
      'capacity' => 'required',
      'bedrooms' => 'required',
      'price' => 'required',
      'country' => 'required',
      'city' => 'required'
    ]);
    
    $saved_stay = StaysModel::where('id', $id)->update($validated);
    
    return redirect()->route('stays.list');
  }
  
  public function create(Request $request)
  {
    $validated = $request->validate([
      'owner' => 'required',
      'title' => 'required',
      'description' => 'required',
      'capacity' => 'required',
      'bedrooms' => 'required',
      'price' => 'required',
      'country' => 'required',
      'city' => 'required'
    ]);
    
    $stay = StaysModel::create($validated);
    if(!$stay){
      session()->flash('error', $this::ERROR_500);
      return redirect()->route('stays.creator');
    }

    $images = $request->file('images');
    if($images)
      foreach($images as $img) {
        $image = array(
          'image_path' => $this->image->set('stays', $img),
          'stay' => $stay['id']
        );
        Stays_Images::create($image);
      }
    
    return redirect()->route('stays.list');
  }

  private function stay_exists_and_ur_the_owner($id)
  {
    $stay_exists = StaysModel::find($id);
    if(!$stay_exists)
      return false;
    
    $stay = $stay_exists->toArray();
    
    $belongs = $stay['owner'] == Auth::id();
    return $belongs;
  }
}
