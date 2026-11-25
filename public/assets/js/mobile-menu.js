// document.addEventListener('DOMContentLoaded', function() {
//     const mobileMenu = document.getElementById('mobile-menu');
//     const toggleMobileMenu = () => {
//         mobileMenu.classList.toggle('hidden');
//     };

//     window.toggleMobileMenu = toggleMobileMenu;
// });

// document.addEventListener('DOMContentLoaded', function() {
//     const mobileMenu = document.getElementById('mobile-menu');
//     window.toggleMobileMenu = () => {
//         mobileMenu.classList.toggle('hidden');
//     };
// });


document.addEventListener('DOMContentLoaded', function() {
    const mobileMenu = document.getElementById('mobile-menu');
    window.toggleMobileMenu = () => {
        mobileMenu.classList.toggle('show');
    };
});