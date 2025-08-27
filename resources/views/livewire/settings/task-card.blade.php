{{-- resources/views/livewire/settings/task-card.blade.php --}}
<div class="flex items-center justify-between group p-4 border rounded-xl bg-gray-50 hover:bg-white transition-all duration-300 shadow-sm hover:shadow-md">

  {{-- kiri: checkbox + konten --}}
  <div class="flex items-start w-0 flex-1 space-x-3">
    {{-- Checkbox: tandai selesai --}}
    <label class="relative cursor-pointer flex items-center group flex-shrink-0 mt-0.5">
      <input type="checkbox" class="peer hidden" wire:click="markAsDone({{ $task->id }})">
      <div class="w-6 h-6 rounded-full border-2 border-gray-300 bg-white flex items-center justify-center transition-all duration-300 shadow-sm group-hover:shadow-md peer-checked:bg-blue-100">
        <svg class="w-4 h-4 text-blue-500 opacity-0 group-hover:opacity-100 transition duration-200 peer-checked:opacity-100" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
        </svg>
      </div>
    </label>

    {{-- Konten --}}
    <div class="flex-1 overflow-hidden">
      <div
        x-data="{
          editing: @js($editingTaskId === $task->id),
          start(){ this.editing = true; $wire.startEditing({{ $task->id }}) },
          save(){ $wire.saveEditing(); this.editing=false },
          cancel(){ this.editing=false; $wire.cancelEditing() }
        }"
        x-on:keydown.escape.prevent="cancel()"
        class="w-full max-w-full overflow-hidden"
      >
        {{-- ===== TITLE ===== --}}
        @if ($editingTaskId === $task->id)
          <textarea
            wire:model.defer="editingTitle"
            class="w-full font-semibold text-gray-800 bg-transparent focus:outline-none focus:ring-0 border-none shadow-none resize-none leading-tight"
            style="padding:0;min-height:0;height:auto;line-height:1.2;"
            autofocus
          ></textarea>
        @else
          <div
            @click="start()"
            class="font-semibold text-gray-800 cursor-text transition hover:opacity-90 break-words whitespace-normal w-full leading-tight"
            style="margin:0;padding:0;line-height:1.2;"
          >
            {{ $task->title }}
          </div>
        @endif

        {{-- ===== DESCRIPTION ===== --}}
        @if ($editingTaskId === $task->id)
          <textarea
            wire:model.defer="editingDescription"
            class="w-full text-sm text-gray-600 bg-transparent focus:outline-none focus:ring-0 border-none shadow-none resize-y transition-all ease-in-out duration-150"
            style="padding:2px 0;min-height:50px;overflow-wrap:break-word;white-space:pre-wrap;"
          ></textarea>
        @else
          <div
            @click="start()"
            class="text-sm text-gray-600 cursor-text hover:opacity-90 transition overflow-hidden line-clamp-2 leading-snug"
          >
            {{ $task->description ?: 'Tanpa deskripsi' }}
          </div>
        @endif

        {{-- ===== PARTICIPANTS (chips + inline add + autocomplete) ===== --}}
        @php
          /** @var \App\Models\Event|null $__event */
          $__event = \App\Models\Event::with('participants')->find($task->id);
          $__participants = $__event?->participants ?? collect();
        @endphp

        <div
          x-data="{
            q:'',sug:'',lastQ:'',
            async updateSug(){
              const now=this.q.trim(); this.lastQ=now;
              if(!now){ this.sug=''; return }
              const res = await $wire.getFirstSuggestionForCard(now);
              if(this.lastQ===now) this.sug = res || '';
            },
            acceptSug(){ if(this.sug && this.sug.startsWith(this.q)) this.q=this.sug; },
            async submit(){
              const name=this.q.trim(); if(!name) return;
              await $wire.attachParticipant({{ $task->id }}, name);
              this.q=''; this.sug='';
            }
          }"
          x-init="$watch('q', () => updateSug())"
          class="flex flex-wrap gap-1 mt-2 relative"
        >
          {{-- chips --}}
          @foreach($__participants as $p)
            <span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full text-xs flex items-center">
              {{ $p->name }}
              <button type="button" wire:click="detachParticipant({{ $task->id }}, {{ $p->id }})"
                      class="ml-1 text-red-500 hover:text-red-700" title="Hapus dari acara">&times;</button>
            </span>
          @endforeach

          {{-- input tambah --}}
          <div class="relative">
            <input type="text" x-model="q" @keydown.tab.prevent="acceptSug()" @keydown.enter.prevent="submit()"
                   placeholder="+ tambah partisipan"
                   class="text-xs border rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-blue-500"
                   style="min-width: 180px" autocomplete="off">
            {{-- ghost suggestion --}}
            <div class="pointer-events-none absolute inset-0 flex items-center px-2 text-xs text-gray-400">
              <template x-if="sug && q && sug !== q">
                <span x-text="q + sug.substring(q.length)"></span>
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- kanan: actions; saat mode edit tampil tombol ✔ / ✖ --}}
  <div class="flex items-center space-x-2 transition"
       :class="{{ json_encode($editingTaskId === $task->id) }} ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'">
    @if ($editingTaskId === $task->id)
      {{-- Save (hanya ini yang menyimpan) --}}
      <button type="button" wire:click="saveEditing" class="text-green-600 hover:text-green-800" title="Simpan">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
      </button>

      {{-- Cancel (kembalikan tampilan kartu, tidak menyimpan) --}}
      <button type="button" wire:click="cancelEditing" class="text-gray-600 hover:text-gray-800" title="Batal">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    @else
      {{-- Star --}}
      <button type="button" wire:click="toggleStar({{ $task->id }})"
              class="text-yellow-500 hover:text-yellow-600" title="Beri bintang">
        <svg class="w-5 h-5" fill="{{ $task->is_starred ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round"
                d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.518 4.674a1 1 0 00.95.69h4.919c.969 0 1.371 1.24.588 1.81l-3.977 2.89a1 1 0 00-.364 1.118l1.518 4.674c.3.921-.755 1.688-1.54 1.118l-3.977-2.89a1 1 0 00-1.175 0l-3.977 2.89c-.784.57-1.838-.197-1.539-1.118l1.518-4.674a1 1 0 00-.364-1.118l-3.977-2.89c-.784-.57-.38-1.81.588-1.81h4.919a1 1 0 00.95-.69l1.518-4.674z" />
        </svg>
      </button>

      {{-- Edit --}}
      <button type="button" wire:click="startEditing({{ $task->id }})"
              class="text-blue-500 hover:text-blue-700" title="Edit">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414
                   a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
      </button>

      {{-- Delete -> konfirmasi (pindah ke Selesai / arsip) --}}
      <button type="button"
              @click="$dispatch('confirm',{
                title:'Pindahkan ke Selesai?',
                text:'Tugas akan dipindahkan ke bagian Selesai.',
                confirmText:'Ya',
                cancelText:'Tidak',
                method:'deleteTask',
                args:[{{ $task->id }}]
              })"
              class="text-red-500 hover:text-red-700" title="Hapus">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
        </svg>
      </button>
    @endif
  </div>
</div>
