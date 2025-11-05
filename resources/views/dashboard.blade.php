<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    @php
        $broadcasts = isset($broadcasts) ? $broadcasts : collect();
        $accounts   = isset($accounts)   ? $accounts   : collect();
    @endphp

    @php
        $accItems = ($accounts instanceof \Illuminate\Pagination\AbstractPaginator) ? $accounts->getCollection() : collect($accounts);
        $bcItems  = ($broadcasts instanceof \Illuminate\Pagination\AbstractPaginator) ? $broadcasts->getCollection() : collect($broadcasts);

        $bcTotal     = $bcItems->count();
        $bcSuccess   = $bcItems->where('status','success')->count();
        $bcFailed    = max(0, $bcTotal - $bcSuccess);
        $successRate = $bcTotal ? round(($bcSuccess / $bcTotal) * 100) : 0;

        $now          = now();
        $accTotal     = $accItems->count();
        $activeTokens = 0;
        $expSoon      = 0; // <= 5 menit
        $platforms    = ['facebook'=>0,'instagram'=>0,'x'=>0];

        foreach ($accItems as $acc) {
            $lastModified = $acc->updated_at ?? $acc->created_at;
            $expiredAt    = $lastModified?->copy()->addMinutes(10);
            $isExpired    = $expiredAt ? $now->isAfter($expiredAt) : true;
            $minutesLeft  = (!$isExpired && $expiredAt) ? $now->diffInMinutes($expiredAt, false) : 0;

            if (!$isExpired) $activeTokens++;
            if (!$isExpired && $minutesLeft <= 5) $expSoon++;

            if (isset($platforms[$acc->platform])) $platforms[$acc->platform]++;
        }

        $daysArray = isset($series7) && is_array($series7) ? $series7 : (function() use ($bcItems, $now) {
            $days = [];
            for ($i=6; $i>=0; $i--) { $days[$now->copy()->subDays($i)->format('Y-m-d')] = 0; }
            foreach ($bcItems as $b) {
                $d = $b->created_at?->format('Y-m-d');
                if ($d && array_key_exists($d, $days)) $days[$d]++;
            }
            return $days;
        })();

        $maxVal = max(1, max($daysArray));
        $points = [];
        $w = 160; $h = 44; $pad = 0;
        $i = 0; $n = count($daysArray) - 1;
        foreach ($daysArray as $val) {
            $x = $pad + ($w - 2*$pad) * ($i / max(1,$n));
            $y = $h - $pad - ($h - 2*$pad) * ($val / $maxVal);
            $points[] = round($x,1).','.round($y,1);
            $i++;
        }

        $recentBroadcasts = $bcItems->sortByDesc('created_at')->take(5);

        $soonAccounts = $accItems->filter(function($acc) use ($now){
            $last = $acc->updated_at ?? $acc->created_at;
            if (!$last) return false;
            $expiredAt = $last->copy()->addMinutes(10);
            $isExpired = $now->isAfter($expiredAt);
            $minutesLeft = $isExpired ? 0 : $now->diffInMinutes($expiredAt, false);
            return !$isExpired && $minutesLeft <= 5;
        })->take(5);
    @endphp

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500">Total Broadcast</p>
                            <h3 class="mt-1 text-2xl font-bold text-gray-900">{{ $bcTotal }}</h3>
                        </div>
                        <div class="rounded-lg bg-blue-50 p-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <svg width="160" height="44" viewBox="0 0 160 44" class="w-full">
                            <polyline fill="none" stroke="currentColor" class="text-blue-500"
                                      points="{{ implode(' ', $points) }}" stroke-width="2" />
                        </svg>
                        <p class="mt-1 text-xs text-gray-500">7 hari terakhir</p>
                    </div>
                </div>


                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium text-gray-500">Success Rate</p>
                    <div class="mt-1 flex items-end gap-2">
                        <h3 class="text-2xl font-bold text-gray-900">{{ $successRate }}%</h3>
                        <span class="text-xs text-gray-500">{{ $bcSuccess }} sukses / {{ $bcTotal }}</span>
                    </div>
                    <div class="mt-3 h-2 w-full rounded-full bg-gray-100">
                        <div class="h-2 rounded-full bg-green-500" style="width: {{ $successRate }}%"></div>
                    </div>
                </div>


                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium text-gray-500">Broadcast Gagal</p>
                    <h3 class="mt-1 text-2xl font-bold text-gray-900">{{ $bcFailed }}</h3>
                    <p class="mt-1 text-xs text-red-600">Perlu dicek & retry</p>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500">Akun Terhubung</p>
                            <h3 class="mt-1 text-2xl font-bold text-gray-900">{{ $accTotal }}</h3>
                        </div>
                        <div class="rounded-lg bg-sky-50 p-2">
                            <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M18 9v3m0 0v3m0-3h3m-3 0h-3M8 9h4m-2-2a2 2 0 00-2 2v6a2 2 0 104 0V9a2 2 0 00-2-2z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3 grid grid-cols-3 gap-2 text-xs">
                        <div class="rounded-md bg-gray-50 px-2 py-1.5">
                            <p class="text-gray-500">Aktif</p>
                            <p class="font-semibold text-gray-900">{{ $activeTokens }}</p>
                        </div>
                        <div class="rounded-md bg-orange-50 px-2 py-1.5">
                            <p class="text-orange-700">Segera Habis</p>
                            <p class="font-semibold text-orange-800">{{ $expSoon }}</p>
                        </div>
                        <div class="rounded-md bg-gray-50 px-2 py-1.5">
                            <p class="text-gray-500">Expired/Off</p>
                            <p class="font-semibold text-gray-900">{{ max(0, $accTotal - $activeTokens) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm lg:col-span-2">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900">Broadcast Terbaru</h3>
                        <a href="{{ route('broadcast.create') }}"
                           class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm">
                            + Buat Broadcast
                        </a>
                    </div>

                    <div class="mt-3 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-gray-600">
                                <tr>
                                    <th class="px-3 py-2 text-left">Caption</th>
                                    <th class="px-3 py-2 text-left">Media</th>
                                    <th class="px-3 py-2 text-left">Tanggal</th>
                                    <th class="px-3 py-2 text-left">Status</th>
                                    <th class="px-3 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($recentBroadcasts as $b)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2 text-gray-900">{{ \Illuminate\Support\Str::limit($b->caption, 50) }}</td>
                                        <td class="px-3 py-2">
                                            @if($b->media_path)
                                                <a class="text-blue-600 hover:underline"
                                                   href="{{ asset('storage/'.$b->media_path) }}" target="_blank">Lihat</a>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-gray-600">{{ $b->created_at?->format('d M Y • H:i') }}</td>
                                        <td class="px-3 py-2">
                                            @if($b->status === 'success')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Terkirim
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Gagal
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            @if($b->status === 'success')
                                                <a href="{{ asset('storage/'.$b->media_path) }}" target="_blank"
                                                   class="text-blue-600 hover:underline text-xs">Lihat</a>
                                            @else
                                                <a href="{{ route('broadcast.retry', $b->id) }}"
                                                   class="text-orange-600 hover:underline text-xs">Retry</a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-3 py-8 text-center text-gray-500">Belum ada broadcast.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-semibold text-gray-900">Distribusi Platform</h3>
                    <div class="mt-3 space-y-3">
                        @php
                            $totalPlat = max(1, array_sum($platforms));
                            $bars = [
                                ['label'=>'Facebook','val'=>$platforms['facebook'],'color'=>'bg-blue-500'],
                                ['label'=>'Instagram','val'=>$platforms['instagram'],'color'=>'bg-pink-500'],
                                ['label'=>'X (Twitter)','val'=>$platforms['x'],'color'=>'bg-gray-700'],
                            ];
                        @endphp
                        @foreach($bars as $row)
                            <div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-600">{{ $row['label'] }}</span>
                                    <span class="text-gray-800 font-medium">{{ $row['val'] }}</span>
                                </div>
                                <div class="mt-1 h-2 w-full rounded-full bg-gray-100">
                                    <div class="h-2 rounded-full {{ $row['color'] }}"
                                         style="width: {{ round(($row['val']/$totalPlat)*100) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        <h4 class="text-xs font-semibold text-gray-900">Token Hampir Kedaluwarsa (≤5m)</h4>
                        <ul class="mt-2 space-y-2">
                            @forelse($soonAccounts as $acc)
                                @php
                                    $last  = $acc->updated_at ?? $acc->created_at;
                                    $left  = $last? now()->diffInMinutes($last->copy()->addMinutes(10), false) : 0;
                                @endphp
                                <li class="text-sm flex items-center justify-between">
                                    <span class="text-gray-700">{{ $acc->username }} <span class="text-gray-400">({{ strtoupper($acc->platform) }})</span></span>
                                    <span class="text-orange-700 text-xs font-semibold">{{ max(0,$left) }}m</span>
                                </li>
                            @empty
                                <li class="text-xs text-gray-500">Tidak ada yang segera habis.</li>
                            @endforelse
                        </ul>
                        <div class="mt-3">
                            <a href="{{ route('accounts.create') }}"
                               class="inline-flex items-center px-3 py-1.5 text-xs bg-sky-600 hover:bg-sky-700 text-white rounded-lg">
                                + Tambah Akun
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Aksi Cepat</h3>
                        <p class="text-xs text-gray-500 mt-1">Mulai broadcast baru atau kelola akun sosial media</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('broadcast.create') }}"
                           class="inline-flex items-center px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg">
                            Mulai Broadcast
                        </a>
                        <a href="{{ route('accounts.create') }}"
                           class="inline-flex items-center px-4 py-2 text-sm bg-sky-600 hover:bg-sky-700 text-white font-semibold rounded-lg">
                            Tambah Akun
                        </a>
                        <a href="{{ route('accounts.index') }}"
                           class="inline-flex items-center px-4 py-2 text-sm bg-gray-800 hover:bg-black text-white font-semibold rounded-lg">
                            Kelola Akun
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
