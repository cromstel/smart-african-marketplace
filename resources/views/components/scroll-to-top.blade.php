<div class="fixed right-5 bottom-36 z-[70]">
    <button
        id="scroll-to-top-btn"
        type="button"
        class="h-12 w-12 rounded-full bg-gray-900/95 text-white shadow-lg ring-1 ring-gray-700/40 backdrop-blur-sm flex items-center justify-center hover:bg-black transition-all duration-200 opacity-0 pointer-events-none translate-y-2"
        aria-label="Scroll to top"
        title="Scroll to top"
    >
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
        </svg>
    </button>
</div>

<script>
    (function () {
        const button = document.getElementById('scroll-to-top-btn');
        if (!button) return;

        const scrollContainers = Array.from(document.querySelectorAll('[class*="overflow-y-auto"], [class*="overflow-auto"]'));

        const getCurrentScroll = () => {
            const windowScroll = window.scrollY || document.documentElement.scrollTop || 0;
            const containerScroll = scrollContainers.reduce((max, el) => Math.max(max, el.scrollTop || 0), 0);
            return Math.max(windowScroll, containerScroll);
        };

        const toggleVisibility = () => {
            const shouldShow = getCurrentScroll() > 120;
            if (shouldShow) {
                button.classList.remove('opacity-0', 'pointer-events-none', 'translate-y-2');
                button.classList.add('opacity-100');
            } else {
                button.classList.add('opacity-0', 'pointer-events-none', 'translate-y-2');
                button.classList.remove('opacity-100');
            }
        };

        button.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
            scrollContainers.forEach((el) => {
                try {
                    el.scrollTo({ top: 0, behavior: 'smooth' });
                } catch (_) {
                    el.scrollTop = 0;
                }
            });
        });

        window.addEventListener('scroll', toggleVisibility, { passive: true });
        scrollContainers.forEach((el) => el.addEventListener('scroll', toggleVisibility, { passive: true }));
        toggleVisibility();
    })();
</script>

