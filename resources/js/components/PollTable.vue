<script setup>
import { ref } from 'vue';
import { useFetchApi } from '@/composables/useFetchApi';

const props = defineProps({
    polls: { type: Array, required: true },
});

const emit = defineEmits(['delete', 'edit']);

const { fetchApi } = useFetchApi();

function shareLink(poll) {
    return window.location.origin + '/polls/' + poll.secret_token;
}

function copyLink(poll) {
    navigator.clipboard.writeText(shareLink(poll));
}
</script>

<template>
    <div class="space-y-4">
        <p v-if="polls.length === 0">Aucun sondage.</p>

        <div v-for="poll in polls" :key="poll.id" class="border rounded p-4">
            <div class="flex justify-between items-start flex-wrap gap-2">
                <div>
                    <p class="font-semibold">{{ poll.question }}</p>
                    <p class="text-sm text-gray-500">
                        {{ poll.is_draft ? 'Brouillon' : 'Lancé' }}
                        <span v-if="poll.ends_at"> · Fin : {{ new Date(poll.ends_at).toLocaleString() }}</span>
                    </p>
                </div>
                <div class="flex gap-2 flex-wrap">
                    <button
                        v-if="!poll.is_draft"
                        @click="copyLink(poll)"
                        class="px-3 py-1 bg-gray-100 rounded text-sm"
                    >
                        Copier le lien
                    </button>
                    <button
                        @click="emit('edit', poll)"
                        class="px-3 py-1 bg-yellow-400 rounded text-sm"
                    >
                        Modifier
                    </button>
                    <button
                        @click="emit('delete', poll.id)"
                        class="px-3 py-1 bg-red-500 text-white rounded text-sm"
                    >
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>