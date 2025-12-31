<div class="w-full">
    <div class="max-w-5xl mx-auto px-6 py-6">
        @include('partials.settings-heading')

        <x-settings.layout :heading="__('Appearance')" :subheading=" __('Update the appearance settings for your account')">
            <div class="my-6 w-full">
                <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
                    <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
                    <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
                    <flux:radio value="system" icon="computer-desktop">{{ __('System') }}</flux:radio>
                </flux:radio.group>
            </div>
        </x-settings.layout>
    </div>
</div>
