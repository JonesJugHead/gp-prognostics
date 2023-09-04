const podiumElement = document.getElementById("podium");

const createPoduimPilote = (data, isMobile = false) => {
  const personElement = document.createElement("div");
  personElement.classList.add("person");

  const avatarElement = document.createElement("img");
  avatarElement.src = "https://" + data.imageUrl;
  avatarElement.style = "object-fit: contain;";
  avatarElement.alt = "Avatar";
  personElement.appendChild(avatarElement);

  const nameElement = document.createElement("h3");
  nameElement.textContent = data.name;
  personElement.appendChild(nameElement);

  const positionElement = document.createElement("p");
  positionElement.textContent = `Votes: ${data.votes}`;
  personElement.appendChild(positionElement);

  if (!isMobile) {
    const marginTop = 50 + (data.rank - 1) * 50;
    personElement.style.marginTop = `${marginTop}px`;
  }

  podiumElement.appendChild(personElement);
};

const createPoduim = (data) => {
  createPoduimPilote(data[1]);
  createPoduimPilote(data[0]);
  createPoduimPilote(data[2]);
};

const createMobilePoduim = (data) => {
  createPoduimPilote(data[0], true);
  createPoduimPilote(data[1], true);
  createPoduimPilote(data[2], true);
};

const windowWidth = window.innerWidth;

let top3Data;

fetch("/api/votes/pilots/?top=3", { method: "GET" })
  .then((response) => response.json())
  .then((data) => {
    if (data.error) console.log(data.error);
    else {
      windowWidth < 768 ? createMobilePoduim(data) : createPoduim(data);
      top3Data = data;
    }
  });

// on resize
window.addEventListener("resize", () => {
  if (window.innerWidth < 768) {
    if (podiumElement.children.length > 0) {
      podiumElement.innerHTML = "";
      createMobilePoduim(top3Data);
    }
  } else {
    if (podiumElement.children.length > 0) {
      podiumElement.innerHTML = "";
      createPoduim(top3Data);
    }
  }
});

// Fonction pour créer une ligne de pilote
function createPiloteRow(pilote) {
  var row = document.createElement("div");
  row.classList.add("pilote-row");

  var logo = document.createElement("img");
  logo.classList.add("pilote-logo");
  logo.src = "https://" + pilote.imageUrl;

  var info = document.createElement("div");
  info.classList.add("pilote-info");

  var name = document.createElement("span");
  name.classList.add("pilote-name");
  name.textContent = pilote.name;

  var team = document.createElement("span");
  team.classList.add("pilote-team");
  team.textContent = pilote.team;

  var votes = document.createElement("span");
  votes.classList.add("pilote-votes");
  votes.textContent = pilote.votes + " votes";

  info.appendChild(name);
  info.appendChild(votes);
  // info.appendChild(team);

  row.appendChild(logo);
  row.appendChild(info);

  return row;
}

// Fonction pour générer la liste des pilotes
function generatePilotesList() {
  var ranking = document.getElementById("ranking");

  // Supprimer le contenu existant
  ranking.innerHTML = "";

  fetch("/api/votes/pilots/", {
    method: "GET",
  })
    .then((response) => {
      return response.json();
    })
    .then((data) => {
      if (data.error) {
        console.log(data.error);
      } else {
        data.forEach((person) => {
          var row = createPiloteRow(person);
          ranking.appendChild(row);
        });
      }
    });
}

// Générer la liste des pilotes
generatePilotesList();
