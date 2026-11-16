@extends('layouts.librenmsv1')
@section('title', 'LibreNMS Utility Suite')
@section('content')
<div class="container-fluid"><h2>LibreNMS Utility Suite</h2><div class="row">
@forelse($modules ?? [] as $module)
<div class="col-sm-6 col-md-4"><div class="panel panel-default"><div class="panel-heading"><strong>{{ $module->name() }}</strong></div><div class="panel-body">Enabled</div></div></div>
@empty
<div class="col-md-12"><div class="alert alert-info">No Utility Suite modules are enabled.</div></div>
@endforelse
</div></div>
@endsection
