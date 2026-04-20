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

const initModelCarousels = () => {
    document.querySelectorAll('[data-carousel-root]').forEach((root) => {
        const track = root.querySelector('[data-carousel-track]');
        const prev = root.querySelector('[data-carousel-prev]');
        const next = root.querySelector('[data-carousel-next]');

        if (!track || !prev || !next) {
            return;
        }

        const getStep = () => {
            const item = track.querySelector('[data-carousel-item]');

            if (!item) {
                return 240;
            }

            const style = window.getComputedStyle(track);
            const gap = Number.parseFloat(style.columnGap || style.gap || '16') || 16;

            return item.getBoundingClientRect().width + gap;
        };

        const updateButtons = () => {
            const maxScroll = Math.max(0, track.scrollWidth - track.clientWidth - 4);
            prev.disabled = track.scrollLeft <= 4;
            next.disabled = track.scrollLeft >= maxScroll;
        };

        prev.addEventListener('click', () => {
            track.scrollBy({ left: -getStep(), behavior: 'smooth' });
        });

        next.addEventListener('click', () => {
            track.scrollBy({ left: getStep(), behavior: 'smooth' });
        });

        const activeItem = track.querySelector('[data-active-model]');

        if (activeItem) {
            const targetLeft = Math.max(0, activeItem.offsetLeft - (track.clientWidth - activeItem.clientWidth) / 2);
            track.scrollTo({ left: targetLeft, behavior: 'auto' });
        }

        track.addEventListener('scroll', updateButtons, { passive: true });
        window.addEventListener('resize', updateButtons);
        updateButtons();
    });
};

initModelCarousels();
