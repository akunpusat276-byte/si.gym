@extends('layouts.app')
@section('title', 'Detail Membership')
@section('content')
<div class="card"><div class="card-body"><h2>Membership {{ $membership->member->user->name }}</h2><p>Status: {{ $membership->calculated_status }}</p><p>Sisa: {{ $membership->remaining_days }} hari</p></div></div>
@endsection
