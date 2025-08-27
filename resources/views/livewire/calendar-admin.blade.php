{{-- resources/views/livewire/calendar-admin.blade.php --}}
<div wire:poll class="min-h-screen bg-gray-50">
  <div class="flex">
    {{-- Sidebar Admin --}}
    @include('partials.sidebar')

    {{-- Main Calendar Area --}}
    <div class="flex-1 p-6">

      {{-- Header (Hari Ini, navigasi, Bulan/Minggu/Hari, Ekspor, Tasks, Buat Acara, Profil) --}}
      @include('partials.header')

      {{-- Kalender yang diinginkan (versi “atas”) --}}
      @include('partials.calendar-grid')

      {{-- ✅ Tidak ada kalender kedua di bawah ini --}}

      {{-- Modals --}}
      @include('partials.modal-create')
      @include('partials.modal-edit')

      {{-- Toast/Notifikasi (opsional) --}}
      @includeWhen(View::exists('partials.toast'), 'partials.toast')
    </div>
  </div>
</div>
