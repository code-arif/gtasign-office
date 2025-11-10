@extends('backend.app', ['title' => 'Edit Plan Feature'])

@section('content')
<div class="app-content main-content mt-0">
    <div class="side-app">
        <div class="main-container container-fluid">

            {{-- PAGE HEADER --}}
       <div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-0">Edit Plan Feature</h1>
    </div>
    <div class="d-flex align-items-center">
        <nav aria-label="breadcrumb" class="me-3">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="#">Subscription</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Feature</li>
            </ol>
        </nav>
        <a href="{{ route('admin.planfeatures.index') }}" class="btn btn-secondary">
            <i class="fa fa-arrow-left me-1"></i> Back to List
        </a>
    </div>
</div>

            {{-- PAGE HEADER --}}

            <div class="row">
                <div class="col-lg-6 mx-auto">
                    <div class="card box-shadow-0">
                        <div class="card-body">
                            <form action="{{ route('admin.planfeatures.update', $planfeature->id) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label for="plan_id" class="form-label">Select Plan</label>
                                    <select name="plan_id" id="plan_id" class="form-select" required>
                                        <option value="">-- Select Plan --</option>
                                        @foreach($plans as $plan)
                                            <option value="{{ $plan->id }}" {{ (old('plan_id', $planfeature->plan_id) == $plan->id) ? 'selected' : '' }}>
                                                {{ $plan->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('plan_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="feature" class="form-label">Feature</label>
                                    <input type="text" name="feature" id="feature" class="form-control" min="0" value="{{ old('feature', $planfeature->feature) }}" required>
                                    @error('feature')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-save me-1"></i> Update Feature
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
