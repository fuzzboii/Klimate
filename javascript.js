/* Funksjonen som åpner og lukker gardinmenyen, bruker height for at den skal gå over hele siden */
/*-------------------------------------------------------------------------------------------------------*/
/*-------------------------------------------------------------------------------------------------------*/
var hamburgerMenyGjort = false;
var hentetBurger = document.getElementsByClassName("menytab");

function hamburgerMeny() {
  if (hamburgerMenyGjort == false) {
    var tabStart = 7;
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
window.onscroll = function() { skrollFunksjon() };

/* Når vinduet scroller, kjør denne */
function skrollFunksjon() {
  var knappen = document.getElementById("toppKnapp");
  if (document.body.scrollTop > 250 || document.documentElement.scrollTop > 250) {
    /* Hvis vinduet er mer enn 20 piksler fra toppen, vis tilbake til topp knappen */
    knappen.style.display = "block";
  } else {
    /* Hvis vinduet er mindre enn 20 piksler fra toppen, fjern tilbake til topp knappen */
    knappen.style.display = "none";
  }
}
/* Når bruker trykker på knappen for å gå til topp, kjør denne */
function tilbakeTilTopp() {
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

  // Henter 
  var bredde = window.innerWidth;

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

// For å sikre riktig visning når bruker endrer størrelsen på vinduet
function fiksRullegardin() {
  // Henter bredde 
  var bredde = window.innerWidth;

  if (bredde >= 720) {
    document.getElementById("konto_rediger_pw").style.display = "block";
  } else if (document.getElementById("kontoRullegardin").innerHTML == "Endre passord") {
    document.getElementById("konto_rediger_pw").style.display = "none";
  } else if (document.getElementById("kontoRullegardin").innerHTML == "Avbryt") {
    document.getElementById("konto_rediger_pw").style.display = "block";
  }
}

// Åpner en bekreftelses-boks for å avregistrere og slette arrangement / artikler
function bekreftMelding(element) {
  var knapp = document.getElementById(element);

  if (knapp.style.display == 'none') {
    knapp.style.display = 'block';
  } else {
    knapp.style.display = 'none';
  }
}

/*-------------------------------------------------------------------------------------------------------*/
/*-------------------------------------------------------------------------------------------------------*/


/* Denne blir kjørt når sok.php blir lastet inn, legger til en eventlistener */
/*---------------------------------------------------------------------------*/
/*---------------------------------------------------------------------------*/
function sokRullegardin() {
  // Funksjonen åpner og lukker rullegardinen for søk, lukker de andre søkemetodene 

  // Henter alle elementer som brukes
  var elementBr = document.getElementsByClassName("brukerRullegardin");
  var elementArt = document.getElementsByClassName("artikkelRullegardin");
  var elementArr = document.getElementsByClassName("arrangementRullegardin");
  var elementInp = document.getElementsByClassName("sokBrukerFelt");

  // Boolean for å vite om en av søkemetodene er åpnet
  var aapnet = false;
  
  var tabStart = 15;
  // FOR løkke som legger til tabIndex på valgene i menyen
  for (i = 0; i < elementInp.length; i++) {
    elementInp[i].tabIndex = tabStart;
    tabStart++;
  }

  // Går igjennom alle elementene fra tidligere, element.length er antall elementer med class navnet
  for (var i = 0; i < elementBr.length; i++) {
      // Legger på en eventlistener som ser etter et klikk på alle elementer med class navn brukerRullegardin
      elementBr[i].addEventListener("click", function() {
          // previousElementSibling returnerer det forrige elementet, altså innholdet i menyen
          var innholdRullegardin = this.previousElementSibling;
          if (aapnet == false) {
            // Når vinduet er åpnet, vis "Avbryt" 
            document.getElementById("brukerRullegardin").innerHTML = "Avbryt";

            // Bytt fargen på knappen til rød for å enkelt visuelt vise hvordan man kan avbryte
            document.getElementById("brukerRullegardin").style.backgroundColor = "darkred";
            document.getElementById("brukerRullegardin").style.color = "white";


            // Gjemmer de to andre søkemetodene så lenge en er åpen
            document.getElementById("artikkelRullegardin").style.display = "none";
            document.getElementById("arrangementRullegardin").style.display = "none";


            // Sett aapnet til true, søkemenyen er åpen
            aapnet = true;
          } else {
            // Når vinduet er lukket, vis "Søk etter bruker" 
            document.getElementById("brukerRullegardin").innerHTML = "Søk etter bruker";
            
            // Setter farge tilbake til farge før åpning
            document.getElementById("brukerRullegardin").style.backgroundColor = "rgb(211, 211, 211)";
            document.getElementById("brukerRullegardin").style.color = "initial";

            // Bruker har lukket valgt søkemetode, viser de to andre søkene igjen
            document.getElementById("artikkelRullegardin").style.display = "block";
            document.getElementById("arrangementRullegardin").style.display = "block";

            // FOR løkke som tømmer input feltene og setter tabIndex til -1 (Tabber ikke inn i lukket meny)
            for (i = 0; i < elementInp.length; i++) {
              if (!elementInp[i].value == "Søk på bruker") {
                elementInp[i].value = "";
              }
              elementInp[i].tabIndex = "-1";
            }
            
            // Sett aapnet til false, søkemenyen er lukket
            aapnet = false;
          }
        
          // Enkel test på om valgene skal vises
          if (innholdRullegardin.style.display == "block") {
              // Viser ikke valgene til søket
              innholdRullegardin.style.display = "none";
          } else {
              // Viser valgene til søket
              innholdRullegardin.style.display = "block";
          }
      });
  }
  // Går igjennom alle elementene fra tidligere, element.length er antall elementer med class navnet
  for (var i = 0; i < elementArt.length; i++) {
    // Legger på en eventlistener som ser etter et klikk på alle elementer med class navn artikkelRullegardin
    elementArt[i].addEventListener("click", function() {
        // previousElementSibling returnerer det forrige elementet, altså innholdet i menyen
        var innholdRullegardin = this.previousElementSibling;
        if (aapnet == false) {
          // Når vinduet er åpnet, vis "Avbryt" 
          document.getElementById("artikkelRullegardin").innerHTML = "Avbryt";

          // Bytt fargen på knappen til rød for å enkelt visuelt vise hvordan man kan avbryte
          document.getElementById("artikkelRullegardin").style.backgroundColor = "darkred";
          document.getElementById("artikkelRullegardin").style.color = "white";

          // Gjemmer de to andre søkemetodene så lenge en er åpen
          document.getElementById("brukerRullegardin").style.display = "none";
          document.getElementById("arrangementRullegardin").style.display = "none";


          // Sett aapnet til true, søkemenyen er åpen
          aapnet = true;
        } else {
          // Når vinduet er lukket, vis "Søk etter artikkel" 
          document.getElementById("artikkelRullegardin").innerHTML = "Søk etter artikkel";

          // Setter farge tilbake til farge før åpning
          document.getElementById("artikkelRullegardin").style.backgroundColor = "rgb(211, 211, 211)";
          document.getElementById("artikkelRullegardin").style.color = "initial";

          // Bruker har lukket valgt søkemetode, viser de to andre søkene igjen
          document.getElementById("brukerRullegardin").style.display = "block";
          document.getElementById("arrangementRullegardin").style.display = "block";

          // FOR løkke som tømmer input feltene og setter tabIndex til -1 (Tabber ikke inn i lukket meny)
          for (i = 0; i < elementInp.length; i++) {
            if (!elementInp[i].value == "Søk på artikkel") {
              elementInp[i].value = "";
            }
            elementInp[i].tabIndex = "-1";
          }
          
          // Sett aapnet til false, søkemenyen er lukket
          aapnet = false;
        }

        // Enkel test på om valgene skal vises
        if (innholdRullegardin.style.display == "block") {
            // Viser ikke valgene til søket
            innholdRullegardin.style.display = "none";
        } else {
            // Viser valgene til søket
            innholdRullegardin.style.display = "block";
        }
    });
  }
  // Går igjennom alle elementene fra tidligere, element.length er antall elementer med class navnet
  for (var i = 0; i < elementArr.length; i++) {
    // Legger på en eventlistener som ser etter et klikk på alle elementer med class navn arrangementRullegardin
    elementArr[i].addEventListener("click", function() {
        // previousElementSibling returnerer det forrige elementet, altså innholdet i menyen
        var innholdRullegardin = this.previousElementSibling;
        if (aapnet == false) {
          // Når vinduet er åpnet, vis "Avbryt" 
          document.getElementById("arrangementRullegardin").innerHTML = "Avbryt";
          
          // Bytt fargen på knappen til rød for å enkelt visuelt vise hvordan man kan avbryte
          document.getElementById("arrangementRullegardin").style.backgroundColor = "darkred";
          document.getElementById("arrangementRullegardin").style.color = "white";

          // Gjemmer de to andre søkemetodene så lenge en er åpen
          document.getElementById("brukerRullegardin").style.display = "none";
          document.getElementById("artikkelRullegardin").style.display = "none";



          // Sett aapnet til true, søkemenyen er åpen
          aapnet = true;
        } else {
          // Når vinduet er lukket, vis "Søk etter arrangement" 
          document.getElementById("arrangementRullegardin").innerHTML = "Søk etter arrangement";

          // Setter farge tilbake til farge før åpning
          document.getElementById("arrangementRullegardin").style.backgroundColor = "rgb(211, 211, 211)";
          document.getElementById("arrangementRullegardin").style.color = "initial";

          // Bruker har lukket valgt søkemetode, viser de to andre søkene igjen
          document.getElementById("brukerRullegardin").style.display = "block";
          document.getElementById("artikkelRullegardin").style.display = "block";

          // FOR løkke som tømmer input feltene og setter tabIndex til -1 (Tabber ikke inn i lukket meny)
          for (i = 0; i < elementInp.length; i++) {
            if (!elementInp[i].value == "Søk på arrangement") {
              elementInp[i].value = "";
            }
            elementInp[i].tabIndex = "-1";
          }

          // Sett aapnet til false, søkemenyen er lukket
          aapnet = false;
        }
        
        // Enkel test på om valgene skal vises
        if (innholdRullegardin.style.display == "block") {
            // Viser ikke valgene til søket
            innholdRullegardin.style.display = "none";
        } else {
            // Viser valgene til søket
            innholdRullegardin.style.display = "block";
        }
    });
  }
}

/* Viser innhold på flere sider */
var forelopigSide = 0; // Starter med å sette side vi er på

// Funksjonene tar imot navn på elementene så vi kan bruke samme kode til forskjellige sider
function hentSide(side, tilbake, neste) {
  // henter alle elementer med navn mottatt i variabel side
  var sideDel = document.getElementsByClassName(side);

  // Sjekker om element eksisterer
  if (typeof sideDel[forelopigSide] != 'undefined') {
    // Hvis ikke viser vi første side av søket
    sideDel[forelopigSide].style.display = "grid";

    if (sideDel.length > 1) {
      // Er det mer enn 1 side, vis neste knapp
      document.getElementById(neste).style.display = "inline-block";
    } 
    if (forelopigSide > 0) {
      // Er vi på side 2 eller høyere, vis tilbake knapp
      document.getElementById(tilbake).style.display = "inline-block";
    } else {
      // Hvis vi er på side 1, gjem tilbake knapp
      document.getElementById(tilbake).style.display = "none";
    }
    if (sideDel.length <= (forelopigSide + 1)) {
      // Om vi er på siste side, gjem neste knapp
      document.getElementById(neste).style.display = "none";
    }
  }
}

function visNesteSide(side, tilbake, neste) {
  // Hent alle elementer med navn side_sok
  var sideDel = document.getElementsByClassName(side);

  // Gjem denne siden, neste side hentes i hentSide()
  sideDel[forelopigSide].style.display = "none";
  
  // Øk teller med 1, dette er siden vi kommer til
  forelopigSide++;

  // Scroll til topp på ny side
  document.body.scrollTop = 0;
  document.documentElement.scrollTop = 0;

  // Hent den nye siden
  hentSide(side, tilbake, neste);
}

function visForrigeSide(side, tilbake, neste) {
  // Hent alle elementer med navn side_sok
  var sideDel = document.getElementsByClassName(side);
  
  // Gjem denne siden, neste side hentes i hentSide()
  sideDel[forelopigSide].style.display = "none";

  // Reduser teller med 1, dette er siden vi kommer til
  forelopigSide--;
  
  // Scroll til topp på ny side
  document.body.scrollTop = 0;
  document.documentElement.scrollTop = 0;
  
  // Hent den nye siden
  hentSide(side, tilbake, neste);
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


/* Ved påmeldt arrangement, vis avmeld ved mouseover */
/*-------------------------------------------------------------------------------------------------------*/
/*-------------------------------------------------------------------------------------------------------*/
function visAvmeld(valg) {
  var knapp = document.getElementById("arrangement_paameldt");
  if(valg == "Avmeld") {
    document.getElementById("arrangement_paameldt").innerHTML = "Avmeld";
  } else if(valg == "Paameld") {
    document.getElementById("arrangement_paameldt").innerHTML = "Påmeldt";
  }
}

/* Denne siden er utviklet av Robin Kleppang, sist endret 13.11.2019 */
/* Denne siden er kontrollert av Glenn Petter Pettersen, siste gang 11.10.2019 */