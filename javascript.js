/* Funksjonen som åpner og lukker gardinmenyen, bruker height for at den skal gå over hele siden */
$gjort = false;


function hamburgerMeny() {
  if ($gjort == false) {
    document.getElementById("navMeny").style.height = "100%";
    $gjort = true;
  } else {
    document.getElementById("navMeny").style.height = "0%";
    $gjort = false;
  }
}

function lukkHamburgerMeny() {
  if ($gjort == true) {
    document.getElementById("navMeny").style.height = "0%";
    $gjort = false;
  }
}


function scrollFunction() {
  if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
      mybutton.style.display = "block";
  } else {
      mybutton.style.display = "none";
  }
  }


  function topFunction() {
  document.body.scrollTop = 0;
  document.documentElement.scrollTop = 0;
  }


/* Denne siden er utviklet av Robin Kleppang, siste gang endret 23.10.2019 */
/* Denne siden er kontrollert av Glenn Petter Pettersen, siste gang 11.10.2019 */