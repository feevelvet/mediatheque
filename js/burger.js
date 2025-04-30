
const burger = document.querySelector('.burger');
const menu = document.querySelector('.menu');
const screenWidth = window.innerWidth;

function updateMenuDisplay() {
    const screenWidth = window.innerWidth;
    if (screenWidth < 768) {
        menu.style.display = 'none'; 
    } else {
        menu.style.display = 'flex'; 
    }
}
updateMenuDisplay();

window.addEventListener('resize', updateMenuDisplay);


burger.addEventListener('click', () => {
    
    if (menu.style.display === 'none') {
        menu.style.display = 'flex'; 
    } else {
        menu.style.display = 'none'; 
    }
});
