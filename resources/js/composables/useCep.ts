// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

import { ref } from 'vue';

export interface CepData {
    state_id: number | null;
    uf: string | null;
    state_name: string | null;
}

export function useCep() {
    const loadingCep = ref(false);
    const cepData = ref<CepData>({ state_id: null, uf: null, state_name: null });

    async function fetchCep(cep: string): Promise<CepData> {
        const digits = cep.replace(/\D/g, '');

        if (digits.length < 8) {
            cepData.value = { state_id: null, uf: null, state_name: null };

            return cepData.value;
        }

        loadingCep.value = true;

        try {
            const res = await fetch(`/api/cep/${digits}`);
            const data = (await res.json()) as CepData;

            cepData.value = data;

            return data;
        } catch {
            cepData.value = { state_id: null, uf: null, state_name: null };

            return cepData.value;
        } finally {
            loadingCep.value = false;
        }
    }

    return { loadingCep, cepData, fetchCep };
}