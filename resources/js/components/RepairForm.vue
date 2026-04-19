<script setup>
import { computed, reactive, ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';

const props = defineProps({
    endpoint: {
        type: String,
        required: true,
    },
    sourcePage: {
        type: String,
        default: '/',
    },
    brand: {
        type: String,
        default: '',
    },
    models: {
        type: Array,
        default: () => [],
    },
    privacyUrl: {
        type: String,
        default: '/politika-za-poveritelnost',
    },
    initialSuccess: {
        type: Boolean,
        default: false,
    },
});

const submitted = ref(props.initialSuccess);
const loading = ref(false);
const errorMessage = ref('');
const errors = ref({});

const form = reactive({
    name: '',
    phone: '',
    email: '',
    city: '',
    model: '',
    issue: '',
    preferred_contact: 'phone',
    gdpr_consent: false,
});

const contactOptions = [
    { value: 'phone', label: 'Телефон' },
    { value: 'viber', label: 'Viber' },
    { value: 'whatsapp', label: 'WhatsApp' },
    { value: 'email', label: 'Имейл' },
];

const helperText = computed(() => `Shadcn-vue форма • Безплатна диагностика • ${props.brand}`);

async function submitForm() {
    loading.value = true;
    errorMessage.value = '';
    errors.value = {};

    try {
        await window.axios.post(props.endpoint, {
            ...form,
            source_page: props.sourcePage,
        });

        submitted.value = true;
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors ?? {};
            errorMessage.value = 'Проверете попълнените полета и опитайте отново.';
        } else {
            errorMessage.value = 'Възникна грешка при изпращането. Опитайте отново или се свържете с нас по телефон.';
        }
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div v-if="submitted" class="success-panel">
        <div class="mx-auto mb-4 inline-flex h-14 w-14 items-center justify-center rounded-full bg-secondary text-2xl">
            ✓
        </div>
        <h3 class="mb-2 text-xl font-semibold text-foreground">Заявката е приета</h3>
        <p class="text-sm leading-7 text-muted-foreground">
            Ще се свържем с вас в рамките на 1 час в работно време.
        </p>
    </div>

    <form v-else class="card-soft space-y-5" @submit.prevent="submitForm">
        <div class="flex flex-col gap-2">
            <Badge variant="secondary" class="w-fit">Shadcn-vue</Badge>
            <div>
                <h3 class="text-xl font-semibold text-foreground">Заявка за ремонт</h3>
                <p class="mt-2 text-sm leading-7 text-muted-foreground">
                    Формата записва заявката в backend системата и създава комуникационна история за телефона ви.
                </p>
            </div>
        </div>

        <div v-if="errorMessage" class="rounded-lg border border-destructive/20 bg-destructive/5 px-4 py-3 text-sm text-destructive">
            {{ errorMessage }}
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <label class="block text-sm font-medium text-foreground">
                Име *
                <Input v-model="form.name" class="mt-2" maxlength="100" placeholder="Вашето име" :aria-invalid="Boolean(errors.name?.[0])" />
                <span v-if="errors.name?.[0]" class="mt-1 block text-xs text-destructive">{{ errors.name[0] }}</span>
            </label>

            <label class="block text-sm font-medium text-foreground">
                Телефон *
                <Input v-model="form.phone" class="mt-2" type="tel" maxlength="20" placeholder="0878 369 024" :aria-invalid="Boolean(errors.phone?.[0])" />
                <span v-if="errors.phone?.[0]" class="mt-1 block text-xs text-destructive">{{ errors.phone[0] }}</span>
            </label>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <label class="block text-sm font-medium text-foreground">
                Имейл
                <Input v-model="form.email" class="mt-2" type="email" maxlength="120" placeholder="name@example.com" :aria-invalid="Boolean(errors.email?.[0])" />
                <span v-if="errors.email?.[0]" class="mt-1 block text-xs text-destructive">{{ errors.email[0] }}</span>
            </label>

            <label class="block text-sm font-medium text-foreground">
                Град
                <Input v-model="form.city" class="mt-2" maxlength="50" placeholder="Напр. София" :aria-invalid="Boolean(errors.city?.[0])" />
                <span v-if="errors.city?.[0]" class="mt-1 block text-xs text-destructive">{{ errors.city[0] }}</span>
            </label>
        </div>

        <label class="block text-sm font-medium text-foreground">
            Модел iPhone
            <select v-model="form.model" class="input-shell mt-2" :aria-invalid="Boolean(errors.model?.[0])">
                <option value="">Изберете модел</option>
                <option v-for="model in models" :key="model" :value="model">{{ model }}</option>
            </select>
            <span v-if="errors.model?.[0]" class="mt-1 block text-xs text-destructive">{{ errors.model[0] }}</span>
        </label>

        <label class="block text-sm font-medium text-foreground">
            Описание на проблема *
            <Textarea
                v-model="form.issue"
                class="mt-2 min-h-32 resize-none"
                rows="4"
                maxlength="1000"
                placeholder="Опишете проблема, кога се проявява и дали има следи от удар, вода или предишен ремонт."
                :aria-invalid="Boolean(errors.issue?.[0])"
            />
            <span v-if="errors.issue?.[0]" class="mt-1 block text-xs text-destructive">{{ errors.issue[0] }}</span>
        </label>

        <fieldset class="flex flex-col gap-3">
            <legend class="text-sm font-medium text-foreground">Предпочитан контакт</legend>
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="option in contactOptions"
                    :key="option.value"
                    type="button"
                    class="rounded-md border px-3 py-2 text-sm font-medium transition-colors"
                    :class="form.preferred_contact === option.value ? 'border-primary bg-primary text-primary-foreground' : 'border-border bg-background text-foreground hover:bg-secondary'"
                    @click="form.preferred_contact = option.value"
                >
                    {{ option.label }}
                </button>
            </div>
            <span v-if="errors.preferred_contact?.[0]" class="text-xs text-destructive">{{ errors.preferred_contact[0] }}</span>
        </fieldset>

        <label class="flex items-start gap-3 rounded-lg border bg-secondary/40 px-4 py-4 text-sm leading-6 text-muted-foreground">
            <Checkbox v-model="form.gdpr_consent" class="mt-1" :aria-invalid="Boolean(errors.gdpr_consent?.[0])" />
            <span>
                Съгласен/на съм данните ми да бъдат обработени за целите на заявката и комуникацията по ремонта според
                <a :href="privacyUrl" class="font-medium text-foreground underline underline-offset-4">политиката за поверителност</a>.
            </span>
        </label>
        <span v-if="errors.gdpr_consent?.[0]" class="block text-xs text-destructive">{{ errors.gdpr_consent[0] }}</span>

        <Button type="submit" class="w-full" :disabled="loading">
            {{ loading ? 'Изпращане...' : 'Изпрати заявка за ремонт' }}
        </Button>

        <p class="text-center text-xs leading-6 text-muted-foreground">{{ helperText }}</p>
    </form>
</template>
