import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useDogStore = defineStore('dogs', () => {
    const search = ref('');
    const filterBreed = ref('');
    const page = ref(1);

    function setSearch(value: string) {
        search.value = value;
        page.value = 1;
    }

    function setBreed(value: string) {
        filterBreed.value = value;
        page.value = 1;
    }

    function setPage(value: number) {
        page.value = value;
    }

    function reset() {
        search.value = '';
        filterBreed.value = '';
        page.value = 1;
    }

    return { search, filterBreed, page, setSearch, setBreed, setPage, reset };
});
