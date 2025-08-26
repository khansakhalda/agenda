<div wire:poll class="min-h-screen bg-gray-50">

    <div class="flex">
        {{-- Sidebar Admin --}}
        @include('partials.sidebar')

        {{-- Main Calendar Area --}}
        <div class="flex-1 p-6">
            {{-- Header Calendar Controls --}}
            @include('partials.header')

            {{-- Calendar Grid Container --}}
            @include('partials.calendar-grid')
        </div>
    </div>

    {{-- Create Event Modal --}}
    @include('partials.modal-create')

    {{-- Edit Event Modal --}}
    @include('partials.modal-edit')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Livewire.on('refreshCalendar', () => { initializeDragAndDrop(); });
            Livewire.on('open-edit-modal', () => {
                const modal = document.getElementById('editModal');
                if (modal) {
                    modal.classList.remove('hidden'); // atau kalau pakai modal library -> modal.show()
                }
            });

            document.addEventListener('livewire:update-autocomplete', event => {
                const el = document.querySelector('[x-data]');
                if (el) {
                    el.__x.$data.results = event.detail.results;
                }
            });

            function initializeDragAndDrop() {
                const currentCells = document.querySelectorAll('[data-date]');
                currentCells.forEach(cell => {
                    cell.removeEventListener('dragover', handleDragOver);
                    cell.removeEventListener('dragleave', handleDragLeave);
                    cell.removeEventListener('drop', handleDrop);
                });
                const currentDraggables = document.querySelectorAll('[draggable="true"]');
                currentDraggables.forEach(event => {
                    event.removeEventListener('dragstart', handleDragStart);
                });

                currentCells.forEach(cell => {
                    cell.addEventListener('dragover', handleDragOver);
                    cell.addEventListener('dragleave', handleDragLeave);
                    cell.addEventListener('drop', handleDrop);
                });
                const draggableEvents = document.querySelectorAll('[draggable="true"]');
                draggableEvents.forEach(event => {
                    event.addEventListener('dragstart', handleDragStart);
                });
            }
            function handleDragOver(e) { e.preventDefault(); this.classList.add('bg-blue-100'); }
            function handleDragLeave(e) { e.classList.remove('bg-blue-100'); }
            function handleDrop(e) {
                e.preventDefault();
                e.classList.remove('bg-blue-100');
                const eventType = e.dataTransfer.getData('text/plain');
                const date = this.dataset.date;
                @this.call('quickCreateEvent', eventType, date);
            }
            function handleDragStart(e) { e.dataTransfer.setData('text/plain', this.dataset.type); }
            initializeDragAndDrop();
        });
    </script>
</div>