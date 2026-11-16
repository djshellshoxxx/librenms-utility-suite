@extends('layouts.librenmsv1')
@section('title', 'Utility Suite Settings')
@section('content')
<div class="container-fluid"><h2>Utility Suite Settings</h2>
<form method="POST" action="{{ route('librenms-utility-suite.settings.update') }}">@csrf
@foreach($modules as $key => $module)
<div class="checkbox"><label><input type="checkbox" name="modules[{{ $key }}]" value="1" @checked(app(\Djshellshoxxx\LibreNMSUtilitySuite\Services\SettingsService::class)->moduleEnabled($key))> {{ $module['name'] }}</label></div>
@endforeach
<button class="btn btn-primary" type="submit">Save changes</button></form></div>
@endsection
