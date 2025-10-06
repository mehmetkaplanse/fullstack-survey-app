<script setup>
import {onMounted, ref, computed, nextTick} from 'vue';
import PageComponent from '../components/PageComponent.vue';
import api from '../services/api';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../store/auth';
import { Icon } from '@iconify/vue';
import { toast } from 'vue3-toastify';
import SurveyStatus from '../constants/survey-status';

const surveyStatusOptions = [
  { label: 'Draft', value: SurveyStatus.Draft },
  { label: 'Published', value: SurveyStatus.Published },
  { label: 'Closed', value: SurveyStatus.Closed },
]

let survey_model = ref({
    title: "",
    status: "",
    description: "",
    image: null,
    image_url: "",
    questions: [],
    expire_date: "",
});

const auth = useAuthStore()
const {id} = useRoute().params;
const router = useRouter();
const user_id = auth.user.id;
const loading = ref(false);
const loadingSave = ref(false);
const loadingAi = ref(false);


onMounted( async () => {
  const fetchSurvey = async () => {
    try {
      loading.value = true;
      const response = await api.get(`/api/survey/${id}`);
      const data = response.data.data;

      const questions = data.questions ? data.questions.map(question => {
        let parsedData = { options: [] };
        try {
          // Eğer data string ise parse et
          if (typeof question.data === 'string') {
            parsedData = JSON.parse(question.data);
          } else if (question.data && typeof question.data === 'object') {
            parsedData = question.data;
          }
        } catch (error) {
          console.warn('Error parsing question data:', error);
          parsedData = { options: [] };
        }

        return {
          ...question,
          data: JSON.stringify(parsedData)
        };
      }) : [];

      survey_model.value = {
        ...data,
        image_url: data.image_url,
        questions: questions
      };

    } catch (error) {
      console.error('Error fetching survey:', error);
      toast.error('Survey yüklenirken hata oluştu');
    } finally {
      loading.value = false;
    }
  };
  if(id) {
    fetchSurvey();
  }
})

const addQuestion = () => {
  survey_model.value.questions.push({
    id: null,
    type: "text",
    question: "",
    description: "",
    data: JSON.stringify({ options: [] }),
  });
};

const addOption = (questionIndex) => {
  const question = survey_model.value.questions[questionIndex];
  const data = JSON.parse(question.data || '{"options": []}');
  data.options.push({
    uuid: crypto.randomUUID(),
    text: ""
  });
  question.data = JSON.stringify(data);
};

const removeOption = (questionIndex, optionIndex) => {
  const question = survey_model.value.questions[questionIndex];
  const data = JSON.parse(question.data || '{"options": []}');
  data.options.splice(optionIndex, 1);
  question.data = JSON.stringify(data);
};

const updateOptionText = (questionIndex, optionIndex, text) => {
  const question = survey_model.value.questions[questionIndex];
  const data = JSON.parse(question.data || '{"options": []}');
  data.options[optionIndex].text = text;
  question.data = JSON.stringify(data);
};

// Helper function to get question options
const getQuestionOptions = (question) => {
  try {
    return JSON.parse(question.data || '{"options": []}').options;
  } catch (error) {
    return [];
  }
};

const handleFileUpload = (e) => {
  const file = e.target.files[0];
  if (!file) return;
  survey_model.value.image_url = URL.createObjectURL(file);
  const reader = new FileReader();
  reader.onload = () => {
    survey_model.value.image = reader.result;
  };
  reader.readAsDataURL(file);
};

const removeImage = () => {
    survey_model.value.image = null;
    survey_model.value.image_url = null;
};

const saveSurvey = async () => {
    try {
        loadingSave.value = true;
        const payload = {
            title: survey_model.value.title,
            status: survey_model.value.status,
            description: survey_model.value.description,
            image: survey_model.value.image || survey_model.value.image_url || null,
            expire_date: survey_model.value.expire_date,
            user_id: user_id,
            questions: survey_model.value.questions
        };
        if(id) {
          const res = await api.put(`/api/survey/${id}`, payload);
        } else {
          const res = await api.post('/api/survey', payload);
          if(res.status === 'success') {
            router.push('/surveys');
          }
        }
        toast.success('Survey saved successfully');
    } catch (error) {
        console.error('Survey save failed:', error);
        toast.error('Survey save failed');
    } finally {
        loadingSave.value = false;
    }
};


const scrollBottom = async () => {
    await nextTick()
    const el = document.scrollingElement || document.documentElement
    window.scrollTo({ top: el.scrollHeight, behavior: 'smooth' })
}

