@props([
    'field' => null,
    'message' => null
])

@unless(empty($field))
    @error($field)
        <p {{ $attributes->class(['text-xs font-semibold text-rose-600 mt-1 flex items-center gap-1']) }}>
            <svg class="w-3.5 h-3.5 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ $message }}</span>
        </p>
    @enderror
@else
    @unless(empty($message))
        <p {{ $attributes->class(['text-xs font-semibold text-rose-600 mt-1 flex items-center gap-1']) }}>
            <svg class="w-3.5 h-3.5 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ $message }}</span>
        </p>
    @endunless
@endunless
