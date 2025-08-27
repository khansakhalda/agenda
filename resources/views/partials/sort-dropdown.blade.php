{{-- resources/views/partials/sort-dropdown.blade.php --}}
<div x-data="{ open: false }" class="relative">
  <button @click="open = !open" class="text-gray-600 hover:text-black focus:outline-none" aria-label="Urutkan">
    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
      <path d="M10 3a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm0 5a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm0 5a1.5 1.5 0 110 3 1.5 1.5 0 010-3z" />
    </svg>
  </button>

  <div
    x-show="open"
    @click.away="open = false"
    x-transition:enter="transition ease-out duration-150"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-100"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    class="absolute right-0 mt-2 w-56 bg-white border rounded-xl shadow-xl z-50 text-sm overflow-hidden origin-top-right"
  >
    <div class="px-4 py-3 text-gray-500 font-semibold border-b">Urutkan</div>

    @php
      $sortOptions = [
        'manual'  => 'Urutan saya',
        'date'    => 'Tanggal',
        'starred' => 'Berbintang terbaru',
        'title'   => 'Judul',
      ];
    @endphp

    @foreach ($sortOptions as $value => $label)
      <button
        @click="open = false"
        wire:click="setSort('{{ $value }}')"
        class="w-full flex items-center justify-between px-4 py-2 text-left hover:bg-gray-100 transition"
        :class="{ 'bg-gray-100 font-medium': '{{ $sortBy }}' === '{{ $value }}' }"
      >
        <span class="{{ $sortBy === $value ? 'text-gray-900 font-medium' : 'text-gray-700' }}">
          {{ $label }}
        </span>
        @if ($sortBy === $value)
          <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
          </svg>
        @endif
      </button>
    @endforeach
  </div>
</div>
