<div class="space-y-6">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="text-sm font-medium text-indigo-600">AI Decision Support</p><h1 class="text-2xl font-semibold text-slate-900">Demand Forecasting</h1><p class="text-sm text-slate-500">Hasil hanya ditampilkan jika inference berhasil dan tersimpan di database.</p></div>
        <form wire:submit="runForecast" class="flex flex-wrap items-end gap-3">
            <label class="text-sm text-slate-600">Produk<select wire:model.live="productId" class="mt-1 block rounded-lg border-slate-300 text-sm"><option value="">Pilih produk</option>@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->sku }} · {{ $product->name }}</option>@endforeach</select></label>
            <label class="text-sm text-slate-600">Horizon<select wire:model="horizon" class="mt-1 block rounded-lg border-slate-300 text-sm"><option value="7">7 hari</option><option value="30">30 hari</option></select></label>
            <button wire:loading.attr="disabled" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white disabled:opacity-50">Jalankan forecast</button>
        </form>
    </div>
    @if($forecasts->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center"><p class="font-semibold text-slate-800">Belum ada hasil forecast yang berhasil</p><p class="mt-1 text-sm text-slate-500">Pastikan dataset historis cukup dan worker AI internal sedang berjalan. Data simulasi diberi label terpisah dan tidak digunakan sebagai hasil bisnis.</p></div>
    @else
        <div class="grid gap-6 lg:grid-cols-3"><div class="rounded-2xl border border-slate-200 bg-white p-5 lg:col-span-2"><h2 class="font-semibold text-slate-900">Actual vs forecast</h2><canvas id="forecast-chart" class="mt-4" height="120"></canvas></div><div class="rounded-2xl border border-slate-200 bg-white p-5"><p class="text-sm text-slate-500">Model</p><p class="mt-1 font-semibold text-slate-900">{{ $forecasts->first()->model_name }}</p><p class="mt-4 text-sm text-slate-500">Versi</p><p class="mt-1 font-mono text-xs text-slate-700">{{ $forecasts->first()->model_version }}</p><p class="mt-4 text-sm text-slate-500">WAPE</p><p class="mt-1 font-semibold text-slate-900">{{ number_format((float) $forecasts->first()->wape * 100, 2) }}%</p></div></div>
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-5 py-3 text-left">Tanggal</th><th class="px-5 py-3 text-right">Prediksi</th><th class="px-5 py-3 text-left">Status</th></tr></thead><tbody class="divide-y divide-slate-100">@foreach($forecasts as $forecast)<tr><td class="px-5 py-3">{{ $forecast->forecast_date->format('d M Y') }}</td><td class="px-5 py-3 text-right font-medium">{{ number_format((float) $forecast->predicted_quantity, 2) }} {{ $forecast->unit }}</td><td class="px-5 py-3"><span class="rounded-full bg-emerald-50 px-2 py-1 text-xs text-emerald-700">{{ $forecast->status }}</span></td></tr>@endforeach</tbody></table></div>
    @endif
    @script
    <script>
        const forecastChartData = @js($chart);
        if (window.Chart && document.getElementById('forecast-chart')) new Chart(document.getElementById('forecast-chart'), {type:'line', data:{labels:forecastChartData.labels,datasets:[{label:'Aktual',data:forecastChartData.actual,borderColor:'#64748b'},{label:'Forecast',data:forecastChartData.forecast,borderColor:'#4f46e5'}]}, options:{responsive:true, interaction:{mode:'index',intersect:false}, scales:{y:{beginAtZero:true}}}});
    </script>
    @endscript
</div>
