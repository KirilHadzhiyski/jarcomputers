import './bootstrap';
import { createApp } from 'vue';
import RepairForm from './components/RepairForm.vue';

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

if (csrfToken) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
}

document.querySelectorAll('[data-vue-component]').forEach((element) => {
    const component = element.dataset.vueComponent;
    const rawProps = element.getAttribute('data-props') ?? '{}';
    const props = JSON.parse(rawProps);

    if (component === 'repair-form') {
        createApp(RepairForm, props).mount(element);
    }
});
