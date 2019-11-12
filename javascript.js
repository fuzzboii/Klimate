/* Funksjonen som åpner og lukker gardinmenyen, bruker height for at den skal gå over hele siden */
$gjort = false;

/* var test = document.getElementsByTagName("a").; */

function hamburgerMeny() {
  if ($gjort == false) {
    document.getElementById("navMeny").style.height = "100%";
    $gjort = true;
    document.getElementById("menytab1").tabIndex = "5";
    document.getElementById("menytab2").tabIndex = "6";
    document.getElementById("menytab3").tabIndex = "7";
    document.getElementById("menytab4").tabIndex = "8";
    document.getElementById("menytab5").tabIndex = "9";
  } else {
    document.getElementById("navMeny").style.height = "0%";
    $gjort = false;
    document.getElementById("menytab1").tabIndex = "-1";
    document.getElementById("menytab2").tabIndex = "-1";
    document.getElementById("menytab3").tabIndex = "-1";
    document.getElementById("menytab4").tabIndex = "-1";
    document.getElementById("menytab5").tabIndex = "-1";
  }
}
/* funksjon for å lukke hamburger-meny'en om man trykker utenfor dropdown'en */
function lukkHamburgerMeny() {
  if ($gjort == true) {
    document.getElementById("navMeny").style.height = "0%";
    $gjort = false;
    document.getElementById("menytab1").tabIndex = "-1";
    document.getElementById("menytab2").tabIndex = "-1";
    document.getElementById("menytab3").tabIndex = "-1";
    document.getElementById("menytab4").tabIndex = "-1";
    document.getElementById("menytab5").tabIndex = "-1";
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