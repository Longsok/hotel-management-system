@extends('layouts.app')

@section('title', 'Edit Room ' . $room->room_number)
@section('page-title', 'Edit Room ' . $room->room_number)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('rooms.index') }}">Rooms</a></li>
    <li class="breadcrumb-item"><a href="{{ route('rooms.show', $room) }}">{{ $room->room_number }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@push('styles')
<style>
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .form-label {
        display: block; font-size: 13px; font-weight: 600;
        color: #374151; margin-bottom: 6px;
    }
    .form-label .req { color: #ef4444; margin-left: 2px; }
    .form-control-custom, .form-select-custom {
        width: 100%; padding: 10px 13px;
        border: 1.5px solid #e5e7eb; border-radius: 9px;
        font-family: inherit; font-size: 13.5px; color: #111827;
        background: #f9fafb; outline: none;
        transition: border-color .2s, box-shadow .2s, background .2s;
    }
    .form-control-custom:focus, .form-select-custom:focus {
        border-color: #1a56db; background: #fff;
        box-shadow: 0 0 0 3px rgba(26,86,219,.09);
    }
    .form-control-custom.is-invalid { border-color: #ef4444; background: #fff5f5; }
    .err-msg { font-size: 12px; color: #ef4444; margin-top: 4px; display: block; }
    textarea.form-control-custom { resize: vertical; min-height: 90px; }

    .amenity-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 8px; }
    .amenity-check {
        display: flex; align-items: center; gap: 9px;
        padding: 9px 12px; border: 1.5px solid #e5e7eb;
        border-radius: 8px; cursor: pointer;
        transition: all .15s; background: #f9fafb;
    }
    .amenity-check:hover { border-color: #93c5fd; background: #eff6ff; }
    .amenity-check input[type=checkbox] { accent-color: #1a56db; width: 15px; height: 15px; }
    .amenity-check.checked { border-color: #1a56db; background: #eff6ff; }
    .amenity-check .ac-name { font-size: 13px; font-weight: 500; color: #374151; }

    .section-title {
        font-size: 13px; font-weight: 700; color: #374151; letter-spacing: .3px;
        margin-bottom: 14px; padding-bottom: 10px; border-bottom: 1px solid #e5e7eb;
        display: flex; align-items: center; gap: 7px;
    }
    .btn-cancel {
        padding: 10px 20px; border-radius: 9px; border: 1.5px solid #e5e7eb;
        background: #fff; color: #374151; font-family: inherit; font-size: 14px;
        font-weight: 600; cursor: pointer; text-decoration: none;
        display: inline-flex; align-items: center; gap: 7px; transition: all .2s;
    }
    .btn-cancel:hover { border-color: #d1d5db; color: #111827; }
    .btn-save {
        padding: 10px 24px; border-radius: 9px; border: none;
        background: #1a56db; color: #fff; font-family: inherit; font-size: 14px;
        font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 7px;
        box-shadow: 0 2px 10px rgba(26,86,219,.25); transition: background .2s, transform .15s;
    }
    .btn-save:hover { background: #1447b5; transform: translateY(-1px); }
</style>
@endpush

@section('content')

<div style="max-width: 820px;">
    <form action="{{ route('rooms.update', $room) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Basic Info --}}
        <div class="card mb-4">
            <div class="card-body p-4">
                <div class="section-title">
                    <i class="fas fa-door-open" style="color:#1a56db;"></i> Room Information
                </div>
                <div class="form-grid">

                    <div>
                        <label class="form-label">Room Number <span class="req">*</span></label>
                        <input type="text" name="room_number"
                               class="form-control-custom {{ $errors->has('room_number') ? 'is-invalid' : '' }}"
                               value="{{ old('room_number', $room->room_number) }}">
                        @error('room_number') <span class="err-msg">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="form-label">Floor <span class="req">*</span></label>
                        <select name="floor" class="form-select-custom">
                            @foreach($floors as $fl)
                                <option value="{{ $fl }}" {{ old('floor', $room->floor) == $fl ? 'selected' : '' }}>
                                    Floor {{ $fl }}
                                </option>
                            @endforeach
                        </select>
                        @error('floor') <span class="err-msg">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="form-label">Room Type <span class="req">*</span></label>
                        <select name="room_type_id" class="form-select-custom">
                            @foreach($roomTypes as $rt)
                                <option value="{{ $rt->id }}" {{ old('room_type_id', $room->room_type_id) == $rt->id ? 'selected' : '' }}>
                                    {{ $rt->name }} – ${{ number_format($rt->base_price, 0) }}/night
                                </option>
                            @endforeach
                        </select>
                        @error('room_type_id') <span class="err-msg">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select-custom">
                            @foreach($statuses as $st)
                                <option value="{{ $st }}" {{ old('status', $room->status) === $st ? 'selected' : '' }}>
                                    {{ ucfirst($st) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div style="grid-column: span 2;">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control-custom">{{ old('notes', $room->notes) }}</textarea>
                    </div>

                    <div style="grid-column: span 2;">
                        <label class="form-label">Room Photo</label>
                        @if($room->image)
                            <div style="margin-bottom:10px;">
                                <img src="{{ asset('storage/' . $room->image) }}"
                                     alt="Room photo"
                                     style="width:160px;height:110px;object-fit:cover;border-radius:8px;border:1.5px solid #e5e7eb;">
                                <div style="margin-top:6px;">
                                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:#dc2626;cursor:pointer;">
                                        <input type="checkbox" name="remove_image" value="1"> Remove current photo
                                    </label>
                                </div>
                            </div>
                        @endif
                        <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
                               class="form-control-custom {{ $errors->has('image') ? 'is-invalid' : '' }}"
                               style="padding:8px 13px; cursor:pointer;">
                        @error('image') <span class="err-msg">{{ $message }}</span> @enderror
                        <p style="font-size:12px;color:#9ca3af;margin-top:5px;">
                            {{ $room->image ? 'Upload a new photo to replace the current one.' : 'JPG, PNG or WebP · max 2 MB' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Amenities --}}
        @if($amenities->isNotEmpty())
            <div class="card mb-4">
                <div class="card-body p-4">
                    <div class="section-title">
                        <i class="fas fa-concierge-bell" style="color:#1a56db;"></i> Amenities
                    </div>
                    @php $currentAmenityIds = old('amenity_ids', $room->amenities->pluck('id')->toArray()); @endphp
                    <div class="amenity-grid">
                        @foreach($amenities as $am)
                            <label class="amenity-check {{ in_array($am->id, $currentAmenityIds) ? 'checked' : '' }}">
                                <input type="checkbox" name="amenity_ids[]" value="{{ $am->id }}"
                                       {{ in_array($am->id, $currentAmenityIds) ? 'checked' : '' }}
                                       onchange="this.closest('.amenity-check').classList.toggle('checked', this.checked)">
                                <span class="ac-name">
                                    <i class="{{ $am->icon ?? 'fas fa-check' }}" style="margin-right:4px;color:#6b7280;font-size:12px;"></i>
                                    {{ $am->name }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Actions: Save button inside the update form --}}
        <div style="display:flex;justify-content:flex-end;gap:10px;">
            <a href="{{ route('rooms.show', $room) }}" class="btn-cancel">
                <i class="fas fa-times"></i> Cancel
            </a>
            <button type="submit" class="btn-save">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </div>

    </form>{{-- END update form --}}

    {{-- Delete form is OUTSIDE the update form to avoid _method collision --}}
    @if(auth()->user()->isAdmin())
    <div style="margin-top:16px;">
        <form action="{{ route('rooms.destroy', $room) }}" method="POST"
              onsubmit="return confirm('Permanently delete room {{ $room->room_number }}?')">
            @csrf @method('DELETE')
            <button type="submit"
                    style="padding:10px 16px;border-radius:9px;border:1.5px solid #fca5a5;background:#fff;color:#dc2626;font-family:inherit;font-size:13.5px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;transition:background .15s;"
                    onmouseover="this.style.background='#fef2f2'"
                    onmouseout="this.style.background='#fff'">
                <i class="fas fa-trash"></i> Delete Room
            </button>
        </form>
    </div>
    @endif
</div>

@endsection
