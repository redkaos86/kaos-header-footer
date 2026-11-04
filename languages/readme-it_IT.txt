=== KaosH&F ===

Header e footer per Elementor: preset emozionali o layout disegnati in Elementor, con effetti sticky, vetro e reveal.

== Description ==

KaosH&F costruisce l'header e il footer del tuo sito in due modi: scegli un preset emozionale e personalizzalo, oppure disegna tu il layout in Elementor e lascia che il plugin aggiunga gli effetti di scroll.

**Header**

* Sei layout: classico, diviso (menu, logo al centro, menu), disposizione personalizzata, centrato, minimale (burger + menu a schermo intero) e una pillola di vetro fluttuante.
* Oltre 20 atmosfere che impostano sfondo, testo e accento con un clic, oppure i tuoi colori e gradienti a più colori.
* Effetti di scroll: sticky fisso o intelligente (si nasconde quando scorri verso il basso), trasparente sopra l'hero, che si rimpicciolisce, un logo diverso dopo lo scroll, colori che si adattano alla sezione sottostante, vetro liquido, una barra di avanzamento della lettura e ingressi e uscite animati.
* Più di una dozzina di effetti al passaggio per il menu, un mega menu, stili a tendina, separatori e 7 stili di icona burger.
* Un menu a schermo intero di cui scegli dimensione e posizione, con immagini o brevi video che cambiano al passaggio sulle voci, più contatti, social e un link legale.
* Allineamento del testo (sinistra, centro, destra) e immagine di sfondo per il menu a schermo intero.

**Footer**

* Layout: colonne, grande invito all'azione emozionale, minimale su una riga e una griglia personalizzata dove scegli righe e colonne e cosa contiene ogni cella (testo, menu, contatti, social, immagine, logo o pulsante).
* Nove forme di divisore superiore con altezza, colore e trasparenza regolabili.
* Una barra inferiore con logo, menu, social e copyright disposti in zone.
* Modalità di ingresso: normale, reveal (il contenuto scorre via e svela il footer) e reveal con parallasse, più effetti di entrata a zoom e dissolvenza.
* Una freccia "torna su" che puoi posizionare, ristilizzare o nascondere per ogni footer.
* Indirizzo cliccabile: dal telefono chiede se aprirlo con Mappe o con Google Maps.

**Ovunque**

* Mostra un template su tutto il sito, solo in alcune pagine, o ovunque tranne in alcune pagine. Spegnilo su una singola pagina dalla schermata di modifica.
* Scegli tra 24 caratteri, ospitati di default sul tuo sito (non viene caricato nulla da terze parti).
* Un collegamento "KaosH&F" nella barra di amministrazione porta direttamente all'header e al footer attivi nella pagina che stai guardando.
* Esporta e importa i template in JSON.
* Un pannello "Kaos Sticky" nella scheda Avanzate di sezioni e contenitori di Elementor, per aggiungere gli effetti di scroll a qualsiasi singolo elemento.
* Funziona con il tema che usi già: nasconde automaticamente header e footer del tema (Hello Elementor, Astra, GeneratePress, Kadence, OceanWP, Blocksy e temi a blocchi).
* Leggero: JavaScript puro senza dipendenze, caricato solo dove un effetto ne ha bisogno.
* L'interfaccia è in inglese con una traduzione italiana completa, e può essere tradotta in altre lingue.

== External services ==

Per impostazione predefinita il plugin non fa richieste a terze parti dai browser dei tuoi visitatori. I servizi qui sotto vengono usati solo come descritto:

* **Google Fonts** (fonts.googleapis.com, fonts.gstatic.com): con la modalità predefinita "Ospitati su questo sito", i file dei caratteri che scegli vengono scaricati una sola volta sul tuo server quando salvi un template, e i visitatori non contattano mai Google. Con la modalità "Google Fonts" il foglio di stile viene caricato direttamente da Google. Informativa sulla privacy: https://policies.google.com/privacy
* **Bunny Fonts** (fonts.bunny.net): solo se selezionato nelle impostazioni del plugin, e per mostrare l'anteprima dei caratteri nell'editor prima che vengano scaricati. Informativa sulla privacy: https://bunny.net/privacy
* **GitHub** (api.github.com e i suoi server di download): per controllare e scaricare gli aggiornamenti firmati del plugin, al massimo ogni poche ore. Non viene inviato nessun dato del sito. Informativa sulla privacy: https://docs.github.com/site-policy/privacy-policies/github-general-privacy-statement
* **Apple Maps e Google Maps** (maps.apple.com, www.google.com/maps): l'indirizzo in un footer o in un menu può essere un link. Non viene caricato nulla in anticipo: l'app di mappe si apre solo se il visitatore tocca l'indirizzo, e solo allora l'indirizzo e il dispositivo del visitatore vengono esposti al servizio scelto. Puoi disattivare il link per ogni template. Informative sulla privacy: https://www.apple.com/legal/privacy/ e https://policies.google.com/privacy

== Installation ==

