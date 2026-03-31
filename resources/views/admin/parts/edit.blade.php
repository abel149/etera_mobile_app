@extends('layouts.admin')
@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">              
        <div class="card">
            <div class="card-body p-4">
                <h5 class="card-title">Edit Spare Part</h5>
                <hr/>
                <form class="row g-3" action="{{ route('parts.update', $carPart->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="col-md-12">
                        <label for="input1" class="form-label">Name</label>
                        <input name="name" value="{{ old('name', $carPart->name) }}" type="text" class="form-control" id="input1" placeholder="Enter The Spare Part...">
                        @error('name')
                            <span class="text-danger">{{$message}}</span>
                        @enderror
                    </div>

                    <div class="col-md-12">
                        <label for="component" class="form-label">Component</label>
                        <select name="component" id="component" class="form-control">
                            <option value="Body Parts (Inner)" {{ $carPart->component == 'Body Parts (Inner)' ? 'selected' : '' }}>Body Parts (Inner)</option>
                            <option value="Body Parts (Outer)" {{ $carPart->component == 'Body Parts (Outer)' ? 'selected' : '' }}>Body Parts (Outer)</option>
                            <option value="Mechanical Parts" {{ $carPart->component == 'Mechanical Parts' ? 'selected' : '' }}>Mechanical Parts</option>
                        </select>
                        @error('component')
                            <span class="text-danger">{{$message}}</span>
                        @enderror
                    </div>

                    <div class="pt-3">
                        <hr/>
                        <button type="submit" class="btn btn-primary radius-30 px-4" onclick="notification('Car Part Updated Successfully')"> Update
                        </button>
                        &nbsp
                        <a href="/admin/parts" type="button" class="btn btn-outline-secondary radius-30 px-3"> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--end page wrapper -->
@endsection
