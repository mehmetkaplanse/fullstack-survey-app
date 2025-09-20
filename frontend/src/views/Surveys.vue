<script setup>
import { onMounted, ref } from 'vue';
import PageComponent from '../components/PageComponent.vue';
import { useRouter } from 'vue-router';
import api from '../services/api';
import { Icon } from '@iconify/vue';
import Modal from '../components/Modal.vue';

const router = useRouter();
const surveys = ref([]);
const loading = ref(false);
const selectedSurvey = ref(null);
const showDeleteModal = ref(false);

const fetchSurveys = async () => {
    try {
        loading.value = true;
        const response = await api.get('/api/survey');
        if (!response) {
            throw new Error('Failed to fetch surveys');
        }
        surveys.value = response.data.data;
    } catch (error) {
        console.error(error);
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    fetchSurveys();
});

const navigateToCreateSurvey = () => {
    router.push('/surveys/create');
}

const handleModalClose = () => {
    showDeleteModal.value = false
}

const handleDeleteSurvey = async (item) => {
  selectedSurvey.value = item;
  showDeleteModal.value = true;
}


const handleDelete = async () => {
  try {
    const res = await api.delete(`/api/survey/${selectedSurvey.value.id}`);
    if (res.status === 200) {
      fetchSurveys();
      showDeleteModal.value = false;
    }
  } catch (error) {
    console.error(error);
  } finally {
    handleModalClose();
  }
}


const getStatusBadge = (status) => {

}
</script>

<template>
    <PageComponent title="All Surveys">
        <template v-slot:header>
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold text-gray-900">Surveys</h1>
                <button
                    class="bg-indigo-500 text-white px-4 py-2 rounded-md cursor-pointer"
                    @click="navigateToCreateSurvey">
                    + Create Survey
                </button>
            </div>
        </template>

        <div v-if="loading" class="flex justify-center items-center py-10">
            <Icon icon="mingcute:loading-fill" class="animate-spin text-4xl text-blue-500" />
        </div>

        <div v-if="surveys.length && !loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="survey in surveys" class="bg-white rounded-lg shadow-md p-4 relative">
                <router-link :to="`/survey-detail/${survey.slug}`" class="absolute top-0 right-0 bg-indigo-500 text-white text-sm px-2 py-1 rounded-full flex items-center gap-1">
                  <Icon icon="mingcute:external-link-line" class="w-5 h-5" />
                </router-link>
                <img v-if="survey.image_url" :src="`${survey.image_url}`" :alt="survey.title" class="w-full h-48 bg-gray-200 object-cover rounded-lg mb-4"></img>
                <div v-else class="w-full h-48 bg-gray-200 rounded-lg mb-4 flex items-center justify-center gap-2 text-gray-500">
                    <Icon icon="heroicons:photo" class="w-8 h-8 text-gray-500" />
                    <span>No Image</span>
                </div>
                <div class="flex flex-col min-h-26">
                    <h2 class="text-lg font-bold text-gray-900">{{ survey.title }}</h2>
                    <p class="text-gray-500 line-clamp-2">{{ survey.description }}</p>
                </div>
                <div class="flex justify-end gap-2 pt-4">
                    <router-link :to="{ name: 'survey', params: {id: survey.id}}" class="w-8 h-8 bg-blue-500 flex items-center justify-center rounded-full cursor-pointer">
                        <Icon icon="mingcute:edit-fill" class="w-5 h- text-white rounded-full"/>
                    </router-link>
                    <div @click="handleDeleteSurvey(survey)" class="w-8 h-8 bg-red-500 flex items-center justify-center rounded-full cursor-pointer">
                        <Icon icon="mingcute:delete-fill" class="w-5 h-5 text-white rounded-full"/>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="!surveys.length && !loading" class="text-center text-gray-500">
            <p>You don't have any survey. You can create a new survey.</p>
        </div>
        <Modal
          :open="showDeleteModal"
          title="Anketi Silmek İstiyor musun?"
          :description="`'${selectedSurvey?.title}' başlıklı anket silinecek.`"
          @close="handleModalClose"
        >
          <p>Bu işlemi geri alamayacağınızdan emin misiniz?</p>


        <div class="mt-6 flex justify-end gap-2">
            <button
              @click="handleModalClose"
              class="px-6 py-2 cursor-pointer rounded-md bg-gray-200 text-gray-800 hover:bg-gray-300"
            >
              İptal
            </button>
            <button
              class="px-6 py-2 cursor-pointer rounded-md bg-blue-600 text-white hover:bg-blue-700"
              @click="handleDelete"
            >
              Evet
            </button>
        </div>
        </Modal>
    </PageComponent>

</template>

<style scoped>

</style>
