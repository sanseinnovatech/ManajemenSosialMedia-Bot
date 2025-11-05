<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Ulangi Kirim Broadcast') }}
        </h2>
    </x-slot>

    <div class="py-12 max-w-4xl mx-auto">
        <form method="POST" action="{{ route('broadcast.retry.send', $broadcast->id) }}" enctype="multipart/form-data"
              class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            @csrf
            @method('POST')

            @php
                $accounts = \App\Models\SocialMediaAccount::all();
            @endphp

            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-1">Pilih Akun Sosial Media</label>
                <select name="accounts[]" id="accounts" multiple
                        class="w-full border-gray-300 rounded shadow-sm focus:ring focus:ring-blue-200">
                    @foreach($accounts->groupBy('platform') as $platform => $group)
                        <optgroup label="{{ $platform }}">
                            @foreach($group as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->username }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                <button type="button"
                        onclick="$('#accounts option').prop('selected', true); $('#accounts').trigger('change');"
                        class="mt-2 text-sm text-blue-600 hover:underline">
                    Pilih Semua Akun
                </button>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-1">Caption</label>
                <textarea name="caption" rows="3" class="w-full border border-gray-300 rounded p-2 shadow-sm"
                          required>{{ $broadcast->caption }}</textarea>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 font-medium mb-1">Media (Foto/Video)</label>
                <input type="file" name="media" accept="image/*,video/*"
                       class="w-full border border-gray-300 p-2 rounded shadow-sm">
                <p class="text-xs text-gray-500 mt-1">Biarkan kosong jika tidak ingin mengganti media lama.</p>
                <a href="{{ asset('storage/' . $broadcast->media_path) }}" target="_blank"
                   class="text-blue-600 text-sm underline mt-1 inline-block">Lihat Media Sebelumnya</a>
            </div>

            <div class="flex justify-between items-center">
                <a href="{{ route('broadcast.index') }}"
                   class="text-sm text-blue-600 hover:underline">← Kembali ke daftar</a>
                <button type="submit"
                        class="bg-yellow-600 hover:bg-yellow-700 text-white px-5 py-2 rounded shadow font-semibold">
                    🔁 Ulangi Kirim
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function () {
            $('#accounts').select2({
                placeholder: 'Pilih akun sosial media',
                width: '100%',
                allowClear: true
            });
        });
    </script>
    @endpush
</x-app-layout>
