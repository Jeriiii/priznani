=====================
GOPAY-PHP-API verze 2.4
=====================
28.05.2013

Tento archív obsahuje modul pro implementaci platebního systému GoPay do e-shopu.

===== Struktura archívu ===== 

===== API =====

Složka "api" obsahuje třídy pro základní práci s platbami - vytváření, kontrola a činosti s nimi spojené.

-----------------------------
country_code.php 

Třída obsahující validní kódy zemí, které se používají při předávání údajů o zákazníkovi.

-----------------------------
gopay_config.php

Konfiguracni trida pro ziskavani URL pro praci s platbami na testovacím či provozním prostředí. 
Nastavení požadovaného prostředí (test / provoz) se provede nastavením parametru v souboru example/config.php

-----------------------------
gopay_helper.php

Pomocné funkce pro 
- sestavování řetězců pro podpis komunikačního elementu
- šifrování / dešifrování
- kontrolu vytvořené platby
- vytváření platebního formuláře / odkazu

-----------------------------
gopay_http.php

Pomocné funkce pro komunikaci pomocí HTTP/GET či HTTP/POST

-----------------------------
gopay_soap.php

Pomocné funkce pro komunikaci pomocí GoPayWS, pro vytváření a kontrolu platby

-----------------------------
payment_methods.php

Struktura pro načtení přehledu aktivních platebních metod ze serveru GoPay webovou službou


===== EXAMPLE =====

Složka "example" obsahuje ukázkovou implementaci GoPay do e-shopu

-----------------------------
order.php

Třída použitá pro uchování dat objednávky
Metody pro načítáni dat objednávky, jejich uložení a zpracování objednávky.
Upravte podle vašich potřeb a uložení objednávek v DB 

-----------------------------
config.php

Soubor s nastavením konfigurace GoPay modulu na e-shopu.
Je nutné nastavit následující parametry: 
- GoID e-shopu
- secret
- cesty k tomuto modulu - konstanta HTTP_SERVER
- nastavení prostředí (testovací / provozní) pomocí gopay_config.php


===== SOAP =====

-----------------------------
soap/payment.php

Skript ukazuje vytvoření platby a přesměrvání na platební bránu GoPay
- načtení relevantní objednávky
- založení platby na straně GoPay
- kontrola správného založení
- přesměrování na platební bránu (v případě úspěšného založení)
  
-----------------------------
soap/callback.php

Zpracování přesměrování z platební brány
- kontrola příchozích parametrů dle podpisu encryptedSignature
- kontrola stavu platby na serveru GoPay
- zobrazení výsledku zákazníkovy - zaplaceno, nezaplaceno (zrušená platba, apod.), čeká se na platbu

-----------------------------
soap/notify.php

Zpracování příchozího asynchronního oznámení o zaplacenosti platby
- kontrola příchozích parametrů dle podpisu encryptedSignature
- kontrola stavu platby na serveru GoPay
- zpracování objednávky v případě stavu "zaplaceno"
