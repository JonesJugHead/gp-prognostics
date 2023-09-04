const voteButton = document.querySelector(".voteButton");
const voteCountSpan = document.querySelector("#voteCount");
const voteContainer = document.querySelector(".voteContainer");
if (token == null) {
  voteButton.id = "voteButtonLogin";
  voteButton.innerHTML = "Se connecter";
  voteButton.addEventListener("click", () => {
    window.location.href = "/login";
  });
} else if (window.location.pathname == "/vote/") {
  voteButton.remove();

  // Add a dropdown to select the pilot to vote for
  // get pilots list from https://gp-prognostics.fr/api/votes/pilots/, only take props name and id (id will be a input hidden)
  // create a select with the list of pilots
  // add a button to submit the vote
  // on submit, send a POST request to https://gp-prognostics.fr/api/vote with the pilot id

  fetch("https://gp-prognostics.fr/api/votes/pilots/", {
    method: "GET",
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.error) console.log(data.error);
      const select = document.createElement("select");
      select.id = "pilotSelect";
      select.name = "pilotSelect";
      data.forEach((pilot) => {
        const option = document.createElement("option");
        option.value = pilot.id;
        option.textContent = pilot.name + " - " + pilot.team;
        select.appendChild(option);
      });
      const submitButton = document.createElement("button");
      submitButton.id = "voteButtonSubmit";
      submitButton.textContent = "Voter";
      submitButton.addEventListener("click", () => {
        fetch(
          "https://gp-prognostics.fr/api/vote/?voteType=pilot&targetId=" +
            select.value,
          {
            method: "POST",
            headers: {
              Authorization: token,
              "Content-Type": "application/json",
            },
          }
        )
          .then((response) => response.json())
          .then((data) => {
            if (data.error) console.log(data.error);
            else {
              if (data.success) {
                const fallback = document.getElementById("fallback");
                fallback.classList.add("success");
                fallback.textContent = "Vote enregistré !";
              } else {
                const fallback = document.getElementById("fallback");
                fallback.classList.add("error");
                fallback.textContent =
                  "Erreur lors de l'enregistrement du vote";
              }
            }
          });
      });

      voteContainer.appendChild(select);
      voteContainer.appendChild(submitButton);
    });
} else {
  voteButton.id = "voteButtonVote";
  voteButton.innerHTML = "Voter";
  voteButton.addEventListener("click", () => {
    window.location.href = "/vote";
  });
}

fetch("https://gp-prognostics.fr/api/voteCount?voteType=pilot").then(
  (response) => {
    response.json().then((data) => {
      voteCountSpan.innerHTML = data.count;
    });
  }
);
