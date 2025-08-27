{{-- resources/views/partials/modal-create.blade.php --}}

@php
  /**
   * $allDayProp (opsional) bisa dikirim saat include:
   *   @include('partials.modal-create', ['allDayProp' => 'allDay'])      // Calendar Admin
   *   @include('partials.modal-create', ['allDayProp' => 'allDayEvent']) // Tasks
   *
   * Jika tidak dikirim, kita deteksi otomatis properti yang tersedia di komponen Livewire.
   */
  if (!isset($allDayProp)) {
      $allDayProp = property_exists($this, 'allDayEvent')
          ? 'allDayEvent'
          : (property_exists($this, 'allDay') ? 'allDay' : 'allDayEvent');
  }
  $__allDayVal = property_exists($this, $allDayProp) ? $this->{$allDayProp} : false;
@endphp

@if($showCreateModal)
  <div class="fixed inset-0 bg-gray-600/50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
    <div class="relative mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-bold text-gray-900">Buat Acara Baru</h3>
        <button wire:click="closeCreateModal" class="text-gray-800" aria-label="Tutup">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <form wire:submit.prevent="createEvent" class="space-y-4">
        {{-- Judul --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Judul</label>
          <input type="text" wire:model="title" placeholder="Judul acara"
                 class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          @error('title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        {{-- Deskripsi --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
          <textarea wire:model="description" placeholder="Deskripsi acara" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
          @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        {{-- Sepanjang hari --}}
        <div class="flex items-center">
          <input id="allday" type="checkbox" wire:model="{{ $allDayProp }}" class="mr-2">
          <label for="allday" class="text-sm text-gray-700">Sepanjang hari</label>
        </div>

        {{-- Tanggal & Waktu Mulai --}}
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
            <input type="date" wire:model="startDate"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('startDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
          </div>

          @if(! $__allDayVal)
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Mulai</label>
              <select wire:model="startTime"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Pilih waktu mulai</option>
                @foreach($this->getTimeOptions() as $time)
                  <option value="{{ $time }}">{{ $time }}</option>
                @endforeach
              </select>
              @error('startTime') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
          @endif
        </div>

        {{-- Tanggal & Waktu Selesai --}}
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
            <input type="date" wire:model="endDate"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('endDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
          </div>

          @if(! $__allDayVal)
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Selesai</label>
              <select wire:model="endTime"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Pilih waktu selesai</option>
                @foreach($this->getTimeOptions() as $time)
                  <option value="{{ $time }}">{{ $time }}</option>
                @endforeach
              </select>
              @error('endTime') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
          @endif
        </div>

        {{-- ================== PARTISIPAN (MODAL) ================== --}}
        <div
          x-data="{
            query: @entangle('searchParticipant'),
            suggestion: '',
            lastQuery: '',
            async updateSuggestion() {
              const q = this.query;
              this.lastQuery = q;
              if (q && q.length > 0) {
                const res = await $wire.getFirstSuggestion(q);
                if (this.lastQuery === q) this.suggestion = res || '';
              } else {
                this.suggestion = '';
              }
            }
          }"
          x-init="$watch('query', () => updateSuggestion())"
          class="mt-4 relative"
        >
          <label class="block text-sm font-medium text-gray-700 mb-1">Partisipan</label>

          {{-- Input utama (Enter untuk tambah) --}}
          <div class="relative">
            <input
              type="text"
              x-model="query"
              @keydown.tab.prevent="if(suggestion){ query = suggestion; suggestion=''; }"
              @keydown.enter.prevent="$wire.addParticipantFromInput(query); query=''; suggestion='';"
              placeholder="Ketik nama partisipan..."
              class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            {{-- Ghost suggestion --}}
            <div class="absolute top-0 left-0 px-3 py-2 text-gray-400 pointer-events-none">
              <template x-if="suggestion && suggestion !== query">
                <span x-text="query + suggestion.substring(query.length)"></span>
              </template>
            </div>
          </div>

          @error('participant_error')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
          @enderror

          {{-- Chips partisipan terpilih --}}
          <div class="flex flex-wrap gap-2 mt-3">
            @foreach($selectedParticipants as $idx => $pid)
              @php $p = \App\Models\Participant::find($pid); @endphp
              @if($p)
                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full flex items-center">
                  {{ $p->name }}
                  <button type="button" wire:click="removeSelectedParticipant({{ $idx }})"
                          class="ml-2 text-red-500 hover:text-red-700">&times;</button>
                </span>
              @endif
            @endforeach
          </div>
        </div>
        {{-- ================ /PARTISIPAN (MODAL) ================ --}}

        <div class="flex justify-end space-x-3 pt-4">
          <button type="button" wire:click="closeCreateModal"
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
            Batal
          </button>
          <button type="submit" wire:loading.attr="disabled" wire:target="createEvent"
                  class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
            <span wire:loading.remove wire:target="createEvent">Buat</span>
            <span wire:loading wire:target="createEvent" class="flex items-center">
              <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Membuat...
            </span>
          </button>
        </div>
      </form>
    </div>
  </div>
@endif
