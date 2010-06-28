<?
$content = '
<dl>
   <dt>Google Apps [2010-06-28]</dt>
   <dd>Utworzyłem globalny wzorzec domeny o&nbsp;nazwie "Google Apps template". Zawiera wszystkie potrzebne rekordy MX do poczty oraz SRV dla Jabbera. Łatwiej skasować nieużywane rekordy, niż tworzyć te wszystkie wpisy od zera &mdash; wiem, bo sam parę razy robiłem! :)</dd>
   <dt>Ataki [2010-06-26]</dt>
   <dd>Przepraszam za problemy z dostępem do interfejsu, jakiś idiota atakuje (DDoS) serwer. Myślimy, co z tym zrobić.</dd>
   <dt>Poprawki na prośbę [2010-06-22]</dt>
   <dd>Pozwoliłem na wildcard A, a także zmieniłem, żeby wasz adres email nie pojawiał się (po wygenerowaniu strefy) w SOA &mdash; z czego wynika, że już można używać kropek w adresach email (można to sobie wyłączyć w opcjach).
   </dd>
   <dt>Po trzech tygodniach [2010-06-15]</dt>
<!--   <dd>Krótkie podsumowanie: do tej pory zmigrowało ok. 42% domen, acz tylko 28% z tego ma zrobioną delegację na FreeDNS::42, stare dnsy (niedługo wyłączają!) ma ciągle jeszcze 36% ze zmigrowanych stref, zaś 36% w ogóle nie ma delegacji. < ! -- Z drugiej strony, z jeszcze niezmigrowanych aż 70% nie jest zarejestrowanych w starym systemie, więc może to po prostu stare domeny i w ten sposób zostaną naturalnie oczyszczone. - - ></dd> -->
   <dd>Krótki przegląd zmian<!-- (podziękowania dla poszczególnych osób będą w oddzielnej notce) -->: poprawiłem mnóstwo błędów w kodzie, automatyczne tworzenie rekordów AAAA ze strefy odwrotnej, robienie MX dla hosta, przetwarzanie logów, kod html i css, UTF-8, tłumaczenia, zakładanie stref, pokazywanie logów i stref na subkontach, pliterki w mailach, mechanizm zmiany adresu mail <!-- (konto już nie jest blokowane do potwierdzenia) -->, ułatwienie do tworzenia wielu rekordów w strefie odwrotnej, nowy typ przekierowania www, walidacja wielu pól, strefy odwrotne dla sieci mniejszych niż /24... <!-- i wiele innych poprawek jest w drodze. --> Piszcie, jeśli czegoś wam brakuje, póki jeszcze mam melodię na dłubanie w tym kodzie. :)</dd>
  <dd>Krótkie odpowiedzi na często zadawane pytania: dziękuję za oferty pomocy (także tej pieniężnej i materialnej)<!-- , na szczęście sponsorzy pomogli -->; serwis pozostanie darmowy<!-- (poza tym pobieranie opłat od osób prywatnych w Polsce to jakiś koszmar podatkowy) -->; nie, nie dogadałem się, zawartość stref powstała z backupu<!-- oraz przekonwertowana z drugiego serwera DNS, nad którym ciągle mam kontrolę -->; niestety, nie mam jak czytać (i z tego, co wiem, nikt nie czyta) poczty z adresu freedns na starym systemie... no ale przede wszystkim dziękuję za mnóstwo głosów poparcia!</dd>
   <dt>Nowy start [2010-05-23]</dt>
   <dd>Ruszamy! Logujecie się <strong>korzystając z loginu i hasła ze starego systemu</strong>, klikacie przycisk "Migruj", czekacie, aż zacznie działać, zmieniacie u rejestratorów domen wpisy dotyczące serwerów nazw, tj. na: fns1.42.pl&nbsp;(79.98.145.34) oraz fns2.42.pl&nbsp;(195.80.237.194), czekacie, aż się zmiany rozpropagują i&nbsp;wtedy możecie skasować strefy i&nbsp;konto ze starego systemu.</dd><dd>Powodzenia!</dd>
