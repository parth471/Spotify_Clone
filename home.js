// Interactive play button effect
document.querySelectorAll(".play-btn").forEach(btn => {
    btn.addEventListener("click", (e) => {
        e.stopPropagation();
        alert("Playing music 🎵");
    });
});

// Search bar interaction
document.querySelector(".topbar input").addEventListener("focus", function() {
    this.style.background = "#222";
});