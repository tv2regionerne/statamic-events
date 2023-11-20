@extends('statamic::layout')
@section('title', $title)
@section('wrapper_class', 'max-w-3xl')

@section('content')
    <statamic-events-publish-form
        :breadcrumbs="{{ $breadcrumbs->toJson() }}"
        :initial-blueprint='@json($blueprint)'
        :initial-meta='@json($meta)'
        :initial-values='@json($values)'
        initial-title="{{ $title }}"
        action="{{ $action }}"
        method="{{ $method }}"
        :is-creating="true"
        publish-container="base"
        create-another-url="{{ cp_route('statamic-events.create') }}"
        listing-url="{{ cp_route('statamic-events.index') }}"
    ></statamic-events-publish-form>
@endsection