<dd>Czasu nie jest tak dużo: pod koniec czerwca stary system ma zostać wyłączony i jeśli nie przeniesiecie gdzieś (zapraszam tu, rzecz jasna) waszych stref, to przestaną działać.</dd>

   <dt>Polskie znaki w nazwach domen [2009-12-31]</dt>
   <dd>Polskie znaki w nazwach domen uzyskuje się przez wpisanie w konfiguracji nazwy IDN,
   nie bezpośrednio nazwy z polskimi literami. Przykładowo
   zamiast <code>żółtyżółw.pl</code> trzeba założyć 
   domenę <nobr><code>xn--tyw-fnac58bd27be.pl</code></nobr>.
   Przeglądarki automatycznie zamieniają nazwę z polskimi literami wpisaną w pasku adresu
   na postać IDN rozumianą przez DNS, więc dla użytkowników jest to (podobno) niezauważalne,
   tylko administrator DNS widzi, jak jest naprawdę. :)<p>
   Tłumacz IDN jest m.in. na stronie <a href="http://www.dns.pl/cgi-bin/idntranslator.pl">NASK</a>.
   </dd>

   <dt>Eksperyment: rekord WWW [2007-05-27]</dt>
   <dd>Dodałem eksperymentalnie nowy rekord: WWW. To nie jest rekord DNS, tylko
     skrót myślowy na ramkę lub przekierowanie WWW. Działa to w następujący
     sposób: dodajemy rekord WWW <tt>test</tt> w domenie <tt>nasza.pl</tt> z zawartością
     <tt><nobr>http://gdzies.serwer.z.naszym/~username/i/plikami/</nobr></tt>. Jeżeli zaznaczymy
     "przekierowanie", to po wejściu na <tt><nobr>http://test.nasza.pl</nobr></tt> zostaniemy przekierowani
     na stronę jw., a jeżeli "ramka", to zobaczymy od razu naszą stronę (ukrytą
     w ramce). Oczywiście trzeba chwilę odczekać, bo serwer dodaje po cichu
     nowy rekord A wskazujący na adres serwera WWW, który to obsługuje. :)
   </dd>
  <dd>Bardzo proszę o komentarze i zgłaszanie ewentualnych problemów.</dd>

  <dt>Dynamiczny DNS</dt>
  <dd>
        Do dynamicznego aktualizowania rekordów używany jest XML RPC.
        Przygotowałem <a href="freedns-dyndns.py">skrypt w Pythonie</a>,
        który w prosty sposób (<tt><nobr>freedns-dyndns.py --help</nobr></tt> lub zajrzyj do
        źródła) pozwoli Ci na zmianę Twojego IP. Po zmianie IP wystarczy uruchomić
        <tt><nobr>freedns-dyndns.py --newaddress <i>nowy.adres.ip</i></nobr></tt> i już!<br>

        <p style="font-size:80%; margin-left:40px; margin-right:40px;">należy poprawić
        domyślne wartości w źródle skryptu, żeby nie
        musieć podawać nazwy hosta, użytkownika i hasła w linii komend;<br>
        stare IP można podać jako \'*\', nie trzeba go mieć zapisanego;</small></p>

        Innym sposobem jest skorzystanie z jakiegokolwiek serwisu oferującego
        usługę dynamicznego DNS (np. <a href="http://www.dyndns.org">DynDNS</a>)
        i dodanie tutaj rekordu CNAME host.Twoja.domena wskazującego na nazwę
        w zewnętrznym serwisie.
  </dd>
  <dt>Idea</dt>
  <dd>
  Darmowy serwis utrzymywania DNS przeznaczony jest dla osób, które nie
  chcą tracić czasu ani pieniędzy u providerów (którzy w dodatku
  czasem nie są zbyt żwawi, jeśli chodzi o zmiany w DNS).</dd>
  <dd>
  Oferujemy Ci podstawowy <b>oraz</b> zapasowy serwer nazw, automatyczne aktualizacje
  co kwadrans, rekordy SRV, TXT i obsługę IPv6 (ustaw sobie w opcjach użytkownika),
  system przekierowań/ramek (pseudo rekord WWW).
  <br >
  Wszystkie strefy obsługiwane jako podstawowe lub zapasowe na
  głównym serwerze &mdash; fns1.42.pl (79.98.145.34) &mdash; są automatycznie
  replikowane na nasz drugi serwer, fns2.42.pl (195.80.237.194). 
  </dd>

  <dd>Jeżeli rejestrujący domenę (np. NASK) wymaga podania danych osobowych
   i/lub emaila administratora domeny, to podawajcie wasze własne. To wy będziecie
   zarządzać domeną, nie ja. Nie wpisujcie mnie jako administratora
   waszych domen.</dd>
  <dd>
  Wszystkie konfiguracje muszą zostać przeprowadzone przy użyciu
  tego interfejsu WWW.</dd>
  <dt>Skontaktuj się ze mną</dt>
  <dd>
  Jeśli masz jakieś pytania, napisz do mnie email na adres
  <a href="mailto:freedns na 42 kropka pl" class="linkcolor">
  freedns na 42 pl</a>. Czekaj cierpliwie &mdash; to darmowy serwis i na
  pytania odpowiadam w wolnym czasie.
  </dd>
   </dl>
