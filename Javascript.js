const body = document.querySelector("body"),
  sidebar = body.querySelector("nav"),
  toggle = body.querySelector(".toggle"),
  searchBtn = body.querySelector(".search-box"),
  modeSwitch = body.querySelector(".toggle-switch"),
  modeText = body.querySelector(".mode-text");
logoImage = document.querySelector(".sidebar header .image img");

toggle.addEventListener("click", () => {
  sidebar.classList.toggle("close");
  if (!sidebar.classList.contains("close")) {
    logoImage.src = "images/logo.png";
    logoImage.classList.add("open-logo");
    logoImage.classList.remove("close-logo");
  } else {
    logoImage.src = "images/K.png";
    logoImage.classList.add("close-logo");
    logoImage.classList.remove("open-logo");
  }
});

searchBtn.addEventListener("click", () => {
  sidebar.classList.remove("close");
});





