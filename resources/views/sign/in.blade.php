@extends('layouts.main')
@section('body')
  <div class="w-full flex flex-col justify-center items-center">
    <div class="py-12">
      <h1>@lang('Signin')</h1>
    </div>
    <form action="{{route('sign.ing-in')}}" method="post" class="grid grid-cols-1 gap-4 w-1/3">
      @csrf
      <label for="email"><h2>@lang('Email')</h2></label>
      <input type="text" name="email" id="email"/>
      <label for="password"><h2>@lang('Password')</h2></label>
      <input type="password" name="password" id="password" minlength="{{$password_min_length}}" maxlength="{{$password_max_length}}"/>
      <button type="submit" class="btn good">@lang('Sign in')</button>
    </form>
    <div class="p-4">
      <h2 class="text-2xl dark:text-white">@lang("Don't have an account")? <a href="{{route('sign.up')}}" class="dark:text-blue-500">@lang('Create one')</a>!</h2>
    </div>
    <div class="p-4">
      <h2 class="text-2xl dark:text-white">@lang("Forgot your password")? <a href="{{route('recover_password_anonymously')}}" class="dark:text-blue-500">@lang('Recover it')</a>!</h2>
    </div>
    <div>
      <h1 class="text-xl text-white">{{session('message')}}</h1>
    </div>
  </div>
@endsection
