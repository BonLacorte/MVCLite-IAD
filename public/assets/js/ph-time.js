function updatePhTime(phTimeElement) {
    if (!phTimeElement) return;

    const options = {
        timeZone: 'Asia/Manila',
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: 'numeric',
        minute: 'numeric',
        second: 'numeric',
        hour12: true
    };
    const phTime = new Date().toLocaleString('en-US', options);
    phTimeElement.textContent = phTime;
}

// Update time immediately and then every second
document.addEventListener('DOMContentLoaded', function() {
    const phTimeElement = document.getElementById('ph-time');
    if (!phTimeElement) return;

    updatePhTime(phTimeElement);
    setInterval(function() {
        updatePhTime(phTimeElement);
    }, 1000);
});
