@php
  use Carbon\Carbon;
  use Illuminate\Support\Collection;
  Carbon::setLocale('id');
@endphp

<div class="w-72 bg-white shadow-lg p-4">
  {{-- Mini Calendar --}}
  <div class="mt-6 mb-3">
    <div class="bg-gray-50 rounded-lg p-3">
      {{-- Mini Calendar Header --}}
      <div class="flex justify-between items-center mb-2">
        <button wire:click="previousMiniMonth" class="p-1 hover:bg-gray-200 rounded">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
          </svg>
        </button>
        <span
          class="text-sm font-semibold">{{ Carbon::create($miniCalendarYear, $miniCalendarMonth, 1)->translatedFormat('F Y') }}</span>
        <button wire:click="nextMiniMonth" class="p-1 hover:bg-gray-200 rounded">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
          </svg>
        </button>
      </div>

      {{-- Mini Calendar Grid --}}
      <div class="grid grid-cols-7 text-sm">
        {{-- Day Headers --}}
        @foreach(['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $day)
          <div class="text-center text-gray-500 font-medium py-1">{{ $day }}</div>
        @endforeach

        {{-- Calendar Days --}}
        @php
          $miniStartOfMonth = Carbon::create($miniCalendarYear, $miniCalendarMonth, 1);
          $miniEndOfMonth = $miniStartOfMonth->copy()->endOfMonth();
          $miniStartOfGrid = $miniStartOfMonth->copy()->startOfWeek();
          $miniEndOfGrid = $miniEndOfMonth->copy()->endOfWeek();
          $miniCurrentDay = $miniStartOfGrid->copy();
        @endphp

        @while($miniCurrentDay <= $miniEndOfGrid)
          @php
            $isCurrentMonth = $miniCurrentDay->month == $miniStartOfMonth->month;
            $isToday = $miniCurrentDay->isToday();
            $isSelected = $miniCurrentDay->format('Y-m-d') === $this->currentDate;
          @endphp
          <button wire:click="selectMiniCalendarDate('{{ $miniCurrentDay->format('Y-m-d') }}')"
            class="h-6 text-xs rounded hover:bg-blue-100 transition-colors {{ !$isCurrentMonth ? 'text-gray-300' : 'text-gray-700' }} {{ $isToday ? 'bg-blue-600 text-white font-bold' : '' }} {{ $isSelected && !$isToday ? 'bg-blue-200 text-blue-800 font-semibold' : '' }}">
            {{ $miniCurrentDay->day }}
          </button>
          @php $miniCurrentDay->addDay(); @endphp
        @endwhile
      </div>
    </div>
  </div>

  {{-- Calendar Stats --}}
  <div class="mb-4">
    <h3 class="text-base font-semibold text-gray-800 mb-3">Statistik Kalender</h3>
    <div class="space-y-3">
      <div class="flex justify-between items-end">
        <span class="text-sm text-gray-600">Total</span>
        <span class="font-bold text-xl">{{ $stats['total'] }}</span>
      </div>
      <div class="flex justify-between items-end">
        <span class="text-sm text-gray-600">Hari Ini</span>
        <span class="font-bold text-xl text-blue-600">{{ $stats['today'] }}</span>
      </div>
    </div>
    <div class="mt-4 text-xs text-gray-600">
      <div>Bulan ini:</div>
      <div class="font-bold text-base">{{ $stats['thisMonth'] }} acara</div>
      <div class="mt-2">Mendatang:</div>
      <div class="font-bold text-base">{{ $stats['upcoming'] }} acara</div>
    </div>
  </div>

  {{-- Participants Management --}}
  <div class="mb-4">
    <h3 class="text-base font-semibold text-gray-800 mb-3">Partisipan</h3>

    {{-- Add Participant Form --}}
    <div class="mb-3">
      <form wire:submit.prevent="addParticipant" class="flex">
        <input type="text" wire:model="newParticipantName" wire:keydown.enter.prevent="addParticipant"
          placeholder="Nama partisipan baru..."
          class="flex-1 px-2 py-1 text-sm border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        <button type="submit"
          class="px-3 py-1 text-sm font-medium text-white bg-blue-600 rounded-r-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
          </svg>
        </button>
      </form>
    </div>

    {{-- Participants List --}}
    <div class="space-y-1 max-h-40 overflow-y-auto">
      @forelse($participants as $participant)
        <div class="flex items-center justify-between p-2 text-sm bg-gray-50 rounded hover:bg-gray-100 group">
          @if($editingParticipantId === $participant->id)
            {{-- Edit Mode --}}
            <form wire:submit.prevent="updateParticipant" class="flex-1 flex">
              <input type="text" wire:model="editingParticipantName" wire:keydown.enter.prevent="updateParticipant"
                wire:keydown.escape="cancelEditParticipant"
                class="flex-1 px-1 py-0 text-sm bg-white border border-blue-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                autofocus>
              <button type="submit" class="ml-1 text-green-600 hover:text-green-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
              </button>
              <button type="button" wire:click="cancelEditParticipant" class="ml-1 text-gray-600 hover:text-gray-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </form>
          @else
            {{-- View Mode --}}
            <span class="flex-1 text-gray-700">{{ $participant->name }}</span>
            <div class="opacity-0 group-hover:opacity-100 flex space-x-1">
              {{-- Edit Button --}}
              <button wire:click="startEditParticipant({{ $participant->id }})" class="text-blue-600 hover:text-blue-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                  </path>
                </svg>
              </button>
              {{-- Delete Button --}}
              <button type="button"
                onclick="if (confirm('Hapus partisipan ini?')) { @this.deleteParticipant({{ $participant->id }}) }"
                class="text-red-500 hover:text-red-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          @endif
        </div>
      @empty
        <div class="text-xs text-gray-500 text-center py-2">Belum ada partisipan</div>
      @endforelse
    </div>
  </div>
</div>