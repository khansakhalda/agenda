@php
  if (!isset($allDayProp)) {
      $allDayProp = property_exists($this, 'allDayEvent')
          ? 'allDayEvent'
          : (property_exists($this, 'allDay') ? 'allDay' : 'allDayEvent');
  }
  $__allDayVal = property_exists($this, $allDayProp) ? $this->{$allDayProp} : false;
@endphp

@if($showCreateModal)
  <div
    class="fixed inset-0 z-[9998] flex items-center justify-center bg-black/40"
    wire:ignore.self
    @keydown.escape.window="$wire.closeCreateModal()"
    @click.self="$wire.closeCreateModal()"
  >
    <div class="relative w-[92vw] max-w-lg rounded-2xl bg-white shadow-2xl ring-1 ring-black/5">
      {{-- Header --}}
      <div class="flex justify-between items-center px-5 py-4 border-b">
        <h3 class="text-base font-semibold text-gray-900">Buat Acara Baru</h3>
        <button wire:click="closeCreateModal"
                class="h-8 w-8 grid place-items-center rounded-md text-slate-500 hover:bg-slate-100"
                aria-label="Tutup">×</button>
      </div>

      {{-- Body (scrollable) --}}
      <div class="px-5 py-4 max-h-[75vh] overflow-y-auto">
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

          {{-- Tanggal & waktu mulai --}}
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

          {{-- Tanggal & waktu selesai --}}
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

          {{-- ================== PARTISIPAN: autocomplete + enter tambah ================== --}}
          <div
            x-data="{
              query: @entangle('searchParticipant'),
              sug: [],
              suggestion: '',
              lastQuery: '',
              async updateSuggestion(){
                const q = (this.query||'').trim();
                this.lastQuery = q;

                // ghost suggestion
                if(q){
                  const res = await $wire.getFirstSuggestion(q);
                  if(this.lastQuery === q) this.suggestion = res || '';
                } else {
                  this.suggestion = '';
                }

                // dropdown suggestions
                if(q){
                  this.sug = await $wire.searchCalendarParticipants(q);
                } else {
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

              {{-- Dropdown suggestions --}}
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
            {{-- Chips terpilih --}}
            <div class="flex flex-wrap gap-2 mt-3">
              @foreach(\App\Models\Participant::whereIn('id', $selectedParticipants ?? [])->get() as $p)
                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full flex items-center">
                  {{ $p->name }}
                  <button type="button" wire:click="removeSelectedParticipant({{ $loop->index }})"
                          class="ml-2 text-red-500 hover:text-red-700">&times;</button>
                </span>
              @endforeach
            </div>
          </div>
          {{-- ================== /PARTISIPAN ================== --}}

          {{-- Footer --}}
          <div class="flex justify-end gap-2 pt-2">
            <button type="button" wire:click="closeCreateModal"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
              Batal
            </button>
            <button type="submit" wire:loading.attr="disabled" wire:target="createEvent"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50">
              <span wire:loading.remove wire:target="createEvent">Buat</span>
              <span wire:loading wire:target="createEvent">Membuat...</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endif