1. Installa e attiva Elementor (versione gratuita).
2. Carica la cartella `kaos-header-footer` in `/wp-content/plugins/`, oppure installa lo zip da Plugin → Aggiungi nuovo.
3. Attiva il plugin.
4. Apri il menu KaosH&F, premi "Nuovo" e scegli un header o un footer.

== Frequently Asked Questions ==

= Mi serve Elementor? =

Sì, il plugin richiede che Elementor (versione gratuita) sia installato e attivo. Puoi creare un header o un footer dai preset emozionali senza toccare Elementor, oppure disegnare il layout in Elementor e usare il plugin per le regole di visualizzazione e gli effetti di scroll.

= L'header o il footer del tema è ancora visibile =

Il plugin nasconde header e footer dei temi più diffusi. Se il tuo non viene riconosciuto, apri KaosH&F → Impostazioni e inserisci il selettore CSS dell'header o del footer del tuo tema. Quando una pagina non mostra nessun header, il menu "KaosH&F" nella barra di amministrazione ti dice se il template è spento o escluso dalle sue regole di visualizzazione.

= Perché il footer non scorre via in alcune pagine? =

La modalità "reveal" deve conoscere il contenitore del contenuto della pagina. Se l'effetto non funziona con il tuo tema, imposta il selettore del contenitore in KaosH&F → Impostazioni.

= Funziona con la cache delle pagine? =

Sì. L'output dipende solo dalla pagina visualizzata, e i caratteri sono file statici sul tuo sito.

= Viene inviato qualcosa a terze parti? =

Vedi la sezione Servizi esterni qui sopra. Con le impostazioni predefinite, sul front end non viene caricato nulla da terze parti.

= Posso tradurlo? =

Sì. L'interfaccia è in inglese con una traduzione italiana completa, e le stringhe usano il text domain `kaos-header-footer`.

== Screenshots ==

1. La bacheca: anteprime dal vivo di ogni header e footer.
2. L'editor dell'header con l'anteprima dal vivo accanto alle impostazioni.
3. Il footer a griglia personalizzata, con celle di vetro e un divisore a nuvola.
4. Un header di vetro fluttuante sopra una sezione hero.
5. Il menu a schermo intero con immagini che cambiano al passaggio.

== Changelog ==

= 0.9.7 =
* La riga del copyright del footer ora accetta link (e grassetto, corsivo e a capo), così puoi collegare le pagine di privacy e cookie policy.

= 0.9.6 =
* Traduzione italiana: corrette "Header e Footer", "Crema e oro" e "intestazione".
* La finestra "Visualizza dettagli" dell'elenco dei plugin ora mostra descrizione, installazione, FAQ, screenshot e changelog, con il banner, e in italiano quando il sito è in italiano.

= 0.9.5 =
* Menu a schermo intero: l'allineamento del testo (sinistra, centro, destra) ora vale per ogni dimensione dello schermo, non solo per gli smartphone.
* Menu a schermo intero: l'indirizzo ora compare con i contatti e apre le mappe quando è cliccabile.

= 0.9.4 =
* Stili e script ora vengono aggiunti tramite le funzioni di enqueue di WordPress invece di essere stampati direttamente nella pagina.

= 0.9.3 =
* Celle del footer con stile vetro: il vetro smerigliato ora è disegnato su un livello a parte, così il passaggio sui link al suo interno non causa più bande scure o sfarfallio su alcuni browser e schede grafiche.

= 0.9.2 =
* L'anteprima dell'editor ora si aggiorna in modo affidabile quando cambi il layout o un'impostazione: la bozza viene inviata direttamente alla pagina di anteprima invece di essere salvata prima, cosa che alcuni hosting e cache potevano saltare.

= 0.9.1 =
* Il pulsante del menu non assume più l'aspetto dei pulsanti del tema: con alcuni temi poteva diventare blu al tocco o comparire su desktop.
* Il footer "reveal" non compare più ai lati del contenuto con i temi che hanno una colonna di contenuto stretta, come Hello Elementor.
* GeneratePress: il footer del tema ora viene nascosto quando è attivo un footer KaosH&F.
* Se il download dei caratteri fallisce, viene ritentato al massimo ogni 30 minuti invece che a ogni visualizzazione di pagina.

= 0.9.0 =
* I caratteri ora sono ospitati di default sul tuo sito, così i visitatori non contattano mai terze parti. Bunny Fonts e Google Fonts restano disponibili nelle impostazioni.
* L'interfaccia ora è in inglese con una traduzione italiana completa.
* Il footer ha effetti di entrata: zoom in, zoom out, zoom dall'alto, dal basso, da sinistra o da destra, e dissolvenza dal basso.
* Allineamento del testo del footer su smartphone, un indirizzo cliccabile che apre le mappe, e un grande titolo con carattere e allineamento propri.
* I gradienti sono disponibili per gli sfondi in tutto il plugin.
* Il menu a schermo intero mostra la sua immagine come sfondo su smartphone, con il testo allineato come scegli.
* Una nuova scelta "Intero sito" nelle regole di visualizzazione, e un messaggio nella barra di amministrazione che spiega perché un header o un footer non compare in una pagina.
