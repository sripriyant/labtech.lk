@extends('layouts.admin')

@section('content')
    <div class="card">
        <h2 style="margin:0 0 8px;">{{ $pageTitle ?? 'Module' }}</h2>
        <p style="margin:0;color:var(--muted);">This module is planned. Tell me the fields and workflow to build next.</p>
    </div>
@endsection
