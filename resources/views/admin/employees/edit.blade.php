@extends('layouts.admin')
@section('content')
        <div class="page-wrapper">
            <div class="page-content">              
                <livewire:edit-employee :employeeId="$employee->id" />
              </div>
        </div>
@endsection
