<div wire:poll class="min-h-screen bg-gray-50"><!-- Root element -->
    <div class="flex min-h-screen items-stretch">
        {{-- Main Content --}}
        <div class="flex-1 p-6">
            <div class="flex justify-between items-center mb-5">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Task List</h2>

                {{-- Create Event + Sort --}}
                <div class="flex items-center space-x-4">
                    <button
                        wire:click="openCreateModal"
                        wire:loading.attr="disabled"
                        wire:target="openCreateModal,createEvent"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="openCreateModal">Create Event</span>
                        <span wire:loading wire:target="openCreateModal">Loading...</span>
                    </button>

                    {{-- Sort Dropdown (Alpine) --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="text-gray-600 hover:text-black focus:outline-none">
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
                            class="absolute right-0 mt-2 w-52 bg-white border rounded-xl shadow-xl z-50 text-sm overflow-hidden origin-top-right"
                        >
                            <div class="px-4 py-3 text-gray-500 font-semibold border-b">Sort by</div>

                            @php
                                $sortOptions = [
                                    'manual'  => 'My order',
                                    'date'    => 'Date',
                                    'starred' => 'Starred recently',
                                    'title'   => 'Title',
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
                </div>
            </div>

            {{-- Flash Message --}}
            @if ($flashMessage)
                <div
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 -translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-4"
                    x-init="setTimeout(() => show = false, 3000)"
                    :class="{
    // Kuning
    'bg-yellow-100 text-yellow-800 border border-yellow-200': 
        '{{ $flashMessage }}' === 'Star status updated!',

    // Hijau
    'bg-green-100 text-green-800 border border-green-200': 
        '{{ $flashMessage }}' === 'Event created successfully!' ||
        '{{ $flashMessage }}' === 'Task restored!' ||
        '{{ $flashMessage }}' === 'Task updated!' ||
        '{{ $flashMessage }}' === 'Task schedule updated!' ||
        '{{ $flashMessage }}' === 'Task completed!',

    // Merah
    'bg-red-100 text-red-800 border border-red-200': 
        '{{ $flashMessage }}' === 'Task moved to Completed!' ||
        '{{ $flashMessage }}' === 'Task permanently deleted!' ||
        '{{ $flashMessage }}' === 'All completed tasks deleted!'
}"
                    class="fixed top-6 right-6 z-50 px-4 py-2 rounded-lg shadow-md text-sm font-semibold"
                >
                    {{ $flashMessage }}
                </div>
            @endif

            {{-- Task List (aktif) --}}
            @if ($sortBy === 'date' || $sortBy === 'starred')
                <div class="mb-8">
                    <h3 class="text-gray-700 font-semibold text-lg mb-4">
                        {{ $sortBy === 'starred' ? 'Starred' : 'Soon' }}
                    </h3>
                    <div class="flex flex-col space-y-4">
                        @forelse ($soonTasks as $task)
                            @include('livewire.settings.task-card', ['task' => $task])
                        @empty
                            <p class="text-gray-500 italic">No {{ $sortBy === 'starred' ? 'starred' : 'upcoming' }} tasks.</p>
                        @endforelse
                    </div>
                </div>

                <div class="mb-10 space-y-4">
                    <h3 class="text-gray-700 font-semibold text-lg mb-4">
                        {{ $sortBy === 'starred' ? 'Not Starred' : 'Past' }}
                    </h3>
                    @forelse ($pastTasks as $task)
                        @include('livewire.settings.task-card', ['task' => $task])
                    @empty
                        <p class="text-gray-500 italic">No {{ $sortBy === 'starred' ? 'not starred' : 'past' }} tasks.</p>
                    @endforelse
                </div>
            @else
                <div class="flex flex-col space-y-4 mb-10">
                    @forelse ($tasks as $task)
                        @include('livewire.settings.task-card', ['task' => $task])
                    @empty
                        <p class="text-center text-gray-500">No tasks yet.</p>
                    @endforelse
                </div>
            @endif

{{-- Completed Section (dropdown; card mirip task-card tapi abu-abu/disabled) --}}
<div class="mt-6" x-data="{ openCompleted: true }">
  <div class="flex items-center justify-between mb-3">
    <!-- Tombol dropdown ala chevron kecil di kiri -->
    <button type="button"
            @click="openCompleted = !openCompleted"
            class="flex items-center space-x-2 text-gray-700 hover:text-black font-medium">
      <svg class="w-4 h-4 text-gray-500 transition-transform duration-200"
           :class="openCompleted ? 'rotate-90' : ''"
           viewBox="0 0 24 24" fill="currentColor">
        <path d="M8.25 4.5l7.5 7.5-7.5 7.5" />
      </svg>
      <span>Completed ({{ $completedTasks->count() }})</span>
    </button>

    @if($completedTasks->count() > 0)
      <button wire:click="clearCompleted"
              class="text-sm px-3 py-1.5 rounded-md border text-red-600 border-red-200 hover:bg-red-50">
        Delete all completed
      </button>
    @endif
  </div>

  <div x-show="openCompleted"
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0 -translate-y-1"
       x-transition:enter-end="opacity-100 translate-y-0"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100 translate-y-0"
       x-transition:leave-end="opacity-0 -translate-y-1"
       class="space-y-3">

    @forelse($completedTasks as $c)
      <div class="flex items-center justify-between group p-4 border rounded-xl 
                  bg-gray-100 hover:bg-gray-100 transition-all duration-300 shadow-sm opacity-80">
        <div class="flex items-start w-0 flex-1 space-x-3">
          
          {{-- Checkbox (klik restore) --}}
          <button type="button" wire:click="restoreTask({{ $c->id }})"
                  class="relative cursor-pointer flex items-center flex-shrink-0 mt-0.5">
            <div class="w-6 h-6 rounded-full border-2 border-gray-300 bg-white flex items-center justify-center
                        transition-all duration-300 shadow-sm">
              <svg class="w-4 h-4 text-blue-500 opacity-100"
                   fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
              </svg>
            </div>
          </button>

          {{-- Konten --}}
          <div class="flex-1 overflow-hidden pointer-events-none">
            <div class="font-semibold text-gray-600 line-through break-words whitespace-normal w-full leading-tight">
              {{ $c->title }}
            </div>

            <div class="text-sm text-gray-500 mt-1 line-clamp-2 leading-snug">
              {{ $c->description ?: 'No description' }}
            </div>

            <div class="text-xs text-gray-500 mt-1">
              Completed: {{ optional(\Carbon\Carbon::parse($c->completed_at))->format('D, M j') ?? '-' }}
            </div>
          </div>
        </div>

        {{-- Trash (hapus permanen) --}}
        <button type="button" wire:click="destroyTask({{ $c->id }})"
                class="text-red-500 hover:text-red-700 transition-colors duration-200">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5"
               viewBox="0 0 24 24" fill="currentColor">
            <path d="M3 6h18v2H3V6zm2 3h14l-1.5 12.5a1 1 0 0 1-1 .87H7.5a1 1 0 0 1-1-.87L5 9zm5-5h4a1 1 0 0 1 1 1v1H9V5a1 1 0 0 1 1-1z"/>
          </svg>
        </button>
      </div>
    @empty
      <p class="text-gray-400 text-sm">No completed tasks.</p>
    @endforelse
  </div>
</div>

    {{-- Modal: Create Event (match CalendarAdmin) --}}
    @if($showCreateModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
            <div class="relative mx-auto p-5 border w-[480px] md:w-[520px] max-w-[92vw] shadow-lg rounded-md bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Create New Event</h3>
                    <button wire:click="closeCreateModal" class="text-gray-800 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="createEvent" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Event Title</label>
                        <input type="text" wire:model.defer="title" placeholder="Event title"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea wire:model.defer="description" placeholder="Event description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="allDayEvent" wire:model="allDayEvent" class="mr-2">
                        <label for="allDayEvent" class="text-sm text-gray-700">All day</label>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                            <input type="date" wire:model.defer="startDate"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('startDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        @if(!$allDayEvent)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                                <select wire:model.defer="startTime"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select start time</option>
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
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                            <input type="date" wire:model.defer="endDate"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('endDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        @if(!$allDayEvent)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                                <select wire:model.defer="endTime"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select end time</option>
                                    @foreach($this->getTimeOptions() as $time)
                                        <option value="{{ $time }}">{{ $time }}</option>
                                    @endforeach
                                </select>
                                @error('endTime') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>

                    <div class="flex justify-end space-x-3 pt-2">
                        <button type="button" wire:click="closeCreateModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                            Cancel
                        </button>
                        <button type="submit"
                                wire:loading.attr="disabled"
                                wire:target="createEvent"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="createEvent">Create</span>
                            <span wire:loading wire:target="createEvent" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
