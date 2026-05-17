<script setup>
import { ref, computed, onMounted } from 'vue';
import { useFetchApi } from '@/composables/useFetchApi';
import { usePolling } from '@/composables/usePolling';

const props = defineProps({
    token: { type: String, required: true },
    isAuthenticated: { type: Boolean, default: false },
    userId: { type: Number, default: null },
});

const { fetchApi } = useFetchApi();

const poll = ref(null);
const error = ref(null);
const loading = ref(true);
const selectedOptions = ref([]);
const voteError = ref(null);
const voteSuccess = ref(false);

const isExpired = computed(() => {
    if (!poll.value?.ends_at) return false;
    return new Date() > new Date(poll.value.ends_at);
});

const canVote = computed(() => {
    return props.isAuthenticated && !isExpired.value && !poll.value?.is_draft;
});

const totalVotes = computed(() => {
    if (!poll.value?.options) return 0;
    return poll.value.options.reduce((sum, o) => sum + (o.votes_count ?? 0), 0);
});

const showResults = computed(() => {
    return poll.value?.results_public
        || isExpired.value
        || voteSuccess.value
        || poll.value?.user_id === props.userId;
});

async function loadPoll() {
    try {
        const data = await fetchApi({ url: `/polls/${props.token}` });
        poll.value = data;
    } catch (e) {
        error.value = 'Sondage introuvable.';
    } finally {
        loading.value = false;
    }
}

async function submitVote() {
    voteError.value = null;
    try {
        await fetchApi({
            url: `/polls/${props.token}/vote`,
            method: 'POST',
            data: { option_ids: selectedOptions.value },
        });
        voteSuccess.value = true;
        await loadPoll();
    } catch (e) {
        voteError.value = e.data?.message ?? 'Erreur lors du vote.';
    }
}

function toggleOption(id) {
    if (poll.value.allow_multiple_choices) {
        if (selectedOptions.value.includes(id)) {
            selectedOptions.value = selectedOptions.value.filter(o => o !== id);
        } else {
            selectedOptions.value.push(id);
        }
    } else {
        selectedOptions.value = [id];
    }
}

function getPercent(option) {
    if (totalVotes.value === 0) return 0;
    return Math.round((option.votes_count ?? 0) / totalVotes.value * 100);
}

onMounted(async () => {
    await loadPoll();
});

usePolling(loadPoll, 5000);
</script>

<template>
    <div class="max-w-xl mx-auto p-4">
        <div v-if="loading">Chargement...</div>
        <div v-else-if="error">{{ error }}</div>
        <div v-else-if="poll">
            <h1 class="text-2xl font-bold mb-2">{{ poll.question }}</h1>

            <p v-if="poll.is_draft" class="text-gray-500">Ce sondage n'est pas encore disponible.</p>

            <template v-if="!poll.is_draft">
                <p v-if="isExpired" class="text-red-500 mb-4">Ce sondage est terminé.</p>
                <div v-if="voteSuccess" class="text-green-600 mb-4">Vote enregistré !</div>
                <div v-if="voteError" class="text-red-500 mb-4">{{ voteError }}</div>

                <div class="space-y-2 mb-6">
                    <div v-for="option in poll.options" :key="option.id">
                        <div
                            v-if="canVote && !voteSuccess"
                            class="flex items-center gap-2 cursor-pointer p-2 border rounded"
                            :class="{ 'border-blue-500 bg-blue-50': selectedOptions.includes(option.id) }"
                            @click="toggleOption(option.id)"
                        >
                            <span>{{ option.label }}</span>
                        </div>
                        <div v-else class="p-2 border rounded">
                            <div class="flex justify-between mb-1">
                                <span>{{ option.label }}</span>
                                <span v-if="showResults">{{ option.votes_count ?? 0 }} votes</span>
                            </div>
                            <div v-if="showResults" class="w-full bg-gray-200 rounded h-2">
                                <div
                                    class="bg-blue-500 h-2 rounded"
                                    :style="{ width: getPercent(option) + '%' }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>

                <button
                    v-if="canVote && !voteSuccess"
                    @click="submitVote"
                    :disabled="selectedOptions.length === 0"
                    class="px-4 py-2 bg-blue-600 text-white rounded disabled:opacity-50 mb-6"
                >
                    Voter
                </button>

                <div v-if="showResults && poll.options" class="mt-6">
                    <h2 class="text-lg font-semibold mb-2">Résultats</h2>
                    <div v-for="option in poll.options" :key="option.id" class="mb-3">
                        <div class="flex justify-between text-sm mb-1">
                            <span>{{ option.label }}</span>
                            <span>{{ getPercent(option) }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded h-4">
                            <div
                                class="bg-blue-500 h-4 rounded"
                                :style="{ width: getPercent(option) + '%' }"
                            ></div>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 mt-2">Total : {{ totalVotes }} votes</p>
                </div>

                <p v-if="!isAuthenticated && !poll.results_public" class="text-gray-500 mt-4">
                    Connectez-vous pour voter.
                </p>
            </template>
        </div>
    </div>
</template>