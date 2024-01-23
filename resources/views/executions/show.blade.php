@extends('statamic::layout')
@section('title', $title)
@section('wrapper_class', 'max-w-3xl')

@section('content')

    <header class="mb-6">
        @include('statamic::partials.breadcrumb', [
            'url' => $breadcrumbs[0]['url'],
            'title' => $breadcrumbs[0]['text']
        ])
        <div class="flex items-center justify-between">
            <h1>{{ __('View Execution :id', ['id' => $record['id']]) }}</h1>
        </div>
    </header>

    <div class="card p-0">
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ __('When') }}</th>
                    <th>{{ __('Description') }}</th>
                    <th>{{ __('Data') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($record->logs()->orderBy('created_at')->get() as $log)
                    <tr>
                        <td class="flex items-center">
                            <div class="text-gray-800 leading-none">{{ $log->created_at->format('d/m/Y H:i:s') }}</div>
                        </td>
                        <td>
                            {{ $log->description }}
                        </td>
                        <td>
                            <pre>{{ json_encode($log->properties) }}</pre>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
        </table>
    </div>

@endsection
