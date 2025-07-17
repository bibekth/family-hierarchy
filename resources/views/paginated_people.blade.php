@foreach ($data as $item)
    <div class="flex flex-col items-center">
        <a href="{{ route('hierarchy.display', $item->slug) }}" target="_blank">
            <div class="relative w-24 h-24 sm:w-36 sm:h-36 md:w-40 md:h-40 rounded-full overflow-hidden border-4"
                style="border-color: {{ $item->sex === 'Male' ? '#2980b9' : ($item->sex === 'Female' ? '#c0392b' : '#ffffff') }}; background-color: {{ $item->sex === 'Male' ? '#3498db' : ($item->sex === 'Female' ? '#e91e63' : '#6A727F') }};">
                @if ($item->avatar)
                    <img src="{{ $item->avatar }}" alt="{{ $item->name }}"
                        class="absolute inset-0 w-full h-full object-cover" />
                @endif
            </div>
        </a>
        <span class="mt-2 text-sm text-center">{{ $item->name }}</span>
    </div>
@endforeach
