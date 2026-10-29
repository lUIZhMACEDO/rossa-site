@extends('study.summary.layout')

@section('tab-content')
    @livewire('data-exploration', ['study' => $study], key('data-exploration-' . $study->id))
@endsection
