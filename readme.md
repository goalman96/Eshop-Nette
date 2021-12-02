Základ aplikace pro eshop prodávající knihy.

## Základ aplikace

:mega:

Pro její spuštění:
1. stáhněte si soubor s exportem databáze ([eshop-db.sql](./eshop-db.sql)) a naimportujte jeho obsah do MariaDB
2. stáhněte si složku **[eshop](./eshop)** se základem projektu, nahrajte její obsah na server (a nezapomeňte na úpravu práv k adresářům *log* a *temp*)
3. otevřete si ukázkové projekty ve vývojovém prostředí
4. v souboru **config/local.neon** přístupy k databázi, později také přístupy k FB loginu
5. zaregistrujte si v aplikaci uživatelský účet a následně mu v databázi přiřaďte roli *admin*

:point_right:
- v základu projektu je hotové:
   - přihlašování uživatelů, kontrola práv
   - rozdělení aplikace na moduly
   - v administraci je hotový základ správy kategorií
- administraci najdete na adrese, do které doplníte */admin*, tj. například: https://eso.vse.cz/~xname/eshop/admin
- pro možnost jednoduchého smazání cache je v základu projektu skript *deleteCacheDir.php*, najdete ho např. na adrese https://eso.vse.cz/~xname/eshop/deleteCacheDir.php 
