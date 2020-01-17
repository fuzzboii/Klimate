/* Funksjonen som åpner og lukker gardinmenyen, bruker height for at den skal gå over hele siden */
/*-------------------------------------------------------------------------------------------------------*/
/*-------------------------------------------------------------------------------------------------------*/
var hamburgerMenyGjort = false;
var hentetBurger = document.getElementsByClassName("menytab");

function hamburgerMeny() {
  if (hamburgerMenyGjort == false) {
    var tabStart = 5;
    /* Når hamburgermenyen er åpen får menyinnholdet tabIndex for å kunne gå igjennom dette uten mus */
    document.getElementById("navMeny").style.height = "100%";
    hamburgerMenyGjort = true;
    // Bruker for løkke for å gå igjennom elementene mottat 
    for (i = 0; i < hentetBurger.length; i++) {
      hentetBurger[i].tabIndex = tabStart;
      tabStart++;
    }
  } else {
    /* Når hamburgermenyen er lukket setter vi tabIndex for innholdet til -1 for å ikke tabbe inn i denne */
    document.getElementById("navMeny").style.height = "0%";
    hamburgerMenyGjort = false;
    // Bruker for løkke for å gå igjennom elementene mottat 
    for (i = 0; i < hentetBurger.length; i++) {
      hentetBurger[i].tabIndex = "-1";
    }
  }
}
/* funksjon for å lukke hamburger-meny'en om man trykker utenfor dropdown'en */
/* Når hamburgermenyen er lukket setter vi tabIndex for innholdet til -1 for å ikke tabbe inn i denne */
function lukkHamburgerMeny() {
  if (hamburgerMenyGjort == true) {
    document.getElementById("navMeny").style.height = "0%";
    hamburgerMenyGjort = false;
    // Bruker for løkke for å gå igjennom elementene mottat 
    for (i = 0; i < hentetBurger.length; i++) {
      hentetBurger[i].tabIndex = "-1";
    }
  }
}
/*-------------------------------------------------------------------------------------------------------*/
/*-------------------------------------------------------------------------------------------------------*/




/* Del for å vise "Tilbake til topp" knapp */
/*-------------------------------------------------------------------------------------------------------*/
/*-------------------------------------------------------------------------------------------------------*/
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
/*-------------------------------------------------------------------------------------------------------*/
/*-------------------------------------------------------------------------------------------------------*/




/* Denne blir kjørt når konto_rediger.php blir lastet inn, legger til en eventlistener */
/*-------------------------------------------------------------------------------------------------------*/
/*-------------------------------------------------------------------------------------------------------*/
function kontoRullegardin() {
  // Funksjonen åpner og lukker rullegardinen innenfor passord endring ved klikk
  // Henter alle elementer som har class kontoRullegardin og KontoredigeringFeltPW
  var element = document.getElementsByClassName("kontoRullegardin");
  var elementPW = document.getElementsByClassName("KontoredigeringFeltPW");

  // Boolean for å vite om "Endre passord" delen er åpen eller ikke
  var aapnet = false;

  // Går igjennom alle elementene fra tidligere, element.length er antall elementer med class navnet
  for (var i = 0; i < element.length; i++) {
      // Legger på en eventlistener som ser etter et klikk på alle elementer med class navn kontoRullegardin
      element[i].addEventListener("click", function() {
          // nextElementSibling returnerer det neste elementet som følger gjeldende element (this)
          var innholdRullegardin = this.nextElementSibling;
          if (aapnet == false) {
            // Når vinduet er åpnet, vis "Avbryt" 
            document.getElementById("kontoRullegardin").innerHTML = "Avbryt";
            aapnet = true;
          } else {
            // Når vinduet er lukket, vis "Endre passord" 
            document.getElementById("kontoRullegardin").innerHTML = "Endre passord";
            // FOR løkke som tømmer input feltene da bruker ikke lenger ønsker å oppdatere passord
            for (i = 0; i < elementPW.length; i++) {
              elementPW[i].value = "";
            }
            aapnet = false;
          }
          if (innholdRullegardin.style.display == "block") {
              // Med none vises ikke innholdet i rullegardinen (Gammelt pw, nytt pw og bekreft nytt pw)
              innholdRullegardin.style.display = "none";
          } else {
              // Nå vises innholdet
              innholdRullegardin.style.display = "block";
          }
      });
  }
}
/*-------------------------------------------------------------------------------------------------------*/
/*-------------------------------------------------------------------------------------------------------*/




/* Vis passord ved registrering og innlogging*/
/*-------------------------------------------------------------------------------------------------------*/
/*-------------------------------------------------------------------------------------------------------*/
function visPassordReg() {
  // Henter alle elementer med class navn RegInnFeltPW og kaller funksjonen visPassord
  var hentetReg = document.getElementsByClassName("RegInnFeltPW");
  visPassord(hentetReg);
}

/* Vis passord ved brukerinstillinger */
function visPassordInst() {
  // Henter alle elementer med class navn KontoredigeringFeltPW og kaller funksjonen visPassord
  var hentetInst = document.getElementsByClassName("KontoredigeringFeltPW");
  visPassord(hentetInst);
}

/* Funksjonalitet bak det å vise et passord */
function visPassord(hentet) {
  // Bruker for løkke for å gå igjennom elementene mottat 
  for (i = 0; i < hentet.length; i++) {
    if (hentet[i].type == "password") {
      // Hvis elementet er av type passord, sett dette til tekst (Bruker har valgt å vise passord)
      hentet[i].type = "text";
    } else {
      // Hvis elementet er av type text, sett dette til passord (Bruker har valgt å skjule passord)
      hentet[i].type = "password";
    }
  }
}
/*-------------------------------------------------------------------------------------------------------*/
/*-------------------------------------------------------------------------------------------------------*/


/* Denne siden er utviklet av Robin Kleppang, sist endret 13.11.2019 */
/* Denne siden er kontrollert av Glenn Petter Pettersen, siste gang 11.10.2019 */