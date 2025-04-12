document.addEventListener('DOMContentLoaded', function () {
    const cronometros = document.querySelectorAll('[data-endtime]');
    cronometros.forEach(function (el) {
        const countdownEl = el.querySelector('.wplp-countdown');
        if (!countdownEl) return;

        const endTime = new Date(el.dataset.endtime).getTime();
        const template = el.classList.contains('template-box') ? 'box' : 'simple';

        function updateCountdown() {
            const now = new Date().getTime();
            const distance = endTime - now;

            if (distance <= 0) {
                countdownEl.innerText = 'Encerrado!';
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            if (template === 'box') {
                countdownEl.innerHTML = `
                    <span><div>${days}</div><small>dias</small></span>
                    <span><div>${hours}</div><small>horas</small></span>
                    <span><div>${minutes}</div><small>minutos</small></span>
                    <span><div>${seconds}</div><small>segundos</small></span>
                `;
            } else {
                countdownEl.innerText = `${days}d ${hours}h ${minutes}m ${seconds}s`;
            }
        }

        updateCountdown();
        setInterval(updateCountdown, 1000);
    });
});
