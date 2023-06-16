<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Countries;
use App\Helpers\Image;
use App\Helpers\Data;
use App\Models\User;

class Controller extends BaseController
{
  use AuthorizesRequests, ValidatesRequests;

  // Errors
  const INVALID_TOKEN = "Invalid token";
  const ERROR_500 = "Something went wrong, please try again later";
  
  // Authentification
  const NOT_LOGGED = "You are not logged in";
  const INCORRECT_DATA = "Email or password incorrect";
  const ACCOUNT_CREATED = "Account created successfully, a verification email has been sent to you";

  // Account
  const NOT_THE_ACCOUNT_OWNER = "You are not the owner of that account";
  const PASSWORD_INCORRECT = "Password is incorrect";
  
  // Projects
  const NOT_THE_PROJECT_OWNER = "You are not the owner of that project";
  const NO_PROJECTS_YET = "You don't have a project yet";
  const PROJECT_CREATED = 'Project created';
  const PROJECT_404 = "Project not found";
  const PROJECT_EDITED = 'Project edited';
  
  // Stays
  const NOT_THE_STAY_OWNER = "You are not the owner of that stay";
  const STAY_NOT_RENTED = "You didn't rent that stay";
  const STAY_NOT_AVAILABLE = 'Stay not available';
  const NO_STAYS_YET = "You have no stays yet";
  const STAY_RENTED = 'Stay rented';
  const STAY_404 = 'Stay not found';
  const STAY_CREATED = 'Stay created';
  const STAY_EDITED = 'Stay edited';

  protected Data $data;
  protected Image $image;
  protected Countries $countries;

  public function __construct()
  {
    $this->data = Data::getInstance();
    $this->countries = Countries::getInstance();
    $this->image = Image::getInstance();
  }
  
  protected function view($page)
  {
    $this->data->isLogged(Auth::check());
    $this->data->current($page);
    
    return view($page, $this->data->get());
  }

  protected function getLastProjectOpened($userId)
  {
    $lastProjectOpened = User::where("id", $userId)->first()->lastProjectOpened ?? false;
    if(!$lastProjectOpened) {
      session()->reflash();
      return false;
    }
    return $lastProjectOpened;
  }
}
