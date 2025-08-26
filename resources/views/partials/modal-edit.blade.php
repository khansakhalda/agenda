@if($showEditModal)
  <div
    class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
    <div class="relative mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-bold text-gray-900">Edit Acara</h3>
        <button wire:click="closeEditModal" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      {{-- Kontrol Navigasi Event di Modal --}}
      @if($fromMore && $eventsInCurrentModalSlot->count() > 1)
        <div class="flex items-center justify-between mb-4">
          <button wire:click="navigateModalEvent('prev')" @if($currentModalEventIndex === 0) disabled @endif
            class="px-2 py-1 bg-gray-100 rounded-md text-gray-700 hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
            </svg>
          </button>
          <span class="text-sm text-gray-600">{{ $currentModalEventIndex + 1 }} dari
            {{ $eventsInCurrentModalSlot->count() }}</span>
          <button wire:click="navigateModalEvent('next')" @if($currentModalEventIndex === $eventsInCurrentModalSlot->count() - 1) disabled @endif
            class="px-2 py-1 bg-gray-100 rounded-md text-gray-700 hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
            </svg>
          </button>
        </div>
      @endif

      <form wire:submit.prevent="updateEvent" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Judul</label>
          <input type="text" wire:model="title" placeholder="Judul acara"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          @error('title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
          <textarea wire:model="description" placeholder="Deskripsi acara" rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
          @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="flex items-center">
          <input type="checkbox" wire:model="allDay" class="mr-2">
          <label class="text-sm text-gray-700">Sepanjang hari</label>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
            <input type="date" wire:model="startDate"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('startDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
          </div>

          @if(!$allDay)
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

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
            <input type="date" wire:model="endDate"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('endDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
          </div>

          @if(!$allDay)
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
        <div x-data="{
                            query: @entangle('searchParticipant'),
                            suggestion: '',
                            lastQuery: '',
                            async updateSuggestion() {
                                const currentQuery = this.query;
                                this.lastQuery = currentQuery;

                                if (currentQuery.length > 0) {
                                    const res = await $wire.getFirstSuggestion(currentQuery);

                                    // Hanya update kalau query saat ini masih sama
                                    if (this.lastQuery === currentQuery) {
                                        this.suggestion = res || '';
                                    }
                                } else {
                                    this.suggestion = '';
                                }
                            }
                        }" x-init="$watch('query', () => updateSuggestion())" class="mt-4 relative">

          <label class="block text-sm font-medium text-gray-700 mb-1">Partisipan</label>

          <div class="relative">
            <!-- Input Utama -->
            <input type="text" x-model="query" @keydown.tab.prevent="if(suggestion){ query = suggestion; suggestion=''; }"
              @keydown.enter.prevent="$wire.addParticipantFromInput(query); query=''; suggestion='';"
              placeholder="Ketik nama partisipan..."
              class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">

            <!-- Ghost Suggestion -->
            <div class="absolute top-0 left-0 px-3 py-2 text-gray-400 pointer-events-none">
              <template x-if="suggestion && suggestion !== query">
                <span x-text="query + suggestion.substring(query.length)"></span>
              </template>
            </div>
          </div>

          <!-- Pesan Error -->
          @error('participant_error')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
          @enderror

          <!-- Chips Partisipan -->
          <div class="flex flex-wrap gap-2 mt-3">
            @foreach(\App\Models\Participant::whereIn('id', $selectedParticipants ?? [])->get() as $p)
              <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full flex items-center">
                {{ $p->name }}
                <button type="button" wire:click="removeParticipant({{ $p->id }})"
                  class="ml-2 text-red-500 hover:text-red-700">&times;</button>
              </span>
            @endforeach
          </div>
        </div>

        <div class="flex justify-end space-x-3 pt-4">
          <button type="button" wire:click="closeEditModal"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
            Batal
          </button>
          <button type="button" wire:click="deleteEvent" wire:loading.attr="disabled" wire:target="deleteEvent"
            class="px-4 py-2 text-sm font-medium text-white bg-red-500 rounded-md hover:bg-red-600">
            <span wire:loading.remove wire:target="deleteEvent">Hapus</span>
            <span wire:loading wire:target="deleteEvent">Menghapus...</span>
          </button>
          <button type="submit" wire:loading.attr="disabled" wire:target="updateEvent"
            class="px-4 py-2 text-sm font-medium text-white bg-gray-800 rounded-md hover:bg-gray-900 disabled:opacity-50">
            <span wire:loading.remove wire:target="updateEvent">Perbarui</span>
            <span wire:loading wire:target="updateEvent">Memperbarui...</span>
          </button>
        </div>
      </form>
    </div>
  </div>
@endif