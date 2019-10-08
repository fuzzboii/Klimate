/* Funksjonen som åpner gardinmenyen, bruker height for at den skal gå over hele siden */
function aapneHamburger() {
  document.getElementById("navMeny").style.height = "100%";
}

/* Funksjonen som lukker gardinmenyen, bruker height for at den ikke skal vises i det hele tatt*/
function lukkHamburger() {
  document.getElementById("navMeny").style.height = "0%";
}