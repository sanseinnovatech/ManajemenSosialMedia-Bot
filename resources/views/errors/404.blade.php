<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>404 • Cieee gak ketemu</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    html,body{font-family:ui-sans-serif,system-ui,"Figtree"}
    /* Micro-animations (halus & tidak mengganggu) */
    @keyframes floaty {0%{transform:translateY(0)}50%{transform:translateY(-10px)}100%{transform:translateY(0)}}
    @keyframes pulseSoft {0%,100%{transform:scale(1)}50%{transform:scale(1.02)}}
    @keyframes shimmer {0%{background-position:-200% 0}100%{background-position:200% 0}}
    @keyframes ticker {0%{transform:translateX(100%)}100%{transform:translateX(-100%)}}
    @keyframes fall {
      0%{transform:translateY(-10vh) rotate(0deg);opacity:.8}
      100%{transform:translateY(110vh) rotate(360deg);opacity:.8}
    }

    .floaty {animation: floaty 6s ease-in-out infinite}
    .pulse-soft {animation: pulseSoft 4s ease-in-out infinite}
    .ticker {animation: ticker 18s linear infinite}
    .confetti {position:absolute;top:-10vh;width:7px;height:12px;border-radius:2px;opacity:.85}

    /* Gradien halus */
    .bg-soft {
      background-image:
        radial-gradient(30rem 30rem at 20% 10%, rgba(99,102,241,.08), transparent 55%),
        radial-gradient(36rem 36rem at 80% 0%, rgba(236,72,153,.08), transparent 55%),
        linear-gradient(to bottom, #f8fafc, #ffffff);
    }
    .bg-soft-dark {
      background-image:
        radial-gradient(30rem 30rem at 20% 10%, rgba(99,102,241,.12), transparent 55%),
        radial-gradient(36rem 36rem at 80% 0%, rgba(236,72,153,.12), transparent 55%),
        linear-gradient(to bottom, #0b0f19, #0b0f19);
    }
  </style>
</head>
<body class="min-h-screen text-slate-800 dark:text-slate-100 bg-soft dark:bg-soft-dark">

  <!-- Confetti ringan (jumlah kecil & lambat) -->
  <div id="confetti" class="pointer-events-none fixed inset-0 -z-10"></div>

  <main class="min-h-screen flex items-center justify-center p-6">
    <section class="w-full max-w-3xl">
      <div class="relative overflow-hidden rounded-3xl border border-slate-200/70 bg-white/80 backdrop-blur shadow-xl dark:border-slate-700 dark:bg-slate-900/60">
        <!-- Header strip -->
        <div class="h-1 w-full bg-gradient-to-r from-indigo-500 via-fuchsia-500 to-emerald-500"
             style="background-size:200% 100%; animation: shimmer 14s linear infinite;"></div>

        <div class="p-8 md:p-10">
          <div class="flex items-start gap-5">
            <!-- Icon -->
            <div class="floaty grid h-14 w-14 place-items-center rounded-2xl bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
              <svg class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M10.29 3.86a2 2 0 0 1 3.42 0l8.14 13.56A2 2 0 0 1 20.14 20H3.86a2 2 0 0 1-1.71-2.58L10.29 3.86ZM12 9a1 1 0 0 0-1 1v3.5a1 1 0 1 0 2 0V10a1 1 0 0 0-1-1Zm0 7.75a1.25 1.25 0 1 0 0-2.5 1.25 1.25 0 0 0 0 2.5Z"/>
              </svg>
            </div>

            <div class="flex-1 min-w-0">
              <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">
                404 • <span class="text-indigo-600 dark:text-indigo-400">Cieee gak ketemu</span> 🤭
              </h1>
              <p class="mt-3 text-slate-600 dark:text-slate-300 leading-relaxed">
                Halaman yang kamu cari tidak tersedia.
                Kalau ini <span class="font-semibold">/register</span>, memang kami
                <span class="font-semibold text-amber-600 dark:text-amber-400">nonaktifkan</span> demi keamanan.
              </p>

              <!-- CTA -->
              <div class="mt-6 flex flex-wrap items-center gap-3">
                @if (Route::has('login'))
                  <a href="{{ route('login') }}"
                     class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-white font-semibold shadow hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-indigo-500">
                    Masuk
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m0 0-6-6m6 6-6 6"/>
                    </svg>
                  </a>
                @endif

                <a href="{{ url('/') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-slate-800 font-semibold hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-100 dark:border-slate-700 dark:hover:bg-slate-700">
                  Kembali ke Beranda
                </a>

                <button type="button" onclick="poke()"
                        class="inline-flex items-center gap-2 rounded-xl border border-fuchsia-300/60 bg-fuchsia-50 px-4 py-2.5 text-fuchsia-700 font-semibold hover:bg-fuchsia-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-fuchsia-400 dark:bg-fuchsia-900/30 dark:text-fuchsia-200 dark:border-fuchsia-800/60">
                  Ciee, sentuh aku
                </button>
              </div>

              <!-- Ticker halus -->
              <div class="relative mt-8 overflow-hidden rounded-xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/60">
                <div class="whitespace-nowrap py-2 text-sm text-slate-600 dark:text-slate-300 ticker">
                  • Cieee… gak ketemu • Register dimatikan • Coba login dulu • Atau balik ke beranda • Jaga keamanan akunmu ya •
                </div>
              </div>
            </div>
          </div>

          <!-- Emoji bar (sederhana) -->
          <div class="mt-8 grid grid-cols-6 gap-2 text-xl select-none text-slate-500 dark:text-slate-400">
            <div class="text-center">🙃</div>
            <div class="text-center">🤡</div>
            <div class="text-center">🫠</div>
            <div class="text-center">😵‍💫</div>
            <div class="text-center">🤖</div>
            <div class="text-center">🌀</div>
          </div>
        </div>
      </div>

      <!-- Footnote -->
      <p class="mt-6 text-center text-xs text-slate-500 dark:text-slate-400">
        Laravel v{{ Illuminate\Foundation\Application::VERSION }} • PHP v{{ PHP_VERSION }}
      </p>
    </section>
  </main>

  <script>
    // Confetti ringan (20 pcs, jatuh lambat)
    (function(){
      const wrap = document.getElementById('confetti');
      const colors = ['#6366f1','#22c55e','#f43f5e','#f59e0b','#06b6d4','#a855f7'];
      const pieces = 20;
      for(let i=0;i<pieces;i++){
        const c = document.createElement('div');
        c.className = 'confetti';
        c.style.left = (Math.random()*100)+'vw';
        c.style.background = colors[(Math.random()*colors.length)|0];
        const dur = 7 + Math.random()*8;     // 7-15s
        const delay = Math.random()*-dur;     // offset
        c.style.animation = `fall ${dur}s linear ${delay}s infinite`;
        c.style.opacity = 0.65 + Math.random()*0.25;
        wrap.appendChild(c);
      }
    })();

    // Micro-interaction: toast singkat + title blink
    function poke(){
      const t = document.title;
      document.title = '😜 Cieee…';
      setTimeout(()=>document.title=t, 900);

      const toast = document.createElement('div');
      toast.className = 'fixed bottom-6 right-6 z-50 rounded-xl bg-indigo-600 text-white text-sm px-4 py-2 shadow-lg pulse-soft';
      toast.textContent = 'Hehe… yang ini juga bukan jalan keluarnya 😉';
      document.body.appendChild(toast);
      setTimeout(()=>toast.remove(), 1500);
    }
  </script>
</body>
</html>
