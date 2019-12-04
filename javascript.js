/* Funksjonen som åpner og lukker gardinmenyen, bruker height for at den skal gå over hele siden */
$gjort = false;



function hamburgerMeny() {
  if ($gjort == false) {
    /* Når hamburgermenyen er åpen får menyinnholdet tabIndex for å kunne gå igjennom dette uten mus */
    document.getElementById("navMeny").style.height = "100%";
    $gjort = true;
    document.getElementById("menytab1").tabIndex = "5";
    document.getElementById("menytab2").tabIndex = "6";
    document.getElementById("menytab3").tabIndex = "7";
    document.getElementById("menytab4").tabIndex = "8";
    document.getElementById("menytab5").tabIndex = "9";
  } else {
    /* Når hamburgermenyen er lukket setter vi tabIndex for innholdet til -1 for å ikke tabbe inn i denne */
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
/* Når hamburgermenyen er lukket setter vi tabIndex for innholdet til -1 for å ikke tabbe inn i denne */
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

/* Del for å vise "Tilbake til topp" knapp */
window.onscroll = function() { scrollFunction() };

/* Når vinduet scroller, kjør denne */
function scrollFunction() {
  var knappen = document.getElementById("toppKnapp");
  /* Når bruker har scrollet 20px, vis tilbake til topp knapp til bruker */
  if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
    knappen.style.display = "block";
  } else {
    knappen.style.display = "none";
  }
}
/* Når bruker trykker på knappen for å gå til topp, kjør denne */
function topFunction() {
  document.body.scrollTop = 0;
  document.documentElement.scrollTop = 0;
}



/* Denne blir kjørt når konto_rediger.php blir lastet inn, legger til en eventlistener */
function kontoRullegardin() {
  /* Funksjonen åpner og lukker rullegardinen innenfor passord endring ved klikk */
  var element = document.getElementsByClassName("kontoRullegardin");
  var i;
  var aapnet = false;
  var elementer = [];
  elementer = document.getElementsByClassName("KontoredigeringFeltPW");

  for (i = 0; i < element.length; i++) {
      element[i].addEventListener("click", function() {
          this.classList.toggle("aktiv");
          var innholdRullegardin = this.nextElementSibling;
          if (aapnet == false) {
            document.getElementById("kontoRullegardin").innerHTML = "Avbryt";
            aapnet = true;
          } else {
            document.getElementById("kontoRullegardin").innerHTML = "Endre passord";
            for (i = 0; i < elementer.length; i++) {
              elementer[i].value = "";
            }
            aapnet = false;
          }
          if (innholdRullegardin.style.display == "block") {
              innholdRullegardin.style.display = "none";
          } else {
              innholdRullegardin.style.display = "block";
          }
      });
  }
}

/* Vis passord ved registrering og innlogging */
function visPassordReg() {
  var hentetReg = document.getElementsByClassName("RegInnFeltPW");

  for (i = 0; i < hentetReg.length; i++) {
    if (hentetReg[i].type == "password") {
      hentetReg[i].type = "text";
    } else {
      hentetReg[i].type = "password";
    }
  }
}

/* Vis passord ved brukerinstillinger */
function visPassordInst() {
  var hentetInst = document.getElementsByClassName("KontoredigeringFeltPW");

  for (i = 0; i < hentetInst.length; i++) {
    if (hentetInst[i].type == "password") {
      hentetInst[i].type = "text";
    } else {
      hentetInst[i].type = "password";
    }
  }
}



/* Denne siden er utviklet av Robin Kleppang, sist endret 13.11.2019 */
/* Denne siden er kontrollert av Glenn Petter Pettersen, siste gang 11.10.2019 */