// Check for saved theme preference or default to 'dark'
const theme = localStorage.getItem('theme') || 'dark';
document.documentElement.classList.toggle('dark', theme === 'dark');

function toggleTheme() {
    const isDark = document.documentElement.classList.toggle('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
}