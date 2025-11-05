<x-app-layout>
    <div class="max-w-xl mx-auto py-10 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">✏️ Edit Akun Sosial Media</h2>
            <a href="{{ route('accounts.index') }}"
               class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                ← Kembali ke Daftar
            </a>
        </div>

        @if($errors->any())
            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('accounts.update', $account) }}" class="space-y-5 bg-white p-6 shadow rounded">
            @csrf
            @method('PUT')

            {{-- Platform --}}
            <div>
                <label for="platform" class="block text-sm font-medium text-gray-700">Platform</label>
                <select id="platform" name="platform" onchange="toggleFields()" class="w-full border rounded p-2" required>
                    <option value="">-- Pilih --</option>
                    <option value="facebook" {{ old('platform', $account->platform) === 'facebook' ? 'selected' : '' }}>
                        Facebook Page
                    </option>
                    <option value="instagram" {{ old('platform', $account->platform) === 'instagram' ? 'selected' : '' }}>
                        Instagram (Business/Creator)
                    </option>
                    <option value="x" {{ old('platform', $account->platform) === 'x' ? 'selected' : '' }}>
                        Twitter (X)
                    </option>
                </select>
            </div>

            {{-- Username --}}
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Username/Nama Akun</label>
                <input id="username" name="username" type="text"
                       value="{{ old('username', $account->username) }}"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                       required>
            </div>

            {{-- Access Token --}}
            <div>
                <label for="access_token" class="block text-sm font-medium text-gray-700">Access Token</label>
                <input id="access_token" name="access_token" type="text"
                       value="{{ old('access_token', $account->access_token) }}"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                       required>
                <p id="token_hint" class="text-xs text-gray-500 mt-1"></p>
            </div>

            {{-- Access Token Secret (X only) --}}
            <div id="access_token_secret_wrapper" style="display:none;">
                <label for="access_token_secret" class="block text-sm font-medium text-gray-700">
                    Access Token Secret (X)
                </label>
                <input id="access_token_secret" name="access_token_secret" type="text"
                       value="{{ old('access_token_secret', $account->access_token_secret) }}"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            {{-- Page ID (FB & IG) --}}
            <div id="page_id_wrapper" style="display:none;">
                <label for="page_id" class="block text-sm font-medium text-gray-700">
                    Page ID (Facebook Page yang terhubung)
                </label>
                <input id="page_id" name="page_id" type="text"
                       value="{{ old('page_id', $account->page_id) }}"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                <p class="text-xs text-gray-500 mt-1">
                    Facebook: <b>wajib</b> diisi (Page tujuan posting).<br>
                    Instagram: <b>disarankan</b> diisi agar sistem bisa mengambil <em>IG User ID</em> otomatis bila kosong.
                </p>
            </div>

            {{-- IG User ID (IG only) --}}
            <div id="ig_user_id_wrapper" style="display:none;">
                <label for="ig_user_id" class="block text-sm font-medium text-gray-700">IG User ID (opsional)</label>
                <input id="ig_user_id" name="ig_user_id" type="text"
                       value="{{ old('ig_user_id', $account->ig_user_id ?? '') }}"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                <p class="text-xs text-gray-500 mt-1">
                    Minimal salah satu terisi: <b>IG User ID</b> atau <b>Page ID</b>.<br>
                    IG harus bertipe <b>Business/Creator</b> dan tertaut ke Page.
                </p>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded shadow">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    <script>
        function toggleFields() {
            const platform = document.getElementById('platform').value;

            const pageWrap = document.getElementById('page_id_wrapper');
            const igWrap   = document.getElementById('ig_user_id_wrapper');
            const xWrap    = document.getElementById('access_token_secret_wrapper');

            const pageId   = document.getElementById('page_id');
            const igUserId = document.getElementById('ig_user_id');
            const atSecret = document.getElementById('access_token_secret');

            const tokenHint = document.getElementById('token_hint');

            // reset default
            pageWrap.style.display = 'none';
            igWrap.style.display   = 'none';
            xWrap.style.display    = 'none';

            // reset required flags
            pageId?.removeAttribute('required');
            igUserId?.removeAttribute('required');
            atSecret?.removeAttribute('required');

            if (platform === 'facebook') {
                pageWrap.style.display = 'block';
                pageId?.setAttribute('required', 'required');
                tokenHint.textContent = 'Gunakan Page Access Token (bukan User Token).';
            } else if (platform === 'instagram') {
                pageWrap.style.display = 'block';
                igWrap.style.display   = 'block';
                // Untuk IG, validasi "minimal salah satu" dilakukan di server (controller).
                tokenHint.textContent = 'Gunakan Page Access Token dari Page yang terhubung ke akun IG Business/Creator.';
            } else if (platform === 'x') {
                xWrap.style.display = 'block';
                atSecret?.setAttribute('required', 'required');
                tokenHint.textContent = 'Gunakan Access Token & Access Token Secret (OAuth 1.0a).';
            } else {
                tokenHint.textContent = '';
            }
        }

        // Initial state on page load (agar old()/nilai awal tampil sesuai)
        document.addEventListener("DOMContentLoaded", toggleFields);
    </script>
</x-app-layout>