';
$oldcontent = '
   <dt>Rekordy wildcard (*) [2008-09-12]</dt>
   <dd>Zamiast dodawać <code>* IN A 123.45.67.89</code>, skorzystaj z wieloznacznego
   rekordu CNAME:<p>
   <code>* IN CNAME rekord<br>rekord IN A 123.45.67.89</code>
   </dd>

   <dt>Komunikat [2010-04-30]</dt>
   <dd>
   SGH zdecydowała się wyłączyć FreeDNS i nie współpracować ze mną w zakresie przeniesienia
   użytkowników na nowy serwis. Wkrótce więcej informacji o krokach potrzebnych do
   przeniesienia się tutaj.
   </dd>
   <dt>Komunikat [2010-04-14]</dt>
   <dd>
Uprzejmie informuję, że z powodów ode mnie niezależnych (patrz
<a href="http://42.pl/freedns.html">42.pl/freedns.html</a>)
nie będę mógł się dalej opiekować FreeDNS::SGH.
</dd>
<dd>
W zamian utworzyłem FreeDNS::42, niedługo serdecznie zapraszam
do korzystania z tej wersji.
<!-- Wszystkie wasze dane zostaną przeniesione.<br>
Możecie zgadywać, który serwis będzie dalej rozwijany i ulepszany. -->
   </dd>
   <dt>Komunikat [2010-04-12]</dt>
   <dd>
