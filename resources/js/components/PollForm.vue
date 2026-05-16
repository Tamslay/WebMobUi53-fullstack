<script setup>
import { ref } from 'vue';

const props = defineProps({
    initial: { type: Object, default: null },
});

const emit = defineEmits(['submit', 'cancel']);

const question = ref(props.initial?.question ?? '');
const title = ref(props.initial?.title ?? '');
const isDraft = ref(props.initial?.is_draft ?? true);
const allowMultiple = ref(props.initial?.allow_multiple_choices ?? false);
const resultsPublic = ref(props.initial?.results_public ?? false);
const duration = ref(props.initial?.duration ?? '');
const options = ref(
    props.initial?.options?.map(o => ({ label: o.label })) ?? [{ label: '' }, { label: '' }]
);

function addOption() {
    options.value.push({ label: '' });
}

function removeOption(index) {
    if (options.value.length > 2) {
        options.value.splice(index, 1);
    }
}

function submit() {
    emit('submit', {
        question: question.value,
        title: title.value,
        is_draft: isDraft.value,
        allow_multiple_choices: allowMultiple.value,
        results_public: resultsPublic.value,
        duration: duration.value ? parseInt(duration.value) : null,
        options: options.value,
    });
}
</script>

<template>
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium mb-1">Titre (optionnel)</label>
            <input v-model="title" type="text" class="w-full border rounded px-3 py-2" />
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Question *</label>
            <input v-model="question" type="text" class="w-full border rounded px-3 py-2" />
        </div>

        <div class="space-y-2">
            <label class="block text-sm font-medium">Options *</label>
            <div v-for="(option, index) in options" :key="index" class="flex gap-2">
                <input v-model="option.label" type="text" class="flex-1 border rounded px-3 py-2" :placeholder="`Option ${index + 1}`" />
                <button @click="removeOption(index)" type="button" class="text-red-500 px-2" :disabled="options.length <= 2">✕</button>
            </div>
            <button @click="addOption" type="button" class="text-blue-600 text-sm">+ Ajouter une option</button>
        </div>

        <div class="flex gap-4 flex-wrap">
            <label class="flex items-center gap-2">
                <input v-model="allowMultiple" type="checkbox" />
                Choix multiple
            </label>
            <label class="flex items-center gap-2">
                <input v-model="resultsPublic" type="checkbox" />
                Résultats publics
            </label>
            <label class="flex items-center gap-2">
                <input v-model="isDraft" type="checkbox" />
                Brouillon
            </label>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Durée en secondes (optionnel)</label>
            <input v-model="duration" type="number" min="1" class="w-full border rounded px-3 py-2" />
        </div>

        <div class="flex gap-2">
            <button @click="submit" type="button" class="px-4 py-2 bg-blue-600 text-white rounded">
                Sauvegarder
            </button>
            <button @click="emit('cancel')" type="button" class="px-4 py-2 bg-gray-200 rounded">
                Annuler
            </button>
        </div>
    </div>
</template>