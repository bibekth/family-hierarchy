@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="">
                <div class="card">
                    <div class="card-header d-flex justify-content-between"><span>{{ __('People you have added.') }}</span><span><a href="{{ route('hierarchy.create') }}"><button class="btn btn-sm btn-primary">Add New</button></a></span></div>

                    <div class="card-body">
                        <table class="table table-bordered table-hover">
                            <thead class="thead">
                                <tr>
                                    <th>SN</th>
                                    <th>Name</th>
                                    <th>Gender</th>
                                    <th>Date of Birth</th>
                                    <th>Date of Death</th>
                                    <th>Father</th>
                                    <th>Mother</th>
                                    <th>Spouse</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody class="tbody">
                                @foreach ($data as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->sex ? ($item->sex === 'M' ? 'Male' : 'Female') : 'N/A' }}</td>
                                        <td>{{ $item->dob ?? 'N/A' }}</td>
                                        <td>{{ $item->dod ?? 'N/A' }}</td>
                                        <td>{{ $item->father_id ? $item->father->name : 'N/A' }}</td>
                                        <td>{{ $item->mother_id ? $item->mother->name : 'N/A' }}</td>
                                        <td>{{ $item->spouse_id ? $item->spouse->name : 'N/A' }}</td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('hierarchy.edit', $item->id) }}">
                                                    <button class="btn btn-sm btn-secondary" onclick="return confirm('Are you sure?')">Edit</button>
                                                </a>
                                                <form action="{{ route('hierarchy.destroy', $item->id) }}" method="post">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                                </form>
                                            </div>
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
@endsection
