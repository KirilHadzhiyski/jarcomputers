@if (session('status'))
    <div class="site-container mt-6">
        <div class="rounded-xl border border-primary/20 bg-primary/5 px-4 py-3 text-sm text-primary">
            {{ session('status') }}
        </div>
    </div>
@endif

@if ($errors->any())
    <div class="site-container mt-6">
        <div class="rounded-xl border border-destructive/20 bg-destructive/5 px-4 py-3 text-sm text-destructive">
            <p class="font-medium">Има проблем с изпратените данни.</p>
            <ul class="mt-2 flex flex-col gap-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
