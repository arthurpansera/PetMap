const modal = document.getElementById("modal-images-posts");
const modalImage = document.getElementById("modalImage");
const closeBtn = document.querySelector(".close-images-posts");
const prevBtn = document.getElementById("prevImage");
const nextBtn = document.getElementById("nextImage");

let imagesArray = [];
let currentIndex = 0;

document.querySelectorAll(".image-wrapper.more-images-posts").forEach(wrapper => {
    wrapper.addEventListener("click", () => {
        const dataImages = wrapper.getAttribute("data-images");
        if (!dataImages) return;

        imagesArray = JSON.parse(dataImages);
        currentIndex = 0;

        modalImage.src = "../images/uploads/posts/" + imagesArray[currentIndex];
        modal.style.display = "flex";
    });
});

prevBtn.addEventListener("click", () => {
    currentIndex = (currentIndex - 1 + imagesArray.length) % imagesArray.length;
    modalImage.src = "../images/uploads/posts/" + imagesArray[currentIndex];
});

nextBtn.addEventListener("click", () => {
    currentIndex = (currentIndex + 1) % imagesArray.length;
    modalImage.src = "../images/uploads/posts/" + imagesArray[currentIndex];
});

closeBtn.addEventListener("click", () => {
    modal.style.display = "none";
});

modal.addEventListener("click", e => {
    if (e.target === modal) {
        modal.style.display = "none";
    }
});