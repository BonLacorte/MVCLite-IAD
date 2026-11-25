function updatePST(pstElement) {
    if (!pstElement) return;

    const options = {
        timeZone: 'Asia/Manila',
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
    };

    const now = new Date();
    const pstTime = new Intl.DateTimeFormat('en-US', options).format(now);
    
    // Format: "Thursday, May 14, 2026 at 11:15:46 PM"
    // Intl.DateTimeFormat might produce something slightly different depending on browser, 
    // let's adjust it to match the requested style exactly if needed.
    // The current output of Intl is "Thursday, May 14, 2026, 11:15:46 PM"
    pstElement.textContent = pstTime.replace(',', ' at');
}

document.addEventListener('DOMContentLoaded', function() {
    const pstElement = document.getElementById('pst-time');
    if (!pstElement) return;

    // Initialize clock immediately, then update every second.
    updatePST(pstElement);
    setInterval(function() {
        updatePST(pstElement);
    }, 1000);
});
