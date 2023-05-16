<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Project;

class ProjectsController extends Controller
{
  private $REST_Countries = 'https://restcountries.com/v3.1/all?fields=name';

  public function creator()
  {
    $this->data->title('Create Project');

    $countries = $this->getCountries();
    
    $this->data->set('selected', "France");
    $this->data->set('countries', $countries);

    return $this->view('projects.create');
  }

  public function create(Request $request)
  {
    $valideted = $request->validate([
      'country' => 'required',
      'start' => 'required',
      'end' => 'required',
      'adults' => 'required',
      'children' => 'required',
    ]);

    $project = array(
      'country' => $valideted['country'],
      'start' => $valideted['start'],
      'end' => $valideted['end'],
      'adults' => $valideted['adults'],
      'children' => $valideted['children'],
      'headcount' => $valideted['adults'] + $valideted['children'],
      'image' => $this->getFlag($valideted['country']),
      'isFlag' => true,
      'owner' => Auth::id()
    );
    
    $info = Project::create($project);
    
    if(!$info){
      $request->session()->flash('status', false);
      $request->session()->flash('message', 'Something went wrong, please try again');
      return redirect()->route('creator.project');
    }

    $request->session()->flash('status', true);
    $request->session()->flash('message', 'Project created');
    return redirect()->route('my.list.projects');
  }
  
  public function index()
  {
    $this->data->title('Projects List');

    $user_projects = Project::where('owner', Auth::id())->get();

    $projects = array();

    
    foreach($user_projects as $project_data)
    {
      $project = array(
        'country' => $project_data['country'],
        'start' => date("F", mktime(0, 0, 0, explode('-', $project_data['start'])[1], 1)).' '.explode('-', $project_data['start'])[2],
        'end' => date("F", mktime(0, 0, 0, explode('-', $project_data['end'])[1], 1)).' '.explode('-', $project_data['end'])[2],
        'image' => $project_data['image'],
        'headcount' => $project_data['headcount'],
        'people' => $project_data['headcount'] == 1 ? 'person' : 'people',
      );
      $projects[] = $project;
    }

    $this->data->set('projects', $projects);

    return $this->view('projects.list');
  }

  private function getFlag($country): string
  {
    $country = str_replace(' ', '%20', $country);
    $get_code = "https://restcountries.com/v3.1/name/$country?fields=cca2";
    $data = $this->doCurlURL($get_code);

    $code = $data[0]['cca2'];
    $swaps = array('UM' => 'US');
    foreach($swaps as $key => $value)
      $code = str_replace($key, $value, $code);
      
    $url = "https://flagsapi.com/$code/flat/64.png";
    return $url;
  }

  private function doCurlURL($url)
  {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($curl);
    curl_close($curl);
    $data = json_decode($response, true);
    return $data;
  }
  
  private function getCountries(): array
  {
    $data = $this->doCurlURL($this->REST_Countries);
    $countries_names = array();
    foreach($data as $name)
      $countries_names[] = $name['name']['common'];
    sort($countries_names);
    return $countries_names;
  }
}
