@if($showEditModal)
  <div
    class="fixed inset-0 z-[9998] flex items-center justify-center bg-black/40"
    wire:ignore.self
    @keydown.escape.window="$wire.closeEditModal()"
    @click.self="$wire.closeEditModal()"
  >
    <div class="relative w-[92vw] max-w-lg rounded-2xl bg-white shadow-2xl ring-1 ring-black/5">
      {{-- Header --}}
      <div class="flex justify-between items-center px-5 py-4 border-b">
        <h3 class="text-base font-semibold text-gray-900">Edit Acara</h3>
        <button wire:click="closeEditModal"
                class="h-8 w-8 grid place-items-center rounded-md text-slate-500 hover:bg-slate-100"
                aria-label="Tutup">×</button>
      </div>

      {{-- Body (scrollable) --}}
      <div class="px-5 py-4 max-h-[75vh] overflow-y-auto">
        {{-- Navigasi event (opsional) --}}
        @if($fromMore && $eventsInCurrentModalSlot->count() > 1)
          <div class="flex items-center justify-between mb-4">
            <button wire:click="navigateModalEvent('prev')"
                    @if($currentModalEventIndex === 0) disabled @endif
                    class="px-2 py-1 bg-gray-100 rounded-md text-gray-700 hover:bg-gray-200 disabled:opacity-50">‹</button>
            <span class="text-sm text-gray-600">{{ $currentModalEventIndex + 1 }} dari {{ $eventsInCurrentModalSlot->count() }}</span>
            <button wire:click="navigateModalEvent('next')"
                    @if($currentModalEventIndex === $eventsInCurrentModalSlot->count() - 1) disabled @endif
                    class="px-2 py-1 bg-gray-100 rounded-md text-gray-700 hover:bg-gray-200 disabled:opacity-50">›</button>
          </div>
        @endif

        <form wire:submit.prevent="updateEvent" class="space-y-4">
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

          {{-- All day --}}
          <div class="flex items-center">
            <input type="checkbox" wire:model="allDay" class="mr-2">
            <label class="text-sm text-gray-700">Sepanjang hari</label>
          </div>

          {{-- Tanggal & waktu --}}
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

          {{-- Partisipan --}}
          <div
            x-data="{
              query: @entangle('searchParticipant'),
              sug: [],
              suggestion: '',
              lastQuery: '',
              async updateSuggestion(){
                const q = (this.query||'').trim();
                this.lastQuery = q;

                if(q){
                  const res = await $wire.getFirstSuggestion(q);
                  if(this.lastQuery === q) this.suggestion = res || '';
                  this.sug = await $wire.searchCalendarParticipants(q);
                } else {
                  this.suggestion = '';
                  this.sug = [];
                }
              },
              pick(p){
                if(p?.name){ $wire.addParticipantFromInput(p.name); }
                this.query=''; this.suggestion=''; this.sug=[];
              },
              enter(){
                const name=(this.query||'').trim(); if(!name) return;
                $wire.addParticipantFromInput(name);
                this.query=''; this.suggestion=''; this.sug=[];
              },
              acceptGhost(){
                if(this.suggestion && this.suggestion.startsWith(this.query)){
                  this.query = this.suggestion; this.suggestion='';
                }
              }
            }"
            x-init="$watch('query', () => updateSuggestion())"
            class="mt-2 relative"
          >
            <label class="block text-sm font-medium text-gray-700 mb-1">Partisipan</label>

            <div class="relative">
              <input type="text"
                     x-model="query"
                     @keydown.tab.prevent="acceptGhost()"
                     @keydown.enter.prevent="enter()"
                     placeholder="Ketik nama partisipan..."
                     class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">

              {{-- Ghost suggestion --}}
              <div class="absolute top-0 left-0 px-3 py-2 text-gray-400 pointer-events-none">
                <template x-if="suggestion && suggestion !== query">
                  <span x-text="query + suggestion.substring(query.length)"></span>
                </template>
              </div>

              {{-- Dropdown --}}
              <div class="absolute z-10 mt-1 w-full bg-white border rounded-lg shadow max-h-56 overflow-y-auto"
                   x-show="sug.length">
                <template x-for="p in sug" :key="p.id ?? p.name">
                  <button type="button" class="w-full text-left px-3 py-2 hover:bg-slate-50"
                          @click="pick(p)" x-text="p.name"></button>
                </template>
              </div>
            </div>
<p id="help-partisipan" class="mt-2 text-xs text-slate-500">
  Jika belum ada, tekan <strong>Enter</strong> untuk menambah.
</p>
            {{-- Chips --}}
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
          {{-- /Partisipan --}}

          {{-- Footer --}}
          <div class="flex justify-end gap-2 pt-2">
            <button type="button" wire:click="closeEditModal"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
              Batal
            </button>
            <button type="button"
        @click="$dispatch('confirm',{
          title:'Hapus Acara?',
          text:'Tindakan ini tidak bisa dibatalkan. Yakin ingin menghapus acara ini?',
          confirmText:'Ya, Hapus',
          cancelText:'Batal',
          method:'deleteEvent',
          args:[{{ $editingEventId }}]
        })"
        class="px-4 py-2 text-sm font-medium text-white bg-red-500 rounded-md hover:bg-red-600">
  Hapus
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
  </div>
@endif
