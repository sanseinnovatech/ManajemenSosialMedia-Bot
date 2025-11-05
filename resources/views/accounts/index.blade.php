<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar Akun Sosial Media') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Success Alert -->
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg shadow-lg">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium">{{ session('success') }}</p>
                        </div>
                        <div class="ml-4 flex-shrink-0">
                            <button type="button" class="inline-flex text-green-400 hover:text-green-600 focus:outline-none focus:text-green-600 transition ease-in-out duration-150" onclick="this.parentElement.parentElement.parentElement.remove()">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Header with Add Button -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <div class="flex-1">
                    <h3 class="text-lg font-medium text-gray-900">Kelola Akun Sosial Media</h3>
                    <p class="text-sm text-gray-500 mt-1">Token akan expired otomatis 10 menit setelah dibuat atau diupdate</p>
                </div>

                <a href="{{ route('accounts.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition duration-150 ease-in-out">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Tambah Akun
                </a>
            </div>

            <div class="bg-white shadow-lg rounded-lg overflow-hidden border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-blue-50 to-indigo-50">
                        <tr>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-blue-900 uppercase tracking-wide w-12">No</th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-blue-900 uppercase tracking-wide w-12">Platform</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-blue-900 uppercase tracking-wide">Username</th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-blue-900 uppercase tracking-wide">Token</th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-blue-900 uppercase tracking-wide">Token Secret</th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-blue-900 uppercase tracking-wide w-16">Page ID</th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-blue-900 uppercase tracking-wide w-20">Dibuat/Edit</th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-blue-900 uppercase tracking-wide w-20">Status</th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-blue-900 uppercase tracking-wide w-24">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($accounts as $index => $acc)
                            @php
                                $lastModified = $acc->updated_at ?? $acc->created_at;
                                $expiredAt = $lastModified->addMinutes(10);
                                $isExpired = now()->isAfter($expiredAt);
                                $minutesLeft = $isExpired ? 0 : now()->diffInMinutes($expiredAt, false);
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-2 py-3 text-sm font-medium text-gray-900">
                                    {{ $accounts->firstItem() + $index }}
                                </td>
                                <td class="px-2 py-3">
                                    <div class="flex justify-center">
                                        @if($acc->platform === 'facebook')
                                            <div class="w-7 h-7 bg-blue-100 rounded-full flex items-center justify-center">
                                                <span class="text-blue-600 font-bold text-xs">FB</span>
                                            </div>
                                        @elseif($acc->platform === 'instagram')
                                            <div class="w-7 h-7 bg-pink-100 rounded-full flex items-center justify-center">
                                                <span class="text-pink-600 font-bold text-xs">IG</span>
                                            </div>
                                        @elseif($acc->platform === 'x')
                                            <div class="w-7 h-7 bg-gray-100 rounded-full flex items-center justify-center">
                                                <span class="text-gray-700 font-bold text-xs">X</span>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-sm text-gray-900 font-medium">{{ $acc->username }}</td>
                                <td class="px-2 py-3 text-sm text-gray-500">
                                    <span class="font-mono bg-gray-100 px-1.5 py-0.5 rounded text-xs">
                                        {{ Str::limit($acc->access_token, 15) }}
                                    </span>
                                </td>
                                <td class="px-2 py-3 text-sm text-gray-500">
                                    @if($acc->access_token_secret)
                                        <span class="font-mono bg-gray-100 px-1.5 py-0.5 rounded text-xs">
                                            {{ Str::limit($acc->access_token_secret, 15) }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-2 py-3 text-sm text-gray-900">
                                    {{ $acc->page_id ? Str::limit($acc->page_id, 8) : '-' }}
                                </td>
                                <td class="px-2 py-3 text-xs">
                                    <div class="text-gray-600">
                                        {{ $lastModified->format('d/m') }}
                                    </div>
                                    <div class="text-gray-400">
                                        {{ $lastModified->format('H:i') }}
                                    </div>
                                </td>
                                <td class="px-2 py-3">
                                    @if($isExpired)
                                        <div class="inline-flex items-center px-1.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <div class="w-1.5 h-1.5 bg-red-400 rounded-full mr-1"></div>
                                            Expired
                                        </div>
                                    @elseif($minutesLeft <= 2)
                                        <div class="inline-flex items-center px-1.5 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                            <div class="w-1.5 h-1.5 bg-orange-400 rounded-full mr-1 animate-pulse"></div>
                                            {{ $minutesLeft }}m
                                        </div>
                                    @elseif($minutesLeft <= 5)
                                        <div class="inline-flex items-center px-1.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <div class="w-1.5 h-1.5 bg-yellow-400 rounded-full mr-1"></div>
                                            {{ $minutesLeft }}m
                                        </div>
                                    @else
                                        <div class="inline-flex items-center px-1.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <div class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1"></div>
                                            {{ $minutesLeft }}m
                                        </div>
                                    @endif
                                </td>
                                <td class="px-2 py-3">
                                    <div class="flex items-center space-x-1">
                                        <a href="{{ route('accounts.edit', $acc) }}"
                                           class="inline-flex items-center px-2 py-1 bg-blue-500 hover:bg-blue-600 text-white text-xs font-medium rounded transition-colors duration-150"
                                           title="Edit">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                        <form method="POST" action="{{ route('accounts.destroy', $acc) }}" class="delete-form inline">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center px-2 py-1 bg-red-500 hover:bg-red-600 text-white text-xs font-medium rounded transition-colors duration-150"
                                                    title="Delete">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <p class="text-gray-500 text-lg">Belum ada akun sosial media</p>
                                        <p class="text-gray-400 text-sm mt-1">Tambahkan akun pertama Anda untuk memulai</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($accounts->hasPages())
                <div class="mt-6 flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Menampilkan {{ $accounts->firstItem() }} sampai {{ $accounts->lastItem() }}
                        dari {{ $accounts->total() }} hasil
                    </div>
                    <div>
                        {{ $accounts->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        // Auto dismiss success alert after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const successAlert = document.querySelector('.bg-green-50');
            if (successAlert) {
                setTimeout(function() {
                    successAlert.style.transition = 'all 0.5s ease-out';
                    successAlert.style.opacity = '0';
                    successAlert.style.transform = 'translateY(-10px)';
                    setTimeout(function() {
                        successAlert.remove();
                    }, 500);
                }, 5000);
            }
        });

        // Auto refresh every 30 seconds to update status
        setInterval(function() {
            // Only refresh if no modal is open (to avoid interrupting user actions)
            if (!document.querySelector('.swal2-container')) {
                window.location.reload();
            }
        }, 30000);

        // Delete confirmation with better styling
        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const username = form.closest('tr').querySelector('td:nth-child(3)').textContent.trim();

                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    html: `Yakin ingin menghapus akun <strong>${username}</strong>?<br><small class="text-gray-500">Tindakan ini tidak bisa dibatalkan!</small>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    customClass: {
                        popup: 'rounded-lg shadow-2xl',
                        title: 'text-lg font-semibold',
                        content: 'text-sm',
                        confirmButton: 'px-4 py-2 text-sm font-medium rounded-lg',
                        cancelButton: 'px-4 py-2 text-sm font-medium rounded-lg'
                    },
                    buttonsStyling: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state
                        Swal.fire({
                            title: 'Menghapus...',
                            text: 'Tunggu sebentar',
                            icon: 'info',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            willOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        form.submit();
                    }
                });
            });
        });
    </script>
    @endpush
</x-app-layout>
