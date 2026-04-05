@extends('backend.app')

@section('title', 'Social Links')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">

            <div class="main-container container-fluid">

                <div class="page-header">
                    <div>
                        <h1 class="page-title">Social Settings</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Settings</a></li>
                            <span>&nbsp; &gt&gt &nbsp;</span>
                            <li class="breadcrumb-item active" aria-current="page">Social</li>
                        </ol>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">

                        <div class="card">
                            <div class="card-body">

                                <div class="p-4 mb-4 rounded-1" style="background:#f8f9fb; border:1px solid #e9ecef;">
                                    <form method="POST" action="{{ route('admin.social.links.store') }}">
                                        @csrf

                                        <div class="row g-3 align-items-end">

                                            <!-- Platform -->
                                            <div class="col-md-3">
                                                <label class="form-label">Platform</label>
                                                <select class="form-control" name="name">
                                                    <option value="">Select Platform</option>
                                                    <option value="facebook">Facebook</option>
                                                    <option value="twitter">Twitter</option>
                                                    <option value="instagram">Instagram</option>
                                                    <option value="linkedin">LinkedIn</option>
                                                    <option value="youtube">YouTube</option>
                                                    <option value="tiktok">TikTok</option>
                                                    <option value="pinterest">Pinterest</option>
                                                    <option value="whatsapp">WhatsApp</option>
                                                </select>
                                            </div>

                                            <!-- URL -->
                                            <div class="col-md-6">
                                                <label class="form-label">Profile URL</label>
                                                <input type="text" name="url" class="form-control"
                                                    placeholder="https://facebook.com/yourpage">
                                            </div>

                                            <!-- Status -->
                                            <div class="col-md-2">
                                                <label class="form-label">Status</label>
                                                <select name="status" class="form-control">
                                                    <option value="active">Active</option>
                                                    <option value="inactive">Inactive</option>
                                                </select>
                                            </div>

                                            <!-- Button -->
                                            <div class="col-md-1">
                                                <button type="submit" class="btn btn-primary w-100">
                                                    Add
                                                </button>
                                            </div>

                                        </div>
                                    </form>
                                </div>

                                <hr>

                                <table class="table table-bordered">

                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>URL</th>
                                            <th>Status</th>
                                            <th width="150">Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        @foreach ($socialLinks as $link)
                                            <tr>

                                                <td>{{ ucfirst($link->name) }}</td>
                                                <td>{{ $link->url }}</td>

                                                <td>
                                                    @if ($link->status == 'active')
                                                        <span class="badge bg-success">Active</span>
                                                    @else
                                                        <span class="badge bg-danger">Inactive</span>
                                                    @endif
                                                </td>

                                                <td>

                                                    <form action="{{ route('admin.social.links.delete', $link->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('DELETE')

                                                        <button class="btn btn-danger btn-sm">
                                                            Delete
                                                        </button>

                                                    </form>

                                                </td>

                                            </tr>
                                        @endforeach

                                    </tbody>

                                </table>

                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
