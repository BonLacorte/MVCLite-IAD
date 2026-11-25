document.addEventListener('DOMContentLoaded', function() {
    const dropdowns = document.querySelectorAll('.dropdown');
    
    dropdowns.forEach(dropdown => {
        const trigger = dropdown.querySelector('.dropdown-trigger');
        const content = dropdown.querySelector('.dropdown-content');
        
        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            content.classList.toggle('hidden');
        });
        
        document.addEventListener('click', () => {
            content.classList.add('hidden');
        });
    });
});