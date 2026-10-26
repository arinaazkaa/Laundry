@props(['status'])
@php
$map = [
    'menunggu'         => 'bg-gray-100 text-gray-600',
    'diproses'         => 'bg-blue-100 text-blue-600',
    'dicuci'           => 'bg-blue-100 text-blue-600',
    'dikeringkan'      => 'bg-blue-100 text-blue-600',
    'disetrika'        => 'bg-blue-100 text-blue-600',
    'siap_diambil'     => 'bg-warning-50 text-warning-600',
    'selesai'          => 'bg-success-50 text-success-600',
    'dibatalkan'       => 'bg-danger-50 text-danger-600',
    'belum_bayar'      => 'bg-danger-50 text-danger-600',
    'dp'               => 'bg-warning-50 text-warning-600',
    'lunas'            => 'bg-success-50 text-success-600',
    // antar-jemput
    'dijadwalkan'      => 'bg-gray-100 text-gray-600',
    'dalam_perjalanan' => 'bg-blue-100 text-blue-600',
    // permintaan jemput
    'dikonfirmasi'     => 'bg-blue-100 text-blue-600',
    'dijemput'         => 'bg-success-50 text-success-600',
    'ditolak'          => 'bg-danger-50 text-danger-600',
];
$labels = ['dp' => 'DP'];
$classes = $map[$status] ?? 'bg-gray-100 text-gray-600';
$label = $labels[$status] ?? ucwords(str_replace('_', ' ', $status));
@endphp
<span class="{{ $classes }} text-xs font-medium px-2.5 py-1 rounded-full whitespace-nowrap">{{ $label }}</span>