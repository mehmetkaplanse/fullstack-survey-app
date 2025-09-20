<script setup>
import { useRoute, useRouter } from 'vue-router';
import PageComponent from '../components/PageComponent.vue';
import { onMounted, ref } from 'vue';
import api from '../services/api';
import { Icon } from '@iconify/vue';

const {slug} = useRoute().params;
const router = useRouter();
const survey = ref(null);
const loading = ref(false);
const answers = ref({});

const normalizeQuestions = (questions) => {
    return (questions || []).map((q) => {
        let parsed = {};
        try {
            parsed = typeof q.data === 'string' ? JSON.parse(q.data || '{}') : (q.data || {});
        } catch (e) {
            parsed = {};
        }
        const options = Array.isArray(parsed.options) ? parsed.options : [];
        return { ...q, options };
    });
};

const initAnswers = (questions) => {
    (questions || []).forEach((q) => {
        if (q.type === 'checkbox') {
            answers.value[q.id] = [];
        } else {
            answers.value[q.id] = '';
        }
    });
};

const fetchSurvey = async () => {
    try {
        loading.value = true;
        const response = await api.get(`/api/survey-by-slug/${slug}`);
        const data = response.data.data;
        const normalizedQuestions = normalizeQuestions(data.questions);
        survey.value = { ...data, questions: normalizedQuestions };
        initAnswers(normalizedQuestions);
    } catch (error) {
        console.error(error);
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    fetchSurvey();
})

</script>

<template>
    <PageComponent title="Survey Detail">
        <template v-slot:header>
            <div class="flex items-center gap-2">
                <button @click="router.back()" class="bg-gray-200 text-gray-900 px-2 py-1 rounded-lg flex items-center gap-1 cursor-pointer font-medium">
                    <Icon icon="mingcute:arrow-left-line" class="w-5 h-5" />
                    Back
                </button>
            </div>
        </template>

        <div v-if="loading" class="flex justify-center items-center py-10">
            <Icon icon="mingcute:loading-fill" class="animate-spin text-4xl text-blue-500" />
        </div>

        <div v-if="!loading && survey" class="flex flex-col gap-4">
            <h1 class="text-2xl font-bold text-gray-900">{{ survey.title }}</h1>
            <div>
                <img v-if="survey.image_url" :src="`${survey.image_url}`" :alt="survey.title" class="w-full h-48 bg-gray-200 object-cover rounded-lg mb-4 border border-gray-300"></img>
                <div v-else class="w-full h-48 bg-gray-200 rounded-lg mb-4 flex items-center justify-center gap-2 text-gray-500">
                    <Icon icon="heroicons:photo" class="w-8 h-8 text-gray-500" />
                    <span>No Image</span>
                </div>
            </div>
            <p class="text-gray-500">{{ survey.description }}</p>

            <div v-if="survey?.questions?.length > 0" class="space-y-6">
                <div v-for="question in survey.questions" :key="question.id" class="border border-gray-200 rounded-lg p-4">
                    <h2 class="text-lg font-bold text-gray-900 mb-1">{{ question.question }}</h2>
                    <p v-if="question.description" class="text-sm text-gray-500 mb-3">{{ question.description }}</p>

                    <!-- Text -->
                    <div v-if="question.type === 'text'">
                        <input
                            type="text"
                            v-model="answers[question.id]"
                            class="block w-full rounded-md bg-gray-50 px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-500 sm:text-sm/6"
                            :placeholder="question.placeholder || ''"
                        />
                    </div>

                    <!-- Textarea -->
                    <div v-else-if="question.type === 'textarea'">
                        <textarea
                            rows="3"
                            v-model="answers[question.id]"
                            class="block w-full rounded-md bg-gray-50 px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-500 sm:text-sm/6"
                            :placeholder="question.placeholder || ''"
                        />
                    </div>

                    <!-- Select -->
                    <div v-else-if="question.type === 'select'">
                        <select
                            v-model="answers[question.id]"
                            class="block w-full appearance-none rounded-md bg-gray-50 py-1.5 pr-8 pl-3 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 *:bg-white focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-500 sm:text-sm/6"
                        >
                            <option value="" disabled>Seçiniz</option>
                            <option
                                v-for="opt in question.options"
                                :key="opt.uuid"
                                :value="opt.uuid"
                            >
                                {{ opt.text }}
                            </option>
                        </select>
                    </div>

                    <!-- Radio -->
                    <div v-else-if="question.type === 'radio'" class="space-y-2">
                        <label
                            v-for="opt in question.options"
                            :key="opt.uuid"
                            class="flex items-center gap-2"
                        >
                            <input
                                type="radio"
                                :name="`q-${question.id}`"
                                :value="opt.uuid"
                                v-model="answers[question.id]"
                                class="text-indigo-600 focus:ring-indigo-500"
                            />
                            <span>{{ opt.text }}</span>
                        </label>
                    </div>

                    <!-- Checkbox -->
                    <div v-else-if="question.type === 'checkbox'" class="space-y-2">
                        <label
                            v-for="opt in question.options"
                            :key="opt.uuid"
                            class="flex items-center gap-2"
                        >
                            <input
                                type="checkbox"
                                :value="opt.uuid"
                                v-model="answers[question.id]"
                                class="text-indigo-600 focus:ring-indigo-500"
                            />
                            <span>{{ opt.text }}</span>
                        </label>
                    </div>
                </div>

                <button class="bg-indigo-500 text-white px-4 py-2 rounded-md cursor-pointer ms-auto flex items-center justify-end gap-1">
                    <Icon icon="mingcute:send-fill" class="w-4 h-4" />
                    Send
                </button>
            </div>
        </div>

    </PageComponent>
</template>

<style scoped>
</style>