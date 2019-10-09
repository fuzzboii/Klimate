/* Funksjonen som åpner gardinmenyen, bruker height for at den skal gå over hele siden */
function aapneHamburger() {
  document.getElementById("navMeny").style.height = "100%";
}

/* Funksjonen som lukker gardinmenyen, bruker height for at innholdet ikke skal vises*/
function lukkHamburger() {
  document.getElementById("navMeny").style.height = "0%";
}