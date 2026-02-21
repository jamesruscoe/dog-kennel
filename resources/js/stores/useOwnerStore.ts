import { defineStore } from 'pinia';
import { ref } from 'vue';

/**
 * UI state for the owners module.
 */
export const useOwnerStore = defineStore('owners', () => {
    const search = ref('');
    const page = ref(1);

    function setSearch(value: string) {
        search.value = value;
        page.value = 1;
    }

    function setPage(value: number) {
        page.value = value;
    }

    function reset() {
        search.value = '';
        page.value = 1;
    }

    return { search, page, setSearch, setPage, reset };
});
