<template>
  <div>
    <div>
      <label class="input input-bordered flex items-center gap-2">
        <input
          type="text"
          class="grow"
          placeholder="Search"
          v-model="search"
          @change="onSearch"
        />
        <svg
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 16 16"
          fill="currentColor"
          class="h-4 w-4 opacity-70"
        >
          <path
            fill-rule="evenodd"
            d="M9.965 11.026a5 5 0 1 1 1.06-1.06l2.755 2.754a.75.75 0 1 1-1.06 1.06l-2.755-2.754ZM10.5 7a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Z"
            clip-rule="evenodd"
          />
        </svg>
      </label>
    </div>
    <div>
      <span class="loading loading-dots loading-lg" v-if="isLoading"></span>
    </div>
    <div>{{ alertMsg }}</div>
    <div>
      <div class="overflow-x-auto" v-if="records">
        <table class="table table-xs">
          <thead>
            <tr>
              <th></th>
              <th>r_number</th>
              <th>r_date</th>
              <th>doc_form_number</th>
              <th>Name</th>
              <th>หนังสือนำ</th>
              <th>เอกสารแนบ</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in records" :key="item.id">
              <th>
                {{ pagination.per_page * (current_page - 1) + (index + 1) }}

                <!-- {{ item.id }} -->
              </th>
              <td>{{ item.r_number }}</td>
              <td>{{ item.r_date }}</td>
              <td>{{ item.doc_form_number }}</td>
              <td>{{ item.name }}</td>
              <td>
                {{ item.file }}
                <PdfFile :file_url="item.file" />
              </td>
              <td>{{ item.file_ext }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- {{ records }} -->
    </div>
    <div>
      <!-- {{ pagination }} -->
    </div>
    <div class="join">
      <button
        class="join-item btn"
        :disabled="!pagination.prev_page"
        @click="current_page = pagination.prev_page"
      >
        «
      </button>
      <button class="join-item btn">
        หน้าที่ {{ current_page }} จากทั้งหมด {{ pagination.total_pages }} หน้า
      </button>
      <button
        class="join-item btn"
        :disabled="!pagination.next_page"
        @click="current_page = pagination.next_page"
      >
        »
      </button>
    </div>
  </div>
</template>

<script setup>
import axios from "axios";
import { ref, onMounted, watch } from "vue";
import PdfFile from "@/components/dashboard/PdfFile.vue";

const search = ref("");
const records = ref([]);
const pagination = ref({
  prev_page: "null",
  current_page: 1,
  total_pages: 0,
  next_page: "null",
});
const prev_page = ref("null");
const current_page = ref(1);
const next_page = ref("null");
const alertMsg = ref();
const isLoading = ref(false);
const BASE_URL = `http://127.0.0.1/docz_ext/backend/docz.php`;

onMounted(async () => {
  isLoading.value = true;
  await loadDatas();
  isLoading.value = false;
});

watch(current_page, async () => {
  loadDatas();
});

const loadDatas = async () => {
  isLoading.value = true;
  try {
    const response = await axios.get(
      `${BASE_URL}?search_text=${search.value}&page=${current_page.value}`
    );
    records.value = response.data.records;
    pagination.value = response.data.pagination;
    current_page.value = response.data.pagination.current_page;
    alertMsg.value = "";
  } catch (error) {
    records.value = [];
    pagination.value = {
      prev_page: "null",
      current_page: 1,
      total_pages: 0,
      next_page: "null",
    };
    current_page.value = 1;
    alertMsg.value = error.response.data.message;
    console.log("error", error);
  }
  isLoading.value = false;
};

const onSearch = async () => {
  current_page.value = 1;
  loadDatas();
};
</script>

<style></style>