const addQuestionWithAi = async () => {
    loadingAi.value = true;
    /*
    survey_model.value.questions.push({
        id: null,
        type: "text",
        question: "Yapay zekanın günlük hayatınızdaki en büyük etkisi nedir?",
        description: "",
        data: JSON.stringify({ options: [] }),
    });
    await api.post('/api/used-ai');
    */
    try {
      const res = await api.post('/api/generate-ai-question', { prompt: survey_model.value.title || 'No description' });
      const data = res.data || {};

      if (data.status === 'success' && data.question) {
          toast.success(data.message || 'AI question generated');
          survey_model.value.questions.push({
              id: null,
              type: "text",
              question: data.question,
              description: "",
              data: JSON.stringify({ options: [] }),
          });
          await scrollBottom();
          // Eğer backend'e kullanım bildirimi gönderiyorsanız:
          // await api.post('/api/used-ai');
      } else {
          toast.error(data.message || 'AI question generation failed');
      }

    } catch (error) {
      console.error('Error generating AI question:', error);
      toast.error(error.response?.data?.message || 'Error generating AI question');
    } finally {
      loadingAi.value = false;
    }


}
</script>

<template>
    <PageComponent title="Create Survey">
        <template v-slot:header>
            <h1 class="text-3xl font-bold text-gray-900">Create or View Survey</h1>
        </template>

        <div v-if="loading" class="flex justify-center items-center py-10">
            <Icon icon="mingcute:loading-fill" class="animate-spin text-4xl text-blue-500" />
        </div>

        <form v-if="!loading" @submit.prevent="saveSurvey">
            <div class="space-y-12">
              <div class="border-b border-gray-200 pb-12">
                <h2 class="text-base/7 font-semibold text-gray-900">Survey Information</h2>
                <p class="mt-1 text-sm/6 text-gray-600">Create a new survey with questions and settings.</p>
                <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                  <div class="sm:col-span-4">
                    <label for="title" class="block text-sm/6 font-medium text-gray-700">Title</label>
                    <div class="mt-2">
                      <input type="text" name="title" id="title" v-model="survey_model.title" class="block w-full rounded-md bg-gray-50 px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-500 sm:text-sm/6" placeholder="Enter survey title" />
                    </div>
                  </div>

                  <div class="sm:col-span-2">
                    <label for="status" class="block text-sm/6 font-medium text-gray-700">Status</label>
                    <div class="mt-2">
                     <select
                        id="status"
                        name="status"
                        v-model="survey_model.status"
                        class="block w-full appearance-none rounded-md bg-gray-50 py-1.5 pr-8 pl-3 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 *:bg-white focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-500 sm:text-sm/6"
                      >
                        <option value="">Select status</option>
                        <option
                          v-for="status in surveyStatusOptions"
                          :key="status.value"
                          :value="status.value"
                        >
                          {{ status.label }}
                        </option>
                      </select>

                    </div>
                  </div>

                  <div class="col-span-full">
                    <label for="description" class="block text-sm/6 font-medium text-gray-700">Description</label>
                    <div class="mt-2">
                      <textarea name="description" id="description" rows="3" v-model="survey_model.description" class="block w-full rounded-md bg-gray-50 px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-500 sm:text-sm/6" placeholder="Enter survey description" />
                    </div>
                    <p class="mt-3 text-sm/6 text-gray-600">Write a brief description of your survey.</p>
                  </div>

                  <div class="col-span-full">
                    <label for="image" class="block text-sm/6 font-medium text-gray-700">Survey Image</label>
                    <div class="mt-2">
                      <!-- Image Preview -->
                      <div v-if="survey_model.image_url" class="mb-4">
                        <img :src="survey_model.image_url" alt="Survey preview" class="w-40 h-40 object-contain rounded-lg border border-gray-300" />
                        <button
                          type="button"
                          @click="removeImage"
                          class="mt-2 text-sm text-red-600 hover:text-red-800"
                        >
                          Remove Image
                        </button>
                      </div>

                      <!-- Upload Area -->
                      <div v-if="!survey_model.image_url" class="flex justify-center rounded-lg border border-dashed border-gray-300 px-6 py-10">
                        <div class="text-center">
                          <div class="mt-4 flex text-sm/6 text-gray-600">
                            <label for="file-upload" class="relative cursor-pointer rounded-md bg-transparent font-semibold text-indigo-600 focus-within:outline-2 focus-within:outline-offset-2 focus-within:outline-indigo-500 hover:text-indigo-500">
                              <span>Upload a file</span>
                              <input id="file-upload" name="file-upload" type="file" @change="handleFileUpload" accept="image/*" class="sr-only" />
                            </label>
                            <p class="pl-1">or drag and drop</p>
                          </div>
                          <p class="text-xs/5 text-gray-500">PNG, JPG, GIF up to 10MB</p>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="sm:col-span-3">
                    <label for="expire_date" class="block text-sm/6 font-medium text-gray-700">Expire Date</label>
                    <div class="mt-2">
                      <input type="datetime-local" name="expire_date" id="expire_date" v-model="survey_model.expire_date" class="block w-full rounded-md bg-gray-50 px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-500 sm:text-sm/6" />
                    </div>
                  </div>
                </div>
              </div>

              <div class="border-b border-gray-200 pb-12">
                <div class="flex justify-between sm:items-center sm:flex-row flex-col gap-2">
                    <div>
                        <h2 class="text-base/7 font-semibold text-gray-900">Questions</h2>
                        <p class="mt-1 text-sm/6 text-gray-600">Add questions to your survey.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="addQuestionWithAi" class="text-sm/6 font-semibold text-white bg-gradient-to-r from-indigo-400 to-blue-600 border border-gray-300 rounded-md px-4 py-2 cursor-pointer flex items-center gap-1">
                            <Icon icon="ix:ai" class="w-5 h-5" :class="loadingAi ? 'animate-spin' : ''" />
                            {{loadingAi ? 'Generating...' : 'Add Question with AI' }}
                        </button>
                        <button type="button" @click="addQuestion" class="text-sm/6 font-semibold text-gray-900 border border-gray-300 rounded-md px-4 py-2 cursor-pointer">Add Question</button>
                    </div>
                </div>

                <div class="mt-10 space-y-6">
                  <!-- Questions List -->
                  <div v-if="survey_model.questions && survey_model.questions.length > 0" class="space-y-6">
                    <div v-for="(question, questionIndex) in survey_model.questions" :key="questionIndex" class="border rounded-lg p-6" :class="question.id == null ? 'border-2 border-indigo-500' : 'border-gray-200 '">
                      <div class="flex justify-between items-start mb-4">
                        <h3 class="text-lg font-semibold text-indigo-600">
                          {{ question.question ? question.question : 'Question ' + (questionIndex + 1) }}
                        </h3>
                        <button
                          type="button"
                          @click="survey_model.questions.splice(questionIndex, 1)"
                          class="text-red-600 hover:text-red-800 text-sm cursor-pointer"
                        >
                        Remove
                        </button>
                      </div>

                      <!-- Question Type -->
                      <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Question Type</label>
                        <select
                          v-model="question.type"
                          class="block w-full rounded-md bg-gray-50 px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-500 sm:text-sm/6"
                        >
                          <option value="text">Text</option>
                          <option value="textarea">Textarea</option>
                          <option value="select">Select</option>
                          <option value="radio">Radio</option>
                          <option value="checkbox">Checkbox</option>
                        </select>
                      </div>

                      <!-- Question Text -->
                      <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Question</label>
                        <input
                          type="text"
                          v-model="question.question"
                          class="block w-full rounded-md bg-gray-50 px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-500 sm:text-sm/6"
                          placeholder="Enter your question"
                        />
                      </div>

                      <!-- Question Description -->
                      <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
                        <textarea
                          v-model="question.description"
                          rows="2"
                          class="block w-full rounded-md bg-gray-50 px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-500 sm:text-sm/6"
                          placeholder="Enter question description"
                        />
                      </div>

                      <!-- Options for select, radio, checkbox -->
                      <div v-if="['select', 'radio', 'checkbox'].includes(question.type)" class="mb-4">
                        <div class="flex justify-between items-center mb-2">
                          <label class="block text-sm font-medium text-gray-700">Options</label>
                          <button
                            type="button"
                            @click="addOption(questionIndex)"
                            class="text-sm text-indigo-600 hover:text-indigo-800"
                          >
                            + Add Option
                          </button>
                        </div>

                        <div v-if="getQuestionOptions(question).length > 0" class="space-y-2">
                          <div
                            v-for="(option, optionIndex) in getQuestionOptions(question)"
                            :key="option.uuid"
                            class="flex items-center space-x-2"
                          >
                            <input
                              type="text"
                              :value="option.text"
                              @input="updateOptionText(questionIndex, optionIndex, $event.target.value)"
                              class="flex-1 rounded-md bg-gray-50 px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-500 sm:text-sm/6"
                              placeholder="Enter option text"
                            />
                            <button
                              type="button"
                              @click="removeOption(questionIndex, optionIndex)"
                              class="text-red-600 hover:text-red-800 text-sm cursor-pointer"
                            >
                            <Icon icon="material-symbols:delete" class="w-5 h-5" />
                            </button>
                          </div>
                        </div>

                        <div v-else class="text-sm text-gray-500">
                          No options added yet. Click "Add Option" to add choices.
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Empty State -->
                  <div v-else class="text-center text-gray-500 py-8">
                    <p>You don't have any questions yet. Add a question to your survey.</p>
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-x-6">
              <button @click="router.push('/surveys')" class="text-sm/6 font-semibold text-gray-900 cursor-pointer">Cancel</button>
              <button type="submit" class="rounded-md bg-indigo-500 px-3 py-2 text-sm font-semibold text-white cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus:outline-indigo-500">
                {{ loadingSave ? 'Saving...' : 'Save Survey' }}
              </button>
            </div>
        </form>
    </PageComponent>
</template>

<style scoped>

</style>