Uprzejmie informujemy, że od 8 kwietnia 2010 r. serwis FreeDNS SGH
znajduje
się pod wyłączną opieką administratorów Centrum Informatycznego SGH. O
decyzjach Władz Uczelni związanych z dalszym funkcjonowaniem serwisu
FreeDNS, poinformujemy w terminie późniejszym.<br><br>
Centrum Informatyczne SGH
   </dd>
   <dt>Zmienione delegowanie podstref stref odwrotnych [2008-11-28]</dt>
   <dd>Dla stref odwrotnych IPv6, jako że zazwyczaj dostaje się bardzo duży
       blok IP, wprowadziłem możliwość delegowanie całych podsieci na inne serwery DNS
       (można, oczywiście, podać serwery FreeDNS).<p>
       Dla stref odwrotnych IPv4 zostaje po staremu &mdash; możliwość delegowania
       kawałków sieci (kilku IP) innym użytkownikom FreeDNS. Wyszedłem z założenia,
       że na 99,99% tacy użytkownicy nie mają do dyspozycji klas B, żeby rozpisywać
       dla nich normalne delegacje.
   </dd>
  <dd>Proszę o komentarze i zgłaszanie ewentualnych problemów.</dd>

  <dt>Aktualizacja oprogramowania [2006-10-20]</dt>
  <dd>
        Dziś zaktualizowałem oprogramowanie XName do najnowszej wersji. 
        Rzućcie okiem na swoje strefy... mam nadzieję, że nic się nie popsuło.
   </dd>
   <dt>Częstsze aktualizacje [2007-04-29]</dt>
   <dd>Ha, zapomniałem napisać, że od lutego serwery są aktualizowane co kwadrans,
       nie co pół godziny.
   </dd>
   <dt>Błąd oprogramowania [2007-04-22]</dt>
   <dd>Piotr Szeptyński z Marcinem Kopcem znaleźli błąd w oprogramowaniu XName
       pozwalający oglądać cudze logi strefy. Błąd oczywiście natychmiast 
       poprawiłem. Co zresztą i tak nie ma większego znaczenia, bo z różnych
       powodów logi nie są aktualizowane na bieżąco. :(
   </dd>
   <dt>Brak listów z powiadomieniem o zmianach w strefie [2006-10-31]</dt>
   <dd>Oczywiście po aktualizacji coś się popsuło: a mianowicie przestały
       przychodzić listy z powiadomieniem o zmianach w strefie. Już naprawione.
   </dd>
  <dt>Automatyczne aktualizacje [2005-02-28]</dt>
  <dd>
  Aktualizacja wpisów do DNS odbywa się co kwadrans o :01, :16, :31 i :46. 
  </dd>

  <dt>Nazwa z kropką [2005-01-22]</dt>
  <dd>
  Ulegając wielu prośbom użytkowników umożliwiłem tworzenie wpisów IN A
  zawierających jedną kropkę.
   </dd>

  <dt>CNAME na domenę [2003-12-11]</dt>
  <dd>
  Nie da się ustawić CNAME na domenę. Proszę nie pytać o taką możliwość.<br>
  "cokolwiek IN CNAME" &mdash; tak, "@ IN CNAME" &mdash; nie.<br>
  To nie jest mój wymysł, RFC 1034 zabrania rekordów, które mają coś oprócz 
  CNAME (a tak jest w przypadku rekordu domeny, który przecież musi mieć SOA i NS).
   </dd>

  <dt>Rekordy SRV [2006-10-20]</dt>
  <dd>
        Po aktualizacji doszła jedna opcja w ustawieniach użytkownika: "rekordy SRV".
        Należy ją sobie włączyć, jeśli chcecie zmieniać rekordy SRV. 
   </dd>
  <dt>Problemy z fns2 [2006-08-08]</dt>
  <dd>
    Mamy problem z fns2, a administrator pojechał na urlop. :(<br >
    Bardzo wszystkich przepraszam za kłopot.
  </dd>
        <dt>Dynamiczne aktualizowanie rekordów [2005-08-30]</dt>
        <dd>
        Dynamiczne aktualizowanie rekordów przy pomocy skryptów wykorzystujących
        XML RPC chwilowo nie działa. Gdy zniknie ten komunikat, to znaczy, że już
        działa. :-)
        </dd>
  <dt>Rekordy TXT [2005-07-25]</dt>
  <dd>
        Po aktualizacji doszła jedna opcja w ustawieniach użytkownika: "rekordy TXT".
        Należy ją sobie włączyć, jeśli chcecie zmieniać rekordy TXT. Dodatkowo można
  teraz robić rekordy TXT nie tylko na domenę główną.
        </dd>
  <dt>Aktualizacja oprogramowania [2005-07-24]</dt>
  <dd>
        Dziś zaktualizowałem oprogramowanie XName do najnowszej 
        wersji. Rzućcie okiem na swoje strefy... mam nadzieję, że nic się
  nie popsuło.
        </dd>
  <dt>pf.pl =&gt; epf.pl [2005-03-15]</dt>
  <dd>
        W związku ze zmianą domeny pf.pl na epf.pl i zaprzestaniem świadczenia
        usług pod starą nazwą poszedłem użytkownikom na rękę (mam nadzieję!)
        i zmieniłem hurtem wszystkie adresy mailowe z @pf.pl na @epf.pl.
        </dd>
  <dt>Problem z fns2.sgh.waw.pl [2004-10-07]</dt>
  <dd>
        Popsuł się serwer, na którym stoi fns2. Nie działał prawie przez cały
  dzień 7. października, został podmieniony tymczasowo na słabszą maszynę
  (ale nie powinno to zostać zauważone) i ma być naprawiony w weekend.
        </dd>
  <dt>FreeDNS::SGH na 42. miejscu [2004-09-15]</dt>
  <dd>
  W serwisie top100.pl FreeDNS::SGH zajmuje przyjemne 42. miejsce w rankingu
  serwisów DNS pod względem ilości obsługiwanych domen .pl<br>
  <strong>To wasza zasługa, użytkownicy &mdash; dziękuję.
   </strong> :-)</dd>
  <dd>
  42. miejsce jest przyjemne dlatego, że posiadam domenę 
  <a href="http://42.pl/">42.pl</a>
  i FreeDNS miał tam stać (a może jeszcze będzie!).<br>
  Przy okazji małe piętno dla top100 za podawanie błędnego adresu
  www do FreeDNS (do "freedns.sgh.waw.pl" dokleili na początku "www."
  (taka nazwa nie istnieje) i, co gorsza, odmawiają poprawienia tłumacząc
  się, że "taki mają skrypt" &mdash; lazy excuse of the day).
        </dd>
  <div class="boxheader"><em>strefa połączona z tą już istnieje i to
 nie Ty nią zarządzasz...</em>[2004-09-15]</dd>
  <dd>
  Poprawiłem w końcu to ograniczenie odnośnie zakładania stref, które
  są podstrefami już istniejących. A konkretnie dodałem warunek, że to
  ograniczenie działa tylko dla stref podstawowych (w tym wypadku
  właściciel może założyć podstrefę i oddać uprawnienia do niej komukolwiek).
  Dla stref, które w serwisie były tylko jako secondary (jak np. pl.eu.org),
  ograniczenie to uniemożliwiało skorzystanie z serwisu w ogóle.
  Za kłopot przepraszam.
        </dd>
  <dt>Naprawione listowanie stref [2004-09-15]</dt>
  <dd>
  Serwer DNS działał poprawnie, ale nie można było wylistować zawartości
  stref z innych serwerów w interfejsie WWW. Problem naprawiony.
        </dd>
  <dt>Chwilowa przerwa w działaniu [2004-09-01]</dt>
  <dd>
  Ze względu na upgrade sprzętu w dniu dzisiejszym interfejs WWW FreeDNS::SGH
  może być chwilami nieczynny (komunikat "problem z bazą danych" i brak 
  możliwości zalogowania się na swoje konto). Sam serwer DNS powinien
  działać bez przeszkód.
        </dd>
  <dt>Użytkownicy z adresami @konto.pl [2004-08-20]</dt>
  <dd>
        Administratorzy konto.pl postanowili odrzucać
        maile z domeny sgh.waw.pl, a więc także z serwisu FreeDNS::SGH. Sugeruję
        zapisywać się z innego adresu mail lub spróbować wyjaśnić sprawę
        u administratorów konto.pl.
        </dd>
  <dt>Problemy z bazą danych [2004-07-22]</dt>
  <dd>
  Złośliwość komputerów, spuścić je z oka na kilka dni i się psują. :-)
  Naprawione i mam nadzieję, że się nie powtórzy.
        </dd>
  <dt>Rekordy TXT [2004-06-10]</dt>
  <dd>
  Na prośbę użytkowników dodałem możliwość tworzenia rekordów TXT.
        </dd>
  <dt>Bezpieczne logowanie [2004-05-30]</dt>
  <dd>
  Na prośbę użytkowników logowanie do serwisu jest szyfrowane przez SSL.
  Niestety musiałem przez to zmienić adres IP interfejsu WWW &mdash; teraz jest to
  194.145.96.21 (taki sam, jak adres serwera fns1).
        </dd>
  <del><dt>Uwaga przy tworzeniu domen [2004-05-27]</dt>
  <dd>
  Uwaga: po utworzeniu strefy należy ją zmodyfikować! Choćbyście nie dodawali
  żadnego wpisu, trzeba wejść w zakładkę modyfikuj strefę i wybrać
  <b>Utwórz konfigurację strefy</b>. W przeciwnym wypadku strefa nie będzie
  widoczna jako działająca (przynajmniej dopóki nie naprawię tego błędu).
   </dd></del>
  <dt>Problemy z logami [2004-04-14]</dt>
  <dd>
  Poprawiłem problem braku świeżych logów oraz niepoprawnych dat
  w logach. Przy okazji skasowałem istniejące logi i załadowałem
  od nowa logi od początku roku &mdash; przejrzyjcie i pokasujcie.
   </dd>
  <dt>Długie logowanie [2004-02-13]</dt>
  <dd>
  Występujące od jakiegoś czasu problemy z długim logowaniem (i dość często
  dużymi utrudnieniami w zarządzaniu strefami) okazały się wynikiem
  błędu w skrypcie wrzucającym do bazy logi serwera DNS. Od 1 lutego nazbierało
  się ich całkiem niepotrzebnie ponad 3.5mln, co (w związku z różnymi
  procedurami wykonującymi się w trakcie logowania) kompletnie zatkało
  serwer. Już powinno być w porządku.
   </dd>
  <dt>Admin DNS [2004-01-20]</dt>
  <dd>
  Jeżeli rejestrujący domenę (np. NASK) wymaga podania danych osobowych
   i/lub emaila administratora domeny, to podawajcie wasze. To wy będziecie
   zarządzać domeną, nie ja. Nie wpisujcie mnie jako administratora
   waszych domen.
   </dd>
  <dt>Dynamiczny DNS [2003-12-12]</dt>
  <dd>
  Kilka słów wyjaśnienia: możliwość aktualizacji IP przez skrypt (poniżej)
  to nie jest prawdziwy dynamiczny DNS &mdash; ten system odświeża
  konfigurację co kwadrans. Jeśli Twoje IP zmienia się częściej, to nie
  jest serwis dla Ciebie (chyba że użyjesz CNAME na adres w "prawdziwym" DynDNS).
   </dd>
  <dt>Częściej [2003-12-05]</dt>
  <dd>
  Teraz strefy są odświeżane co pół godziny &mdash; taki Mikołajkowy prezent ;)
   </dd>
  <dt>Drobiazgi [2003-11-28]</dt>
  <dd>
        Poprawiono kilka drobiazgów. M.in. można używać CNAME z kropką,
        a także korzystać z @ (zamiast pełnej nazwy) do robienia wpisów
        dla samej strefy.<br>
        Dodatkowo zmieniłem interfejs w zakładce modyfikacji &mdash; mam nadzieję,
        że teraz jest czytelniej.
        </dd>

  <dt>Dwa serwery DNS</dt>
  <dd>
  Zrezygnowałem z obowiązku rejestrowania obu serwerów DNS,
  teraz obowiązkowy jest tylko fns1.42.pl. Oczywiście
  dalej można wpisywać fns2.42.pl. W dodatku
  fns2 będzie posiadał dokładną kopię fns1, a także będzie
  sam próbował ściągać zawartość stref, dla których jesteśmy
  zapasowym serwerem DNS.
  </dd>

  <dt>Upgrade</dt>
  <dd>
        Zaktualizowałem oprogramowanie FreeDNS. Przy okazji wkradło się
        kilka błędów, które musiałem ręcznie poprawić, co spowodowało
        konieczność wygenerowania wszystkich stref od nowa. Stąd te listy
        o tym, że przeładowano Twoje strefy.
        </dd>
