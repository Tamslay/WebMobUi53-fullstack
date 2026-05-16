<x-default-layout>
    <x-slot:scripts>
        @vite(['resources/js/poll-vote.js'])
    </x-slot>

    <x-slot:title>
        Sondage
    </x-slot>

    <div
        id="app-vote"
        data-props="{{ json_encode([
            'token' => $token,
            'isAuthenticated' => auth()->check(),
            'userId' => auth()->id(),
        ]) }}"
    ></div>
</x-default-layout>