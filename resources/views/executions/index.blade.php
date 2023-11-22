@extends('statamic::layout')
@section('title', $title)
@section('wrapper_class', 'max-w-full')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="flex-1">{{ $title }}</h1>
    </div>

    <statamic-events-listing
        :filters="{{ $filters->toJson() }}"
        :listing-config='@json($listingConfig)'
        :initial-columns='@json($columns)'
        action-url="{{ $actionUrl }}"
        initial-primary-column="{{ $primaryColumn }}"
        :allow-bulk-actions="false"
    ></statamic-events-listing>
@endsection