';
$content_test_old = '
<p style="color:#ff0000;text-align:center;font-size:170%">PROSZĘ JESZCZE NIE UŻYWAĆ<br>JESZCZE NIE JEST GOTOWE<br>DAM WAM ZNAĆ</p>
   <dl>
<dt>Prawdopodobny start #2 [2010-05-20]</dt>
<dd>Tym razem już wszystko mam pod ręką i wygląda, że działa. Zatem najpóźniej w poniedziałek ruszamy.</dd>

<dt>Wszystko działa [2010-05-19]</dt>
<dd>Wszystko działa... a przynajmniej powinno. Możecie dodawać, kasować, zmieniać strefy, obserwujcie, czy fns1.42.pl i fns2.42.pl odpowiadają poprawnie. Nie zapominajcie, że to <strong>wszystko</strong> zostanie skasowane na rzecz importu ze starego serwisu.</dd>

<dt>Ciągle czekamy [2010-05-19]</dt>
<dd>W końcu dostałem wjazd na fns2, biorę się ostro za robotę, do końca tygodnia na pewno ruszy.</dd>

<dt>Ciągle czekamy [2010-05-16]</dt>
<dd>Ciągle czekam na uruchomienie przez sponsora drugiego serwera dns, mam nadzieję, że to już wkrótce.</dd>

