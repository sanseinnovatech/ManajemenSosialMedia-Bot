<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Manajemen Sosial Media</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: { sans: ['Figtree','ui-sans-serif','system-ui'] },
            colors: {
              primary: {
                50:'#eef2ff', 100:'#e0e7ff', 200:'#c7d2fe', 300:'#a5b4fc',
                400:'#818cf8', 500:'#6366f1', 600:'#4f46e5', 700:'#4338ca',
                800:'#3730a3', 900:'#312e81'
              }
            }
          }
        },
        darkMode: 'class'
      }
    </script>
    <style>
      html,body{font-family:'Figtree',ui-sans-serif,system-ui}
    </style>
  </head>
  <body class="antialiased bg-gradient-to-b from-primary-50 to-white dark:from-zinc-900 dark:to-black text-zinc-800 dark:text-zinc-100">
    <div class="relative min-h-screen">
      <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
        <div class="absolute -top-32 -left-32 h-80 w-80 rounded-full bg-primary-300/40 blur-3xl dark:bg-primary-700/25"></div>
        <div class="absolute -bottom-24 -right-24 h-80 w-80 rounded-full bg-fuchsia-300/40 blur-3xl dark:bg-fuchsia-700/25"></div>
      </div>

      <header class="max-w-7xl mx-auto px-6 py-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="h-10 w-10 rounded-xl bg-primary-600 grid place-items-center shadow-lg shadow-primary-600/30">
            <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path d="M12 3c4.97 0 9 3.582 9 8 0 4.42-4.03 8-9 8a10.7 10.7 0 0 1-2.5-.3l-4.19 1.396a1 1 0 0 1-1.287-1.225l1.017-3.391A7.83 7.83 0 0 1 3 11c0-4.418 4.03-8 9-8Zm-2.25 6.25a.75.75 0 0 0 0 1.5h4.5a.75.75 0 0 0 0-1.5h-4.5Zm0 3a.75.75 0 0 0 0 1.5h6a.75.75 0 0 0 0-1.5h-6Z"/>
            </svg>
          </div>
          <span class="text-lg font-semibold">Manajemen Sosial Media</span>
        </div>

        @if (Route::has('login'))
          <nav class="flex items-center gap-3">
            @auth
              <a href="{{ url('/dashboard') }}"
                 class="inline-flex items-center gap-2 rounded-xl border border-zinc-300/60 bg-white px-4 py-2 text-sm font-medium text-zinc-800 hover:bg-zinc-50 dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 dark:hover:bg-zinc-700">
                Dashboard
              </a>
            @else
              <a href="{{ route('login') }}"
                 class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary-600/30 hover:bg-primary-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-primary-500">
                Masuk
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H3m12 0-4-4m4 4-4 4M21 3v18"/>
                </svg>
              </a>
            @endauth
          </nav>
        @endif
      </header>

      <main class="max-w-7xl mx-auto px-6 pb-24">
        <section class="grid lg:grid-cols-2 gap-8 items-center">
          <div class="space-y-6">
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold leading-tight">
              Selamat datang di <span class="text-primary-600">Manajemen Sosial Media</span>
            </h1>
            <p class="text-zinc-600 dark:text-zinc-300 text-lg">
              Kamu bisa <span class="font-semibold">posting otomatis</span> ke berbagai akun sosial mediamu,
              atur <span class="font-semibold">jadwal terencana</span>, dan pantau performanya dalam satu sistem.
            </p>

            @guest
              <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('login') }}"
                   class="inline-flex items-center gap-2 rounded-2xl bg-primary-600 px-6 py-3 text-white font-semibold shadow-md hover:bg-primary-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-primary-500">
                  Masuk untuk Mulai
                  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m0 0-6-6m6 6-6 6"/>
                  </svg>
                </a>
                <span class="text-sm text-zinc-500 dark:text-zinc-400">Akses aman • Tanpa pendaftaran publik</span>
              </div>
            @else
              <a href="{{ url('/dashboard') }}"
                 class="inline-flex items-center gap-2 rounded-2xl bg-primary-600 px-6 py-3 text-white font-semibold shadow-md hover:bg-primary-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-primary-500">
                Buka Dashboard
              </a>
            @endguest
          </div>

          <div class="relative">
            <div class="rounded-3xl border border-zinc-200/70 bg-white/70 backdrop-blur p-4 shadow-xl dark:bg-zinc-900/50 dark:border-zinc-800">
              <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-zinc-200 p-4 dark:border-zinc-800">
                  <div class="flex items-center gap-2 mb-2">
                    <div class="h-8 w-8 rounded-lg bg-primary-100 grid place-items-center text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">⏱️</div>
                    <h3 class="font-semibold">Penjadwalan</h3>
                  </div>
                  <p class="text-sm text-zinc-600 dark:text-zinc-400">Susun kalender konten dan biarkan sistem memposting tepat waktu.</p>
                </div>

                <div class="rounded-2xl border border-zinc-200 p-4 dark:border-zinc-800">
                  <div class="flex items-center gap-2 mb-2">
                    <div class="h-8 w-8 rounded-lg bg-primary-100 grid place-items-center text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">🤖</div>
                    <h3 class="font-semibold">Auto-Post</h3>
                  </div>
                  <p class="text-sm text-zinc-600 dark:text-zinc-400">Terbitkan ke banyak platform sekaligus, konsisten & efisien.</p>
                </div>

                <div class="rounded-2xl border border-zinc-200 p-4 dark:border-zinc-800">
                  <div class="flex items-center gap-2 mb-2">
                    <div class="h-8 w-8 rounded-lg bg-primary-100 grid place-items-center text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">📊</div>
                    <h3 class="font-semibold">Analitik</h3>
                  </div>
                  <p class="text-sm text-zinc-600 dark:text-zinc-400">Lihat reach, engangement, & performa konten dalam satu dashboard.</p>
                </div>

                <div class="rounded-2xl border border-zinc-200 p-4 dark:border-zinc-800">
                  <div class="flex items-center gap-2 mb-2">
                    <div class="h-8 w-8 rounded-lg bg-primary-100 grid place-items-center text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">👥</div>
                    <h3 class="font-semibold">Multi-Akun</h3>
                  </div>
                  <p class="text-sm text-zinc-600 dark:text-zinc-400">Kelola banyak brand/akun sekaligus dengan kontrol akses yang rapi.</p>
                </div>
              </div>

              <div class="mt-4 rounded-xl bg-zinc-50 p-3 text-xs text-zinc-600 border border-zinc-200 dark:bg-zinc-900 dark:text-zinc-400 dark:border-zinc-800">
                <div class="flex items-center gap-2">
                  <span class="h-2 w-2 rounded-full bg-green-500"></span>
                  Integrasi aman—token terenkripsi & audit log aktif.
                </div>
              </div>
            </div>
          </div>
        </section>

        <section class="mt-16">
          <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-md dark:bg-zinc-900 dark:border-zinc-800">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
              <div>
                <h3 class="text-lg font-semibold">Mulai otomasi kontenmu hari ini</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Akses hanya untuk pengguna terdaftar internal. Hubungi admin bila memerlukan akun.</p>
              </div>
              @guest
                <a href="{{ route('login') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg hover:bg-primary-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-primary-500">
                  Masuk Sekarang
                </a>
              @else
                <a href="{{ url('/dashboard') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-zinc-300 bg-white px-5 py-2.5 text-sm font-semibold text-zinc-800 hover:bg-zinc-50 dark:bg-zinc-800 dark:text-zinc-100 dark:border-zinc-700 dark:hover:bg-zinc-700">
                  Ke Dashboard
                </a>
              @endguest
            </div>
          </div>
        </section>
      </main>

      <footer class="mt-10 pb-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        Laravel v{{ Illuminate\Foundation\Application::VERSION }} &middot; PHP v{{ PHP_VERSION }}
      </footer>
    </div>
  </body>
</html>
