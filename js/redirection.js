
document.addEventListener('DOMContentLoaded', () => {
    let countdown = parseInt(document.getElementById('countdown').textContent);
    const countdownElement = document.getElementById('countdown');
    const redirectUrl = countdownElement.dataset.redirect;

    const interval = setInterval(() => {
        countdown--;
        countdownElement.textContent = countdown;
        if (countdown <= 0) {
            clearInterval(interval);
            window.location.href = redirectUrl;
        }
    }, 1000);
});
