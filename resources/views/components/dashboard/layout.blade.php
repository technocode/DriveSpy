<div class="w-full">
    <div class="mb-6">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>
    </div>

    <div class="w-full">
        {{ $slot }}
    </div>
</div>
