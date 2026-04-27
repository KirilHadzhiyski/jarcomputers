<div class="mt-8 flex flex-wrap items-center gap-3 border-t border-border/70 pt-6">
    <button type="submit" class="btn-primary">{{ $submitLabel ?? 'Save' }}</button>
    <a href="{{ $cancelRoute }}" class="btn-secondary-dark">Cancel</a>

    @isset($deleteRoute)
        <form method="POST" action="{{ $deleteRoute }}" class="md:ml-auto">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-secondary-dark text-rose-600" onclick="return confirm('Delete this record?')">Delete</button>
        </form>
    @endisset
</div>
