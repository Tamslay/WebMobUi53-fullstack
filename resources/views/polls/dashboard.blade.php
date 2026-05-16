<x-default-layout>
    <x-slot:scripts>
        @vite(['resources/js/poll-dashboard.js'])
    </x-slot>

    <x-slot:title>
        Sondages
    </x-slot>

    <div
        id="app"
        data-props="{{ json_encode([
            'polls' => $polls,
            'loginUrl' => route('login'),
            'username' => auth()->user()->username ?? null,
        ]) }}"
    ></div>
</x-default-layout>