{{-- resources/views/partials/header.blade.php --}}
@php
    \Carbon\Carbon::setLocale('id');
@endphp

<div class="mb-5">
  <div class="flex items-center justify-between gap-3">
    {{-- LEFT: Navigasi & label tanggal + switcher view --}}
    <div class="flex items-center gap-2">
      <button wire:click="goToToday"
        class="px-3 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
        Hari Ini
      </button>

      <button wire:click="previousPeriod" class="p-2 rounded hover:bg-gray-100" aria-label="Sebelumnya">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
        </svg>
      </button>

      <button wire:click="nextPeriod" class="p-2 rounded hover:bg-gray-100" aria-label="Berikutnya">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
        </svg>
      </button>

      <span class="ml-2 text-lg font-semibold text-gray-800">
        {{ \Carbon\Carbon::parse($this->currentDate)->translatedFormat('l, d F Y') }}
      </span>

      {{-- View switcher --}}
      <div class="ml-3 flex bg-gray-100 rounded-lg p-0.5">
        <button wire:click="setView('month')"
          class="px-3 py-1 text-xs font-medium rounded-md {{ $calendarView === 'month' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
          Bulan
        </button>
        <button wire:click="setView('week')"
          class="px-3 py-1 text-xs font-medium rounded-md {{ $calendarView === 'week' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
          Minggu
        </button>
        <button wire:click="setView('day')"
          class="px-3 py-1 text-xs font-medium rounded-md {{ $calendarView === 'day' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
          Hari
        </button>
      </div>
    </div>

    {{-- RIGHT: Tasks → Buat Acara → Profil --}}
    <div class="flex items-center gap-2">
      <a href="{{ route('settings.tasks') }}"
         class="px-3 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
        Tugas
      </a>

      <button wire:click="openCreateModal" wire:loading.attr="disabled" wire:target="openCreateModal,createEvent"
        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50">
        <span wire:loading.remove wire:target="openCreateModal">Buat Acara</span>
        <span wire:loading wire:target="openCreateModal">Memuat...</span>
      </button>

      {{-- PROFILE dropdown --}}
      <div x-data="{ open:false }" class="relative" x-cloak wire:ignore>
  <button type="button" @click.stop="open = !open" @keydown.escape.window="open=false"
    class="flex items-center gap-2 pl-2 pr-3 py-1.5 rounded-full bg-white border border-gray-300 shadow-sm hover:bg-gray-50">
    <span
      class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-blue-600 text-white text-sm font-semibold">
      {{ strtoupper(auth()->user()->name[0] ?? 'U') }}
    </span>
    <span class="text-sm font-medium text-gray-800">
      {{ auth()->user()->name ?? 'User' }}
    </span>
  </button>

  <div x-show="open" x-transition.origin.top.right @click.outside="open=false"
    class="absolute right-0 mt-2 w-72 rounded-2xl bg-white shadow-lg ring-1 ring-black/5 z-50 overflow-hidden">
    <div class="flex items-center gap-3 p-4">
      <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-bold">
        {{ strtoupper(auth()->user()->name[0] ?? 'U') }}
      </div>
      <div class="truncate">
        <div class="text-base font-semibold text-gray-900 truncate">
          {{ auth()->user()->name ?? 'User' }}
        </div>
        <div class="text-sm text-gray-500 truncate">
          {{ auth()->user()->email ?? '' }}
        </div>
      </div>
    </div>
    <div class="border-t"></div>
    <div class="p-1">
      {{-- Tombol Display --}}
      <a href="http://127.0.0.1:8000/user"
        class="block w-full text-left px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-xl">
        Tampilan
      </a>

      {{-- Tombol Log Out --}}
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit"
          class="w-full text-left px-4 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 rounded-xl">
          Keluar
        </button>
      </form>
    </div>
  </div>
</div>

      {{-- END PROFILE --}}
    </div>
  </div>
</div>
