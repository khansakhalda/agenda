<div class="flex items-center justify-between group p-4 border rounded-xl bg-gray-50 hover:bg-white transition-all duration-300 shadow-sm hover:shadow-md">
    <div class="flex items-start w-0 flex-1 space-x-3"> {{-- items-start agar konten nempel ke atas --}}
        {{-- Checkbox --}}
        <label class="relative cursor-pointer flex items-center group flex-shrink-0 mt-0.5"> {{-- sedikit naik agar sejajar judul --}}
            <input type="checkbox" class="peer hidden" wire:click="markAsDone({{ $task->id }})">
            <div class="w-6 h-6 rounded-full border-2 border-gray-300 bg-white flex items-center justify-center transition-all duration-300 shadow-sm group-hover:shadow-md peer-checked:bg-blue-100">
                <svg class="w-4 h-4 text-blue-500 opacity-0 group-hover:opacity-100 transition duration-200 peer-checked:opacity-100" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </label>

        {{-- Content --}}
        <div class="flex-1 overflow-hidden">
            <div
                x-data="{ editing: false }"
                x-init="$nextTick(() => {
                    if ($refs.titleInput) { $refs.titleInput.focus(); editing = true; }
                    else if ($refs.descInput) { $refs.descInput.focus(); editing = true; }
                })"
                @click.away="if (editing) { $wire.saveEditing(); editing = false }"
                class="w-full max-w-full overflow-hidden"
            >
                {{-- TITLE --}}
                @if ($editingTaskId === $task->id)
                    <textarea
                        wire:model.defer="editingTitle"
                        x-ref="titleInput"
                        @focusout="$wire.saveEditing()"
                        class="w-full font-semibold text-gray-800 bg-transparent focus:outline-none focus:ring-0 border-none shadow-none resize-none leading-tight"
                        style="padding:0;min-height:0;height:auto;line-height:1.2;"
                    ></textarea>
                @else
                    <div
                        wire:click="startEditing({{ $task->id }})"
                        class="font-semibold text-gray-800 cursor-text transition hover:opacity-90 break-words whitespace-normal w-full leading-tight"
                        style="margin:0;padding:0;line-height:1.2;"
                    >
                        {{ $task->title }}
                    </div>
                @endif

                {{-- DESCRIPTION --}}
                @if ($editingTaskId === $task->id)
                    <textarea
                        wire:model.defer="editingDescription"
                        x-ref="descInput"
                        @focusout="$wire.saveEditing()"
                        class="w-full text-sm text-gray-600 bg-transparent focus:outline-none focus:ring-0 border-none shadow-none resize-y transition-all ease-in-out duration-150"
                        style="padding:2px 0;min-height:50px;overflow-wrap:break-word;white-space:pre-wrap;"
                    ></textarea>
                @else
                    <div
                        wire:click="startEditing({{ $task->id }})"
                        class="text-sm text-gray-600 cursor-text hover:opacity-90 transition overflow-hidden line-clamp-2 leading-snug"
                    >
                        {{ $task->description ?: 'No description' }}
                    </div>
                @endif

                {{-- Overdue badge (disembunyikan)
                @if ($task->is_overdue)
                    <div class="text-sm text-gray-500 mt-1">
                        <span
                            class="ml-2 px-2 py-0.5 text-xs font-semibold text-red-600 bg-red-100 rounded-full cursor-pointer hover:bg-red-200"
                            @click="$dispatch('edit-overdue', { taskId: {{ $task->id }} })"
                        >
                            {{ $task->overdue_text }}
                        </span>
                    </div>
                @endif --}}
            </div>
        </div>
    </div>

    {{-- Star & Delete --}}
    <div class="flex items-center space-x-3">
        <button type="button" wire:click="toggleStar({{ $task->id }})" class="transition-transform duration-200 hover:rotate-12 active:scale-110 text-yellow-500 hover:text-yellow-600">
            <svg class="w-5 h-5 drop-shadow-sm" fill="{{ $task->is_starred ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.518 4.674a1 1 0 00.95.69h4.919c.969 0 1.371 1.24.588 1.81l-3.977 2.89a1 1 0 00-.364 1.118l1.518 4.674c.3.921-.755 1.688-1.54 1.118l-3.977-2.89a1 1 0 00-1.175 0l-3.977 2.89c-.784.57-1.838-.197-1.539-1.118l1.518-4.674a1 1 0 00-.364-1.118l-3.977-2.89c-.784-.57-.38-1.81.588-1.81h4.919a1 1 0 00.95-.69l1.518-4.674z" />
            </svg>
        </button>
        <button type="button" wire:click="deleteTask({{ $task->id }})" 
    class="text-red-500 hover:text-red-700 transition-colors duration-200">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" 
        fill="currentColor" viewBox="0 0 24 24">
        <path d="M3 6h18v2H3V6zm2 3h14l-1.5 12.5a1 1 0 0 1-1 .87H7.5a1 1 0 0 1-1-.87L5 9zm5-5h4a1 1 0 0 1 1 1v1H9V5a1 1 0 0 1 1-1z"/>
    </svg>
</button>
    </div>
</div>
