<div class="min-h-screen bg-gray-50">
  <div class="flex">
    {{-- ================= Sidebar Admin ================= --}}
    @include('partials.sidebar')

    {{-- ================= Main Calendar Area ================= --}}
    <div class="flex-1 p-6">

      {{-- Header (Hari Ini, Navigasi, Bulan/Minggu/Hari, Ekspor, Tasks, Buat Acara, Profil) --}}
      @include('partials.header')

      {{-- Kalender (grid bulanan / mingguan / harian) --}}
      @include('partials.calendar-grid')

{{-- MODALS --}}
<div wire:key="modal-create">
  @include('partials.modal-create')
</div>

<div wire:key="modal-edit">
  @include('partials.modal-edit')
</div>

<div wire:key="modal-delete-confirm">
  @include('partials.modal-delete-confirm')
</div>
{{-- /MODALS --}}


      {{-- Toast/Notifikasi (opsional) --}}
      @includeWhen(View::exists('partials.toast'), 'partials.toast')
    </div>
  </div>
</div>
