@extends('buildora::layouts.buildora')

@section('content')

    @include($view, [
        'resource' => $resource,
        'item' => $item,
        'fields' => $fields,
        'model' => $model,
    ])
@endsection
