@extends('layouts.app')

@section('content')
    <div class="container">
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"
            integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <div class="row justify-content-center">
            <div class="">
                <div class="card">
                     <div class="card-header d-flex justify-content-between">
                        <span>{{ __('People you have added.') }}</span><span><a href="{{ route('hierarchy.index') }}"><button
                                    class="btn btn-sm btn-primary">View Table</button></a></span>
                    </div>

                    <div class="card-body d-flex justify-content-center">
                        <form action="{{ route('hierarchy.update', $data->id) }}" method="POST" class="col-8"
                            enctype="multipart/form-data">
                            @csrf @method('PATCH')

                            {{-- Name (always visible) --}}
                            <div class="mb-3">
                                <label for="name">Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $data->name) }}"
                                    class="form-control @error('name') is-invalid @enderror">
                                @error('name')
                                    <span class="text-sa text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Gender --}}
                            <div class="mb-1">
                                <label><input type="checkbox" id="toggle-gender" {{ $data->sex ? 'checked' : '' }}> Do you
                                    know their gender?</label><br>
                            </div>
                            <div class="mb-3 {{ $data->sex ? '' : 'hidden-field' }}" id="gender-field">
                                <label for="sex">Gender</label>
                                <select name="sex" id="sex"
                                    class="form-control @error('sex') is-invalid @enderror">
                                    <option value="">Select gender</option>
                                    <option value="M" @if ($data->sex === 'M') selected @endif>Male</option>
                                    <option value="F" @if ($data->sex === 'F') selected @endif>Female</option>
                                </select>
                                @error('sex')
                                    <span class="text-sa text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- DOB --}}
                            <div class="mb-1">
                                <label><input type="checkbox" id="toggle-dob" {{ $data->dob ? 'checked' : '' }}> Do you know
                                    their date of birth?</label><br>
                            </div>
                            <div class="mb-3 {{ $data->dob ? '' : 'hidden-field' }}" id="dob-field">
                                <label for="dob">Date of Birth</label>
                                <input type="date" name="dob" id="dob" value="{{ old('dob', $data->dob) }}"
                                    class="form-control @error('dob') is-invalid @enderror">
                                @error('dob')
                                    <span class="text-sa text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- DOD --}}
                            <div class="mb-1">
                                <label><input type="checkbox" id="toggle-dod" {{ $data->dod ? 'checked' : '' }}> Do you
                                    know their death date?</label><br>
                            </div>
                            <div class="mb-3 {{ $data->dod ? '' : 'hidden-field' }}" id="dod-field">
                                <label for="dod">Date of Death</label>
                                <input type="date" name="dod" id="dod" value="{{ old('dod', $data->dod) }}"
                                    class="form-control @error('dod') is-invalid @enderror">
                                @error('dod')
                                    <span class="text-sa text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Profile Image --}}
                            <div class="mb-1">
                                <label><input type="checkbox" id="toggle-image" {{ ($data->avatar !== null) ? 'checked' : '' }}> Do
                                    you know have their image?</label><br>
                            </div>
                            <div class="mb-3 {{ ($data->avatar !== null) ? '' : 'hidden-field' }}" id="image-field">
                                <label for="avatar">Profile Image</label>
                                <input type="file" name="avatar" id="avatar"
                                    class="form-control @error('avatar') is-invalid @enderror">
                                @error('avatar')
                                    <span class="text-sa text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Father --}}
                            <div class="mb-1">
                                <label><input type="checkbox" id="toggle-father" {{ $data->father_id ? 'checked' : '' }}>
                                    Do you know their father?</label><br>
                            </div>
                            <div class="mb-3 {{ $data->faher_id ? '' : 'hidden-field' }}" id="father-field">
                                <label for="father_id">Father's Name</label><br>
                                <select id="father_id" name="father_id"
                                    class="form-control select2-ajax @error('father_id') is-invalid @enderror"
                                    data-placeholder="Search for a person"></select>
                                @error('father_id')
                                    <span class="text-sa text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Mother --}}
                            <div class="mb-1">
                                <label><input type="checkbox" id="toggle-mother" {{ $data->mother_id ? 'checked' : '' }}>
                                    Do you know their mother?</label><br>
                            </div>
                            <div class="mb-3 {{ $data->mother_id ? '' : 'hidden-field' }}" id="mother-field">
                                <label for="mother">Mother's Name</label>
                                <select id="mother_id" name="mother_id"
                                    class="form-control select2-ajax @error('mother_id') is-invalid @enderror"
                                    data-placeholder="Search for a person"></select>
                                @error('mother_id')
                                    <span class="text-sa text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Spouse --}}
                            <div class="mb-1">
                                <label><input type="checkbox" id="toggle-spouse" {{ $data->spouse_id ? 'checked' : '' }}>
                                    Do you know their spouse?</label><br>
                            </div>
                            <div class="mb-3 {{ $data->spouse_id ? '' : 'hidden-field' }}" id="spouse-field">
                                <label for="spouse">Spouse's Name</label>
                                <select id="spouse_id" name="spouse_id"
                                    class="form-control select2-ajax @error('spouse_id') is-invalid @enderror"
                                    data-placeholder="Search for a person"></select>
                                @error('spouse_id')
                                    <span class="text-sa text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary my-3">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const toggles = {
                    "toggle-gender": "gender-field",
                    "toggle-dob": "dob-field",
                    "toggle-dod": "dod-field",
                    "toggle-image": "image-field",
                    "toggle-father": "father-field",
                    "toggle-mother": "mother-field",
                    "toggle-spouse": "spouse-field"
                };

                for (let toggleId in toggles) {
                    const checkbox = document.getElementById(toggleId);
                    const field = document.getElementById(toggles[toggleId]);

                    checkbox.addEventListener("change", function() {
                        field.style.display = this.checked ? "block" : "none";
                    });

                    // Initially hide all toggle fields
                    // field.style.display = "none";
                }
            });

            $(document).ready(function() {
                $('.select2-ajax').select2({
                    placeholder: $(this).data('placeholder') || 'Search...',
                    ajax: {
                        url: '/hierarchy-search/',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                name: params.term
                            };
                        },
                        processResults: function(data) {
                            return {
                                results: data.map(user => ({
                                    id: user.id,
                                    text: user.name
                                }))
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 1
                });
            });
        </script>
        <style>
            .hidden-field {
                display: none;
            }

            .select2-container {
                width: 100% !important;
            }

            .select2-container--open .select2-dropdown--above {
                background-color: #212529;
            }

            .select2-container--default .select2-selection--single .select2-selection__rendered {
                background-color: #212529;
            }

            .select2-container--default .select2-selection--single .select2-selection__rendered {
                color: white;
            }
        </style>
    </div>
@endsection
