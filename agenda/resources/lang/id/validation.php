<?php

return [
  'required' => ':attribute wajib diisi.',
  'string' => ':attribute harus berupa teks.',
  'max' => [
    'string' => ':attribute tidak boleh lebih dari :max karakter.',
  ],
  'date' => ':attribute harus berupa tanggal yang valid.',
  'date_format' => ':attribute harus sesuai format :format.',
  'after' => ':attribute harus setelah :date.',
  'after_or_equal' => ':attribute harus sama dengan atau setelah :date.',

  'attributes' => [
    'title' => 'Judul', 
    'description' => 'Deskripsi',
    'startDate' => 'Tanggal mulai',
    'endDate' => 'Tanggal selesai',
    'startTime' => 'Waktu mulai',
    'endTime' => 'Waktu selesai',
    'color' => 'Warna',
  ],
];