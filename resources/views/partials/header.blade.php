@php
  use Carbon\Carbon;
  use Illuminate\Support\Collection;
  Carbon::setLocale('id');
@endphp

<div class="flex justify-between items-center mb-5">
  <div class="flex items-center space-x-3">
    <div class="flex items-center space-x-1">
      <button wire:click="goToToday"
        class="px-3 py-2 text-sm font-medium text-white bg-blue-600 border-gray-300 rounded-md hover:bg-blue-700">Hari
        Ini</button>
      <button wire:click="goToPrevious" class="p-1 hover:bg-gray-100 rounded">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
        </svg>
      </button>
      <button wire:click="goToNext" class="p-1 hover:bg-gray-100 rounded">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
        </svg>
      </button>
      <h2 class="text-lg font-bold text-gray-800 ml-3">
        {{ Carbon::parse($this->currentDate)->locale('id')->translatedFormat('l, d F Y') }}
      </h2>
    </div>
  </div>

  <div class="flex items-center space-x-2">
    <div class="flex bg-gray-100 rounded-lg p-0.5">
      <button wire:click="setView('month')"
        class="px-3 py-1 text-xs font-medium rounded-md {{ $calendarView === 'month' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">Bulan</button>
      <button wire:click="setView('week')"
        class="px-3 py-1 text-xs font-medium rounded-md {{ $calendarView === 'week' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">Minggu</button>
      <button wire:click="setView('day')"
        class="px-3 py-1 text-xs font-medium rounded-md {{ $calendarView === 'day' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">Hari</button>
    </div>
    <button
      class="px-3 py-1 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Ekspor</button>
    <button wire:click="openCreateModal" wire:loading.attr="disabled" wire:target="openCreateModal,createEvent"
      class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
      <span wire:loading.remove wire:target="openCreateModal">Buat Acara</span>
      <span wire:loading wire:target="openCreateModal">Memuat...</span>
    </button>
  </div>
</div>