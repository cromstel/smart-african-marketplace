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

        const updateStrengthMeter = (input, meter) => {
            if (!meter) return;
            const fill = meter.querySelector('[data-password-strength-fill]');
            const label = meter.querySelector('[data-password-strength-label]');
            if (!fill || !label) return;

            const { percent, label: strengthLabel, score } = evaluateStrength(input.value);
            fill.style.width = `${percent}%`;
            label.textContent = strengthLabel;

            label.classList.remove('text-red-600', 'text-orange-500', 'text-yellow-500', 'text-lime-600', 'text-emerald-600');
            const colorClass = score <= 1 ? 'text-red-600' : score <= 3 ? 'text-orange-500' : score <= 4 ? 'text-yellow-500' : score === 5 ? 'text-lime-600' : 'text-emerald-600';
            label.classList.add(colorClass);
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
                const targetId = meter.getAttribute('data-password-strength-for');
                if (!targetId) {
                    return;
                }

                const input = document.getElementById(targetId);
                if (!input || meter.dataset.passwordStrengthBound === '1') {
                    return;
                }

                meter.dataset.passwordStrengthBound = '1';

                const onInput = () => updateStrengthMeter(input, meter);
                input.addEventListener('input', onInput);
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

