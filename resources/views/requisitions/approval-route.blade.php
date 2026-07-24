@extends('layouts.master')

@section('content')

<h2>Approval Route</h2>

<p>PR #{{ $id }}</p>

<a
href="{{ route('approval.queue') }}"
class="btn btn-primary">

Start for Approval

</a>

@endsection