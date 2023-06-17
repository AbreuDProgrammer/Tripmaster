@extends('layouts.main')
@section('body')
  <main class="justify-items-center">
    <h1>@lang('Manage Money')</h1>
    <h3>@lang('Current balance'): {{ $balance }}€</h3>
    <a href="{{route('account.money.adder')}}" class="btn good"><button>Add money</button></a>
    <a href="{{route('account.money.remover')}}" class="btn danger"><button>Remove money</button></a>
    <a href="{{route('account.index')}}" class="btn okay"><button>Go back</button></a>
  </main>
@endsection
