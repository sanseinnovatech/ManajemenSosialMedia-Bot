<x-app-layout>
    <div class="max-w-xl mx-auto py-10">
        <h2 class="text-xl font-bold mb-4">Tambah Akun Media Sosial</h2>

        @if($errors->any())
            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('accounts.store') }}" class="space-y-5 bg-white p-6 shadow rounded">
            @csrf

            <div>
                <label for="platform" class="block text-sm font-medium mb-1">Platform</label>
                <select id="platform" name="platform" onchange="toggleFields()" class="w-full border rounded p-2" required>
                    <option value="">-- Pilih --</option>
                    <option value="facebook" {{ old('platform')==='facebook'?'selected':'' }}>Facebook Page</option>
                    <option value="instagram" {{ old('platform')==='instagram'?'selected':'' }}>Instagram (Business/Creator)</option>
                    <option value="x" {{ old('platform')==='x'?'selected':'' }}>Twitter (X)</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Username/Nama Akun</label>
                <input type="text" name="username" value="{{ old('username') }}" class="w-full border rounded p-2" required>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Access Token</label>
                <input type="text" id="access_token" name="access_token" value="{{ old('access_token') }}" class="w-full border rounded p-2" required>
                <p id="token_hint" class="text-xs text-gray-500 mt-1"></p>
            </div>

            {{-- X / Twitter only --}}
            <div id="access_token_secret_wrapper" style="display: none;">
                <label class="block text-sm font-medium mb-1">Access Token Secret (X)</label>
                <input type="text" id="access_token_secret" name="access_token_secret" value="{{ old('access_token_secret') }}" class="w-full border rounded p-2">
            </div>

            {{-- Facebook & Instagram may use Page ID --}}
            <div id="page_id_wrapper" style="display: none;">
                <label class="block text-sm font-medium mb-1">Page ID (Facebook Page yang terhubung)</label>
                <input type="text" id="page_id" name="page_id" value="{{ old('page_id') }}" class="w-full border rounded p-2">
                <p class="text-xs text-gray-500 mt-1">
                    Untuk Facebook: wajib diisi (Page yang akan dipost).<br>
                    Untuk Instagram: disarankan isi Page ID agar sistem bisa mengambil <em>IG User ID</em> otomatis.
                </p>
            </div>

            {{-- Instagram only (optional if page_id provided) --}}
            <div id="ig_user_id_wrapper" style="display: none;">
                <label class="block text-sm font-medium mb-1">IG User ID (opsional)</label>
                <input type="text" id="ig_user_id" name="ig_user_id" value="{{ old('ig_user_id') }}" class="w-full border rounded p-2">
                <p class="text-xs text-gray-500 mt-1">
                    Jika dikosongkan, sistem akan mencoba mengambil dari Page ID terhubung.<br>
                    IG harus tipe <strong>Business/Creator</strong> dan sudah tertaut ke Page.
                </p>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Simpan Akun
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

            // reset visibility
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
                tokenHint.textContent = 'Gunakan Page Access Token (bukan user token).';
            } else if (platform === 'instagram') {
                pageWrap.style.display = 'block';
                igWrap.style.display   = 'block';
                // IG: minimal salah satu (page_id atau ig_user_id). Kita tidak pakai required di HTML, validasi di server.
                tokenHint.textContent = 'Gunakan Page Access Token dari Page yang terhubung ke akun IG Business/Creator.';
            } else if (platform === 'x') {
                xWrap.style.display = 'block';
                atSecret?.setAttribute('required', 'required');
                tokenHint.textContent = 'Gunakan Access Token & Access Token Secret (OAuth 1.0a).';
            } else {
                tokenHint.textContent = '';
            }
        }

        // init on load (agar old() kepake)
        document.addEventListener('DOMContentLoaded', toggleFields);
    </script>
</x-app-layout>
