@extends('layouts.app')

@section('title', 'Activity Log')

@section('content')
    <x-profilepersonalization.activity-log-page
        :activity-groups="$activityGroups"
        :selected-year="$selectedYear"
        :selected-type="$selectedType"
        :years="$years"
        :type-options="$typeOptions"
    />
@endsection
