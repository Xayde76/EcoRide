class Carousel {
  constructor(selector, interval = 5000) {
    this.slidesContainer = document.querySelector(selector);
    this.totalSlides = this.slidesContainer?.children.length ?? 0;
    this.index = 0;

    if (this.totalSlides > 0) {
      setInterval(() => this.nextSlide(), interval);
    }
  }

  nextSlide() {
    this.index = (this.index + 1) % this.totalSlides;
    this.slidesContainer.style.transform = `translateX(-${this.index * 100}%)`;
  }
}

document.addEventListener("DOMContentLoaded", () => {
  new Carousel(".slides");
});
