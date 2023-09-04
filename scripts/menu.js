const menuToggle = document.querySelector(".menu-toggle");
const menu = document.querySelector(".menu");

menuToggle.addEventListener("click", () => {
  menu.classList.toggle("show-menu");
});

let token = localStorage.getItem("token");
let loginText = document.getElementById("userProfil");
let pilotVoteId = undefined;

if (token != null) {
  fetch("/api/me/", {
    method: "GET",
    headers: {
      Authorization: token,
    },
  })
    .then((response) => {
      return response.json();
    })
    .then((data) => {
      if (data.error) {
        loginText.innerHTML =
          "<a href='/login'><button class='button-connect'>Se connecter avec Discord</button></a>";
        localStorage.removeItem("token");
        token = null;
      } else {
        pilotVoteId = data.pilotVoteId;
        const div = document.createElement("div");
        div.classList.add("user-profile");
        const img = document.createElement("img");
        img.src = data.avatarUrl;
        img.alt = "Avatar";
        const span = document.createElement("span");
        span.textContent = data.username;
        const imgLogout = document.createElement("img");
        imgLogout.src = "https://gp-prognostics.fr/img/logout.svg";
        imgLogout.alt = "Se déconnecter";
        imgLogout.classList.add("logout-icon");
        imgLogout.title = "Se déconnecter";
        div.appendChild(img);
        div.appendChild(span);
        div.appendChild(imgLogout);
        loginText.appendChild(div);
        const logoutButton = document.querySelector(".logout-icon");
        logoutButton.addEventListener("click", () => {
          localStorage.removeItem("token");
          location.reload();
        });
      }
    });
} else {
  loginText.innerHTML =
    "<a href='/login'><button class='button-connect'>Se connecter avec Discord</button></a>";
}
