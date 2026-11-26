@props(['portfolio', 'profile' => null, 'ownerName'])

<footer class="border-t border-ink/10 py-8 px-6 text-center text-xs sm:text-sm text-muted bg-paper">
    <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">
        <p>&copy; {{ date('Y') }} {{ $ownerName }}</p>
        <p class="text-xs text-muted/75">Portfolio CMS</p>
    </div>
</footer>