<dt>Prawdopodobny start [2010-05-04]</dt>
<dd>Prawdopodobny start około 15 maja, czekamy na przyjście nowozakupionych serwerów, no i trzeba skonfigurować i przetestować.</dd>
<dd>
Zdecydowałem też, że zaimportuję wszystkich użytkowników (trzeba będzie potwierdzić adres email, bo jak nie, to kasacja w rozsądnym czasie) oraz wszystkie strefy i rekordy. Kiedy dokładnie ten import nastąpi, jeszcze nie wiem, ale ogłoszę z wyprzedzeniem. W każdym razie nie będziecie musieli (zbyt dużo) aktualizować w stosunku do starego serwisu<!--, gdyż ciągle mam dostęp do drugiego serwera DNS, gdzie są Wasze wszystkie rekordy-->.
</dd>
<dt>Ciągle w wersji testowej [2010-05-03]</dt>
<dd>
Można zakładać sobie użytkowników i strefy, <del>ale żaden serwer DNS jeszcze tego nie obsługuje</del>. Ponadto po zakończeniu testów, przed zaimportowaniem backupu z poprzedniego serwisu, wszystkie dotychczasowe, tymczasowe wpisy z bazy danych zostaną skasowane.
</dd>
<dd>Oczywiście jeśli znajdziecie jakieś problemy, to proszę pisać. :-)</dd>
<!-- Poprzedni fns2 (193.111.27.194) zostanie przekonfigurowany, żeby ściągał
     strefy z nowych serwerów; w ten sposób nawet jeśli ktoś będzie miał problem
     z przekonfigurowaniem, to będzie mu działać. -->

</dl>
';

?>
