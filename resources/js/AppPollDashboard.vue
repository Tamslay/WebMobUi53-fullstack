<script setup>
import { ref } from 'vue';
import { useFetchApi } from '@/composables/useFetchApi';
import PollTable from '@/components/PollTable.vue';
import PollForm from '@/components/PollForm.vue';

const props = defineProps({
    polls: { type: Array, default: () => [] },
});

const { fetchApi } = useFetchApi();

const polls = ref(props.polls);
const view = ref('list');
const editingPoll = ref(null);
const formError = ref(null);

function showCreate() {
    editingPoll.value = null;
    view.value = 'form';
    formError.value = null;
}

function showEdit(poll) {
    editingPoll.value = poll;
    view.value = 'form';
    formError.value = null;
}

function showList() {
    view.value = 'list';
    editingPoll.value = null;
    formError.value = null;
}

async function handleSubmit(data) {
    formError.value = null;
    try {
        if (editingPoll.value) {
            const updated = await fetchApi({
                url: '/polls/' + editingPoll.value.id,
                method: 'PUT',
                data,
            });
            const index = polls.value.findIndex(p => p.id === editingPoll.value.id);
            if (index !== -1) polls.value[index] = updated;
        } else {
            const created = await fetchApi({
                url: '/polls',
                method: 'POST',
                data,
            });
            polls.value.unshift(created);
        }
        showList();
    } catch (e) {
        formError.value = e.data?.message ?? 'Une erreur est survenue.';
    }
}

async function handleDelete(id) {
    if (!confirm('Supprimer ce sondage ?')) return;
    try {
        await fetchApi({ url: '/polls/' + id, method: 'DELETE' });
        polls.value = polls.value.filter(p => p.id !== id);
    } catch (e) {
        alert('Erreur lors de la suppression.');
    }
}
</script>

<template>
    <div class="max-w-2xl mx-auto p-4">
        <div v-if="view === 'list'">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold">Mes sondages</h1>
                <button @click="showCreate" class="px-4 py-2 bg-blue-600 text-white rounded">
                    Nouveau sondage
                </button>
            </div>
            <PollTable :polls="polls" @edit="showEdit" @delete="handleDelete" />
        </div>

        <div v-else-if="view === 'form'">
            <h1 class="text-2xl font-bold mb-4">
                {{ editingPoll ? 'Modifier le sondage' : 'Nouveau sondage' }}
            </h1>
            <p v-if="formError" class="text-red-500 mb-4">{{ formError }}</p>
            <PollForm :initial="editingPoll" @submit="handleSubmit" @cancel="showList" />
        </div>
    </div>
</template>