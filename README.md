# KaosH&F

Headers and footers for Elementor: emotional presets or layouts designed in Elementor, with sticky, glass and reveal effects.

Header e footer per Elementor: preset emozionali oppure layout disegnati con Elementor, con effetti di scroll (sticky, smart, trasparente, vetro liquido, isola fluttuante) e footer "reveal".

## Requisiti

- WordPress 6.8 o successivo
- PHP 7.4 o successivo
- Elementor (versione gratuita)

## Funzioni principali

- **Header**: sei layout (classico, diviso, personalizzato, centrato, minimale, fluttuante), oltre venti atmosfere, colori e gradienti, menu a schermo intero con immagini al passaggio, mega menu, effetti sul menu, icone burger.
- **Footer**: colonne, grande invito all'azione, minimale, griglia personalizzata con celle, divisori superiori, barra inferiore a zone, freccia "torna su", effetti di entrata (reveal, zoom, dissolvenza).
- **Regole**: tutto il sito, solo in alcune pagine, tutto il sito tranne alcune pagine.
- **Caratteri** ospitati sul tuo sito (nessuna richiesta a terzi dai visitatori), oppure Bunny Fonts o Google Fonts.
- Interfaccia in inglese con traduzione italiana completa.

## Sviluppo

I controlli automatici (GitHub Actions) girano a ogni push e ogni settimana:

- sintassi PHP su tutte le versioni supportate, da 7.4 all'ultima;
- sintassi JavaScript;
- traduzione italiana completa (ogni frase del codice ha la sua traduzione);
- standard di codice e sicurezza WordPress (PHPCS) e compatibilità PHP;
- prova del plugin su WordPress stabile e nightly;
- Plugin Check (le linee guida di WordPress.org), a titolo informativo.

Per eseguire PHPCS in locale:

```bash
composer install
composer lint
```

Lo zip di rilascio si crea con `git archive`, che esclude i file di sviluppo indicati in `.gitattributes`.
Lo zip per WordPress.org (senza traduzioni incluse) si crea con:

```bash
node bin/build-wporg.js
```

## Traduzioni

I testi del codice sono in inglese; la traduzione italiana è in `languages/`. Dopo aver aggiunto o cambiato dei testi:

```bash
wp i18n make-pot . languages/kaos-header-footer.pot --slug=kaos-header-footer --domain=kaos-header-footer --exclude=vendor,node_modules,tests,bin,dist,branding
wp i18n update-po languages/kaos-header-footer.pot languages/
# tradurre le nuove voci in languages/kaos-header-footer-it_IT.po, poi:
wp i18n make-mo languages
wp i18n make-json languages --no-purge
```

## Licenza

GPL-2.0-or-later.
