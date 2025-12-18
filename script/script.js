let slideIndex = 1;
let slideInterval;
/*При наведении на картинку слайдер останавливается 
а при уводе мышки возобновляется автоматическая прокрутка*/
document.addEventListener('DOMContentLoaded', function() {
  showSlides(slideIndex);
  startAutoSlide();
  const container = document.querySelector('.mySlides').parentElement;
  container.addEventListener('mouseenter', stopAutoSlide); 
  container.addEventListener('mouseleave', startAutoSlide);
});
// Функция автопрокрутки слайдера (3 секунды) 
function startAutoSlide() { 
  slideInterval = setInterval(function() {
      plusSlides(1);
  }, 3000);
}
// Функция остановки слайдера
function stopAutoSlide() {
  if (slideInterval) {
      clearInterval(slideInterval);
      slideInterval = null;
  }
}
// Смена слайда 
function plusSlides(n) {
  showSlides(slideIndex += n);
}
// Показывает конкретный слайд (точки снизу слайдера)
function currentSlide(n) {
  showSlides(slideIndex = n);
}
// Функция показа картинки и скрытие остальных
function showSlides(n) {
  let i;
  let slides = document.getElementsByClassName("mySlides");
  let dots = document.getElementsByClassName("dot");
  if (n > slides.length) {slideIndex = 1}
  if (n < 1) {slideIndex = slides.length}
  for (i = 0; i < slides.length; i++) {
      slides[i].style.display = "none";
  }
  for (i = 0; i < dots.length; i++) {
      dots[i].className = dots[i].className.replace(" active", "");
  }
  slides[slideIndex-1].style.display = "block";
  dots[slideIndex-1].className += " active";
}