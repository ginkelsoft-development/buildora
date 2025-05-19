@extends('buildora::layouts.buildora')

@section('content')
    <div class="grid grid-cols-12 gap-6">
        @foreach($widgets as $widget)
            <div class="@foreach($widget->getColSpan() as $break => $span) {{ $break === 'default' ? "col-span-$span" : "$break:col-span-$span" }} @endforeach">
                {!! $widget->render() !!}
            </div>
        @endforeach
    </div>
@endsection
