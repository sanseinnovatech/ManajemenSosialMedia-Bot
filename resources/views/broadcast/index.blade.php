<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar Broadcast') }}
        </h2>
    </x-slot>

    <div class="py-12 max-w-6xl mx-auto">
        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded shadow text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex justify-end mb-4">
            <a href="{{ route('broadcast.create') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow font-semibold">
                + Buat Broadcast Baru
            </a>
        </div>

        <div class="flex justify-center">
            <div class="w-full max-w-6xl bg-white shadow rounded-lg overflow-x-auto">
                <table class="min-w-full table-auto text-sm text-gray-700 border border-gray-200">
                    <thead class="bg-blue-100 text-blue-800 font-semibold text-xs uppercase text-center">
                        <tr>
                            <th class="px-6 py-3 text-start">No</th>
                            <th class="px-6 py-3 text-start">Caption</th>
                            <th class="px-6 py-3 text-start">Media</th>
                            <th class="px-6 py-3 text-start">Dibuat</th>
                            <th class="px-6 py-3 text-start">Status</th>
                            <th class="px-6 py-3 text-start">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($broadcasts as $i => $bc)
                            <tr>
                                <td class="px-6 py-4">{{ $i + 1 }}</td>
                                <td class="px-6 py-4 text-gray-800">{{ Str::limit($bc->caption, 40) }}</td>
                                <td class="px-6 py-4 text-blue-600 underline">
                                    <a href="{{ asset('storage/' . $bc->media_path) }}" target="_blank">Lihat Media</a>
                                </td>
                                <td class="px-6 py-4 text-gray-500">{{ $bc->created_at->format('d M Y') }}</td>
                                <td class="px-6 py-4">
                                    @if($bc->status === 'success')
                                        <span class="text-green-600 font-semibold text-xs">✅ Terkirim</span>
                                    @else
                                        <span class="text-red-600 font-semibold text-xs">❌ Gagal</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($bc->status === 'success')
                                        <a href="{{ asset('storage/' . $bc->media_path) }}"
                                           class="text-blue-600 text-xs font-semibold hover:underline" target="_blank">
                                            Lihat
                                        </a>
                                    @else
                                        <a href="{{ route('broadcast.retry', $bc->id) }}"
                                           class="text-orange-600 text-xs font-semibold hover:underline">
                                            Retry
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada broadcast.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
