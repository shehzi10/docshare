@extends('admin.layout.app')
@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Users</h4>
                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="table-rep-plugin">
                                    <div class="table-responsive mb-0" data-pattern="priority-columns">
                                        <table id="tech-companies-1" class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Username</th>
                                                    <th>Image</th>
                                                    <th>Email</th>
                                                    <th data-priority="1">Number Of Documents</th>
                                                    <th data-priority="6">Actions</th>

                                                    <th> </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($users as $user)
                                                @php
                                                    $img  = asset(!empty($user->profile_pic)) ? asset('publi/images/' . $user->profile_pic) : assets('public/images/default.jpg')
                                                @endphp
                                                    <tr>
                                                        <th>{{ $user->username }}</th>
                                                        <th><img src="{{ $img }}" alt="user"
                                                                class="avatar-sm"></th>
                                                        <td>{{ $user->email }}</td>
                                                        <td>7</td>
                                                        <td><select class="form-control" name="status" style="width: 50%">
                                                                <option selected="" value="active">Activate</option>
                                                                <option value="deActive">Deactivate</option>
                                                            </select></td>
                                                    </tr>
                                                @endforeach

                                            </tbody>
                                        </table>
                                    </div>

                                </div>

                            </div>
                        </div>
                    </div> <!-- end col -->
                </div> <!-- end row -->
            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->
    </div>
@endsection
