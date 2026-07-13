<script>
    (() => {
        const strengthLabels = ['Very Weak', 'Weak', 'Fair', 'Strong', 'Very Strong'];

        const evaluateStrength = (value) => {
            let score = 0;
            if (value.length >= 8) score += 1;
            if (value.length >= 12) score += 1;
            if (/[A-Z]/.test(value)) score += 1;
            if (/[a-z]/.test(value)) score += 1;
            if (/[0-9]/.test(value)) score += 1;
            if (/[^A-Za-z0-9]/.test(value)) score += 1;

            const percent = Math.min(100, Math.round((score / 6) * 100));
            const labelIndex = Math.min(strengthLabels.length - 1, Math.floor(score / 2));
            return { percent, label: strengthLabels[labelIndex], score };
        };

        const GRADIENT_CLASSES = ['bg-gradient-to-r', 'from-orange-500', 'via-yellow-500', 'to-rose-500'];
        const FILL_COLOR_CLASSES = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-lime-600', 'bg-emerald-500'];
        const LABEL_COLOR_CLASSES = ['text-red-600', 'text-orange-500', 'text-yellow-500', 'text-lime-600', 'text-emerald-600'];

        const updateStrengthMeter = (meter) => {
            if (!meter) return;
            const fill = meter.querySelector('[data-password-strength-fill]');
            const label = meter.querySelector('[data-password-strength-label]');
            if (!fill || !label) return;

            const passwordInput = document.getElementById(meter.getAttribute('data-password-strength-for'));
            const confirmInput = meter.getAttribute('data-password-confirm-for')
                ? document.getElementById(meter.getAttribute('data-password-confirm-for'))
                : null;

            const password = passwordInput ? passwordInput.value : '';
            const confirm = confirmInput ? confirmInput.value : '';

            const { percent, label: strengthLabel, score } = evaluateStrength(password);

            fill.classList.remove(...GRADIENT_CLASSES, ...FILL_COLOR_CLASSES);
            label.classList.remove(...LABEL_COLOR_CLASSES);

            const confirmFilled = confirmInput && confirm.length > 0;
            const matches = confirmFilled && password === confirm;
            const mismatches = confirmFilled && password !== confirm;

            let displayLabel = strengthLabel;
            let width = percent;

            if (matches) {
                displayLabel = 'Match';
                width = 100;
                fill.classList.add('bg-emerald-500');
                label.classList.add('text-emerald-600');
            } else if (mismatches) {
                displayLabel = 'Mismatch';
                fill.classList.add('bg-red-500');
                label.classList.add('text-red-600');
            } else {
                fill.classList.add(...GRADIENT_CLASSES);
                label.classList.add(
                    score <= 1 ? 'text-red-600'
                        : score <= 3 ? 'text-orange-500'
                        : score <= 4 ? 'text-yellow-500'
                        : 'text-lime-600'
                );
            }

            fill.style.width = `${width}%`;
            label.textContent = displayLabel;
        };

        const initPasswordVisibilityToggles = () => {
            document.querySelectorAll('[data-toggle-password]').forEach((toggle) => {
                const targetId = toggle.getAttribute('data-toggle-password');
                if (!targetId) {
                    return;
                }

                const input = document.getElementById(targetId);
                if (!input || toggle.dataset.passwordToggleBound === '1') {
                    return;
                }

                toggle.dataset.passwordToggleBound = '1';

                toggle.addEventListener('click', () => {
                    const shouldShow = input.type === 'password';
                    input.type = shouldShow ? 'text' : 'password';
                    toggle.setAttribute('aria-label', shouldShow ? 'Hide password' : 'Show password');

                    const eye = toggle.querySelector('[data-password-eye]');
                    const eyeOff = toggle.querySelector('[data-password-eye-off]');
                    if (eye && eyeOff) {
                        eye.classList.toggle('hidden', shouldShow);
                        eyeOff.classList.toggle('hidden', !shouldShow);
                    }
                });
            });
        };

        const initPasswordStrengthMeters = () => {
            document.querySelectorAll('[data-password-strength-for]').forEach((meter) => {
                if (meter.dataset.passwordStrengthBound === '1') {
                    return;
                }
                meter.dataset.passwordStrengthBound = '1';

                const passwordInput = document.getElementById(meter.getAttribute('data-password-strength-for'));
                const confirmInput = meter.getAttribute('data-password-confirm-for')
                    ? document.getElementById(meter.getAttribute('data-password-confirm-for'))
                    : null;

                const onInput = () => updateStrengthMeter(meter);
                if (passwordInput) passwordInput.addEventListener('input', onInput);
                if (confirmInput) confirmInput.addEventListener('input', onInput);
                onInput();
            });
        };

        const init = () => {
            initPasswordVisibilityToggles();
            initPasswordStrengthMeters();
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }

        document.addEventListener('livewire:navigated', init);
    })();
</script>
