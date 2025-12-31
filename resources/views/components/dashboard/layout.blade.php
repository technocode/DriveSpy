<div class="w-full">
    <div class="max-w-5xl mx-auto px-6 py-6">
        <div class="mb-6">
            <flux:heading>{{ $heading ?? '' }}</flux:heading>
            <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>
        </div>

        <div class="w-full">
            {{ $slot }}
        </div>
    </div>
</div>
