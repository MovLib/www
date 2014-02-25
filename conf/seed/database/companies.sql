-- ---------------------------------------------------------------------------------------------------------------------
-- This file is part of {@link https://github.com/MovLib MovLib}.
--
-- Copyright © 2013-present {@link https://movlib.org/ MovLib}.
--
-- MovLib is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public
-- License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
-- version.
--
-- MovLib is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
-- of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
--
-- You should have received a copy of the GNU Affero General Public License along with MovLib.
-- If not, see {@link http://www.gnu.org/licenses/ gnu.org/licenses}.
-- ---------------------------------------------------------------------------------------------------------------------

-- ---------------------------------------------------------------------------------------------------------------------
-- Movie seed data.
--
-- @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
-- @copyright © 2013 MovLib
-- @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
-- @link https://movlib.org/
-- @since 0.0.1-dev
-- ---------------------------------------------------------------------------------------------------------------------

TRUNCATE TABLE `companies`;

-- START "Synapse films"

INSERT INTO `places` SET
  `place_id`     = 97981472,
  `country_code` = 'US',
  `dyn_names`    = COLUMN_CREATE(
    'en', 'Novi, Michigan',
    'de', 'Novi (Michigan)'
  ),
  `latitude`     = -83.4754913,
  `longitude`    = 42.48059
;

INSERT INTO `companies` SET
  `created`                = CURRENT_TIMESTAMP,
  `dyn_descriptions`       = COLUMN_CREATE(
    'en', '&lt;p&gt;Synapse Films is a DVD/Blu-ray label owned and operated by Don May, Jr. and his business partners Jerry Chandler and Charles Fiedler. The company was started in 1997 and it specializes in cult horror, science fiction, and exploitation films. May graduated from Illinois State University in 199. He always had an interest in television and film. “I caught the laserdisc bug while working at a local laserdisc store while I was in college. I was selling laserdisc players and buying product and I pretty much spent every extra dollar I had on laserdiscs. I loved movies and the disc format and knew this was a business I wanted to be in.” May became a part owner of Elite Entertainment. This was after he quit his job at laserdisc retailer.&lt;/p&gt;&lt;p&gt;Synapse&#039;s focus has been to provide quality restoration and video transfers to genre films usually neglected or granted shoddy release on home video. May has brought to the company an expertise in video mastering and film restoration gleaned from nearly a decade of experience in the LaserDisc industry, and personal enthusiasm for exploitation film of all stripe.&lt;/p&gt;&lt;p&gt;The Synapse catalog ranges from European horror touchstones like Vampyros Lesbos, and Castle of Blood, to important genre documentaries including Roy Frumkes&#039; Document of the Dead, from drive-in favorites like The Brain That Wouldn&#039;t Die to Leni Riefenstahl’s Nazi film Triumph of the Will.&lt;/p&gt;&lt;p&gt;In 2004, Synapse released a definitive edition of the controversial Thriller – A Cruel Picture, a DVD which was not without controversy itself.&lt;/p&gt;&lt;p&gt;Recently, Detroit film scholar Nicholas Schlegel released his documentary The Synapse Story in its entirety on YouTube. The documentary details the history and vision of the label and its founders.&lt;/p&gt;',
    'de', '&lt;p&gt;Synapse films ist ein US-amerikanisches DVD-Label welches von Don May, Jr. gegründet und betrieben wird. Mitgründer und Partner sind Jerry Chandler und Charles Fiedler. Gegründet wurde das Unternehmen 1997 mit dem Ziel Horrorfilme und Science Fiction Filme in perfekter digitaler Qualität zu präsentieren.&lt;/p&gt;&lt;p&gt;Der Hauptfokus liegt also in der Restaurierung und dem Video Transfer von Genre Filmen die lediglich eine sehr schlechte, oder gar keine, Video-Auswertung in der Vergangenheit erhielten. Don May, Jr. hatte in der Vergangenheit bei seinem vorigen Unternehmen Elite Entertainment – er war nur Teilhaber − schon sehr viel Erfahrung im Bereich der Restauration von Filmen gesammelt, Laserdisc Veröffentlichungen von Elite Entertainment galten damals als die Besten im Horrorbereich. Diese Erfahrung kommt ihm und seinem Unternehmen natürlich heute zugute.&lt;/p&gt;&lt;p&gt;Der Katalog von Synapse films reicht von Euro-Horror Filmen über Dokumentationen bis hin zu japanischen Exploitation Filmen aus den 1960er und 1970er.&lt;/p&gt;&lt;p&gt;2004 veröffentlichte Synapse films den kontroversen Thriller – ein unbarmherziger Film auf DVD. Die Veröffentlichung des Films war jedoch nicht weniger kontrovers wie der Film selbst. Der notorische Regisseur Bo Arne Vibenius verbreitete überall, dass Synapse films den Film von ihm einfach gestohlen habe und schrieb unter falschem Namen Drohbriefe, E-Mails und Faxe. 2002 kaufte Synapse films von Chrome Entertainment die weltweiten Vertriebsrechte für $10.000. Die Gründe weshalb Vibenius gegen das DVD-Label vorging sind unbekannt.&lt;/p&gt;&lt;p&gt;Synapse films arbeitet eng mit anderen kleinen US DVD-Unternehmen zusammen. Die Veröffentlichungen der Legends Of The Poisonous Seductress Serie entstanden zum Beispiel in Zusammenarbeit mit Panik House Entertainment. Des Weiteren hilft Synapse films Impulse pictures ihre DVD-Videos über Ryko Distribution auch in größere Einkaufsketten zu bringen.&lt;/p&gt;&lt;p&gt;Der aus Detroit stammende Filmemacher Nicholas Schlegel hat seine Dokumentation The Synapse Story aufgeteilt in 4 Teile bei YouTube veröffentlicht. Die Dokumentation bietet einen sehr guten Einblick in die Entstehungsgeschichte von Synapse films.&lt;/p&gt;'
  ),
  `dyn_wikipedia`          = COLUMN_CREATE(
    'en', 'http://en.wikipedia.org/wiki/Synapse_Films',
    'de', 'http://de.wikipedia.org/wiki/Synapse_films'
  ),
  `name`                   = 'Synapse films',
  `aliases`                = 'a:1:{i:0;s:18:"Synapse Films, Inc";}',
  `founding_date`          = '1997-00-00',
  `links`                  = 'a:1:{i:0;s:24:"http://synapse-films.com";}',
  `place_id`               = 97981472,
  `image_width`            = 401,
  `image_height`           = 151,
  `image_filesize`         = 40416,
  `image_extension`        = 'jpg',
  `image_changed`          = '2013-11-28 15:13:42',
  `dyn_image_descriptions` = COLUMN_CREATE(
    'en', '&lt;p&gt;The Synapse Films logo.&lt;/p&gt;',
    'de', '&lt;p&gt;Das Synapse Films Logo.&lt;/p&gt;'
  ),
  `image_styles`           = 'a:2:{i:140;a:3:{s:6:"height";i:52;s:5:"width";i:140;s:9:"resizeArg";s:8:"140x140>";}i:60;a:3:{s:6:"height";i:22;s:5:"width";i:60;s:9:"resizeArg";s:4:"60x>";}}',
  `image_uploader_id`      = 1
;

-- END "Synapse films"

-- START "Fox Film Corporation"

INSERT INTO `companies` SET
  `created`                = CURRENT_TIMESTAMP,
  `dyn_descriptions`       = COLUMN_CREATE(
    'en', '&lt;p&gt;The Fox Film Corporation was formed in 1915 by theater chain pioneer William Fox, who formed Fox Film Corporation by merging two companies he had established in 1913: Greater New York Film Rental, a distribution firm, which was part of the Independents; and Fox (or Box, depending on the source) Office Attractions Company, a production company. This merging of companies of two different types was an early example of vertical integration. Only a year before, the latter company had distributed Winsor McCay&#039;s groundbreaking cartoon Gertie the Dinosaur.&lt;/p&gt;&lt;p&gt;Always more of an entrepreneur than a showman, Fox concentrated on acquiring and building theaters; pictures were secondary. The company&#039;s first film studios were set up in Fort Lee, New Jersey where it and many other early film studios in America&#039;s first motion picture industry were based at the beginning of the 20th century. In 1917, William Fox sent Sol M. Wurtzel to Hollywood to oversee the studio&#039;s West Coast production facilities where a more hospitable and cost-effective climate existed for filmmaking. Fox had purchased the Edendale studio of the failing Selig Polyscope Company, which had been making films in Los Angeles since 1909 and was the first motion picture studio in Los Angeles.&lt;/p&gt;&lt;p&gt;With the introduction of sound technology, Fox moved to acquire the rights to a sound-on-film process. In the years 1925–26, Fox purchased the rights to the work of Freeman Harrison Owens, the U.S. rights to the Tri-Ergon system invented by three German inventors, and the work of Theodore Case. This resulted in the Movietone sound system later known as &quot;Fox Movietone&quot;. Later that year, the company began offering films with a music-and-effects track, and the following year Fox began the weekly Fox Movietone News feature, which ran until 1963. The growing company needed space, and in 1926 Fox acquired 300 acres (1.2 km2) in the open country west of Beverly Hills and built &quot;Movietone City&quot;, the best-equipped studio of its time.&lt;/p&gt;&lt;p&gt;When rival Marcus Loew died in 1927, Fox offered to buy the Loew family&#039;s holdings. Loew&#039;s Inc. controlled more than 200 theaters, as well as the MGM studio. When the family agreed to the sale, the merger of Fox and Loew&#039;s Inc. was announced in 1929. But MGM studio boss Louis B. Mayer was not included in the deal and fought back. Using political connections, Mayer called on the Justice Department&#039;s antitrust unit to delay giving final approval to the merger. Fox was badly injured in a car crash in the summer of 1929, and by the time he recovered he had lost most of his fortune in the fall 1929 stock market crash, ending any chance of the merger going through even without the Justice Department&#039;s objections.&lt;/p&gt;&lt;p&gt;Overextended and close to bankruptcy, Fox was stripped of his empire in 1930 and ended up in jail. Fox Film, with more than 500 theatres, was placed in receivership. A bank-mandated reorganization propped the company up for a time, but it soon became apparent that despite its size, Fox could not stay independent. Under new president Sidney Kent, the new owners began negotiating with the upstart, but powerful independent Twentieth Century Pictures in the early spring of 1935.&lt;/p&gt;',
    'de', '&lt;p&gt;Die Fox Film Corporation wurde 1915 von William Fox, einem Pionier der amerikanischen Kinoindustrie, gegründet. Das Unternehmen entstand aus dem Zusammenschluss des Filmverleihs Greater New York Film Rental mit der Produktionsfirma Fox Office Attractions Company. Beide Firmen gehörten William Fox und waren erst zwei Jahre zuvor gegründet worden. Der Zusammenschluss zur Fox Film Corporation war einer der ersten Fälle in der Geschichte der amerikanischen Filmindustrie, bei dem ein Verleih- und ein Produktionsunternehmen fusionierten (Vertikale Integration).&lt;/p&gt;&lt;p&gt;William Fox entdeckte das Potential der bis dahin unbekannten Schauspielerin Theda Bara (1885–1955) und baute sie ab 1915 mit einer bis dahin beispiellosen Publicitykampagne zum ersten Vamp und damit Sexsymbol der Kinogeschichte auf. Weitere Stars aus der Frühzeit des Studios waren die Schwimmerin Annette Kellermann (* 1887), Betty Blythe (* 1887) sowie der populäre Westerndarsteller Tom Mix. 1921 kam der Regisseur John Ford (1894 - 1973) zu Fox und blieb bis weit in die 1930er unter Vertrag. Andere bedeutende Regisseure, die ihre Karriere bei dem Studio begannen waren Raoul Walsh (1887 - 1980), Frank Borzage (1893 - 1962) und Herbert Brenon (1880 - 1958).&lt;/p&gt;&lt;p&gt;Charakteristisch für die Fox Film Corporation war in den ersten Jahren der Vorrang, den die Kinoauswertung vor der Filmproduktion erhielt. Letztere lieferte lediglich das Material, mit dem die Kinos arbeiten konnten. Das erste eigene Filmatelier des Unternehmens entstand in Fort Lee, New Jersey. 1917, als viele andere große Filmproduktionsunternehmen aus New York nach Hollywood umsiedelten, richtete unter dem Management von Winfield Sheehan sowie Sol M. Wurtzel auch die Fox ein Filmstudio in Kalifornien ein.&lt;/p&gt;&lt;p&gt;In der Umbruchphase zum Tonfilm konnte das Studio mit dem Patent am Lichttonverfahren auf Dauer die bessere Technologie vorweisen. Bei diesem Verfahren wird die Tonspur direkt auf das Filmmaterial kopiert. 1925/26 kamen die Rechte an den Erfindungen von Freeman Harrison Owens und Theodore Case sowie am Tri-Ergon-Lichttonverfahren hinzu. Daraus entstand bei Fox das später als Fox Movietone bekannt gewordene Tonverfahren. Mitte der 1920er Jahre begann Fox damit, erstmals eine vertonte Wochenschau herauszubringen, die unter dem Titel Fox Movietone News bis 1963 produziert wurde. 1926 erwarb die Fox Film Corporation westlich von Beverly Hills 1,2 km² unerschlossenes Land und errichtete dort Movietone City, das bestausgestattete Filmstudio seiner Zeit.&lt;/p&gt;&lt;p&gt;1929 bereitete William Fox den Ankauf der Aktienmehrheit der Loew&#039;s, Inc, der Muttergesellschaft des Konkurrenzstudios Metro-Goldwyn-Mayer vor. Daneben übernahm er 45 % der Gaumont-British, der bedeutendsten Filmproduktionsgesellschaft in England. Fox stand unmittelbar vor einer monopolistischen Stellung auf beiden Kinomärkten, als es zu einer Verkettung von Unglücksfällen kam. Fox verunglückte beinahe tödlich bei einem Autounfall, der ihn für zwei Monate ins Krankenhaus zwang. Die Unsicherheit der Aktionäre über die weitere Entwicklung verschärfte die Krise des Studios nach dem Schwarzen Donnerstag. Der Wert der Aktien des Studios fiel innerhalb von zwei Tagen von $ 119 auf $ 1. Daneben drohte die US-Regierung mit einem Antitrust-Verfahren, so dass die Transaktionen in sich zusammenbrachen. 1930 musste Fox sein Imperium an ein Konsortium von Bankern verkaufen. Wie die meisten anderen Gesellschaften hatte auch Fox-Film enorme Verluste in den Folgejahren durch die stark defizitären Kinoketten, auf denen die hohen Investitionen für die Umrüstung auf den Tonfilm lasteten und deren Besucherzahlen gleichzeitig seit 1930 wegen der Weltwirtschaftskrise stark rückläufig waren. Trotz der Popularität seiner Stars wie Janet Gaynor, Will Rogers und seit 1934 Shirley Temple wurde das Studio unter Zwangsverwaltung gestellt. Unter dem neuen Präsidenten Sidney Kent wurde das Unternehmen schließlich 1935 mit dem Filmproduktionsunternehmen Twentieth Century Pictures zur 20th Century Fox zusammengeschlossen.&lt;/p&gt;'
  ),
  `dyn_wikipedia`          = COLUMN_CREATE(
    'en', 'https://en.wikipedia.org/wiki/20th_Century_Fox#Fox_Film_Corporation',
    'de', 'https://de.wikipedia.org/wiki/Fox_Film_Corporation'
  ),
  `name`                   = 'Fox Film Corporation',
  `aliases`                = 'a:1:{i:0;s:16:"20th Century Fox";}',
  `founding_date`          = '1915-00-00',
  `defunct_date`           = '1935-00-00',
  `dyn_image_descriptions` = ''
;

-- END "Fox Film Corporation"

-- Start "Castle Rock Entertainment"

INSERT INTO `companies` SET
  `created`                = CURRENT_TIMESTAMP,
  `dyn_descriptions`       = COLUMN_CREATE(
    'en', '&lt;p&gt;Castle Rock Entertainment is an American film and television production company founded in 1987 by Martin Shafer, director Rob Reiner, Andrew Scheinman, Glenn Padnick and Alan Horn. It is a subsidiary of Warner Bros. Entertainment, which in turn is a unit of Time Warner.&lt;/p&gt;&lt;p&gt;Reiner named the company in honor of the fictional Maine town that serves as the setting of several stories by Stephen King (which was named after the fictional Castle Rock in Lord of the Flies), after the success of his film Stand by Me, which was based on The Body, a novella by King.&lt;/p&gt;&lt;p&gt;Reiner and Scheinman already had a production company. They were friends with Shafer, who worked with Horn at 20th Century Fox at the time. Horn was disappointed at Fox and agreed to join the trio at forming the company. Horn brought along Padnick, who was an executive at Embassy Television. In Castle Rock, Horn became the CEO, Shafer ran the film division, Padnick ran TV, and Reiner &amp; Scheinman became involved in the development of productions.&lt;/p&gt;&lt;p&gt;The company was originally backed by The Coca-Cola Company, the then-parent company of Columbia Pictures. Coke and the company&#039;s founders jointly owned a stake in the company. Months after the deal, Coke exited the entertainment business, succeeded by Columbia Pictures Entertainment (now Sony Pictures Entertainment).&lt;/p&gt;&lt;p&gt;In 1989, Castle Rock was supported by another backer, Group W, a subsidiary of Westinghouse. Castle Rock later struck a deal with Nelson Entertainment, the company that owned the domestic home video rights to Reiner&#039;s This Is Spinal Tap, The Sure Thing, and The Princess Bride, to co-finance Castle Rock&#039;s films.&lt;/p&gt;&lt;p&gt;Under the deal, Nelson also distributed the films on video in North American markets, and handled international theatrical distribution, while Columbia, which Nelson forged a distribution deal with, would receive domestic theatrical distribution rights. Some of Nelson&#039;s holdings were later acquired by New Line Cinema, which took over Nelson&#039;s duty. Columbia, shortly after the company&#039;s formation, thereafter had to re-invest with a substantial change in terms when accumulated losses exhausted its initial funding.&lt;/p&gt;&lt;p&gt;Reiner has stated that Castle Rock&#039;s purpose was to allow creative freedom to individuals; a safe haven away from the pressures of studio executives. Castle Rock was to make films of the highest quality, whether they made or lost money.&lt;/p&gt;&lt;p&gt;Castle Rock has also produced several television shows, such as the successful sitcom Seinfeld and the animated sitcom Mission Hill.&lt;/p&gt;&lt;p&gt;On August 1993, Ted Turner agreed to acquire Castle Rock, along with co-financing partner (and eventual Castle Rock corporate sibling) New Line Cinema. The sale was completed on December 22, 1993. The motivation behind the purchase to allow a stronger company to handle the overhead. Turner Broadcasting System eventually merged with Time Warner in 1996.&lt;/p&gt;',
    'de', '&lt;p&gt;Castle Rock Entertainment (früher als CR abgekürzt) ist eine ehemalige US-amerikanische Filmproduktionsgesellschaft, die 1987 von fünf unabhängigen Filmproduzenten gegründet wurde. Das ehemalige Unternehmen ist heute als Abteilung von Warner Bros. organisiert, einer Tochtergesellschaft von Time Warner.&lt;/p&gt;&lt;p&gt;Castle Rock ist eine fiktive Kleinstadt in Maine, in der viele von Stephen Kings Geschichten beheimatet sind, unter anderem auch die Kurzgeschichte Die Leiche aus der Kurzgeschichtensammlung Frühling, Sommer, Herbst und Tod, die der Regisseur Rob Reiner im Jahre 1986 unter dem Titel Stand by Me – Das Geheimnis eines Sommers filmisch umsetzte (siehe: Castle-Rock-Zyklus).&lt;/p&gt;&lt;p&gt;Aufgrund des Erfolgs des Spielfilms gründete Reiner mit vier anderen Filmproduzenten im Jahre 1987 die Filmproduktionsgesellschaft Castle Rock Entertainment, die Harry und Sally im Jahre 1989 als ersten Film veröffentlichte.&lt;/p&gt;&lt;p&gt;In den ersten Jahren arbeitete Castle Rock Entertainment eng mit Nelson Entertainment und Columbia Pictures zusammen, wobei erstere Anteile an Castle Rock besaß. Columbia besorgte den Kinovertrieb in den USA, während Nelson sich vor allem um den Home-Entertainment-Vertrieb kümmerte.&lt;/p&gt;&lt;p&gt;Castle Rock setzte sechs King-Romane filmisch um, darunter auch den mit einem Oscar für die Beste Hauptdarstellerin ausgezeichneten Thriller Misery aus dem Jahre 1990.&lt;/p&gt;&lt;p&gt;Als Ted Turners Turner Broadcasting System (TBS) 1993 New Line Cinema erwarb, erwarb man indirekt auch die Anteile an Nelson, die New Line gehörten. TBS veräußerte die Anteile an Nelson und erwarb per Anfang 1994 Castle Rock Entertainment. Damit übernahm Castle Rock/Turner die Home-Entertainment-Rechte und baute in Zusammenarbeit mit lokalen Filmverleihern Vertriebsniederlassungen auf (Rank-Castle Rock/Turner in Großbritannien, Concorde-Castle Rock/Turner in Deutschland, Filmayer-Castle Rock/Turner in Spanien).&lt;/p&gt;&lt;p&gt;TBS wurde per Ende 1996 mit Time Warner fusioniert und die betroffenen Unternehmen restrukturiert. Die Anzahl der Produktionen von Castle Rock wurde stark eingeschränkt und die Vertriebsabkommen im Ausland wurden im Laufe des Jahres 1997 durch den Rückzug von Castle Rock aufgelöst.&lt;/p&gt;&lt;p&gt;Das Vertriebsabkommen mit Columbia Pictures wurde 1999 aufgelöst und Warner Bros. übernahm den US-Kinovertrieb, während gleichzeitig Universal Pictures mit dem internationalen Kinovertrieb betraut wurde, der über United International Pictures (UIP) abgewickelt wurde.&lt;/p&gt;&lt;p&gt;Im Zuge der sogenannten AOL-Krise bei Time Warner wurde 2003 der komplette Konzern restrukturiert. Dabei wurde Castle Rock Entertainment von einer halbautonomen Geschäftseinheit von Time Warner in eine Abteilung von Warner Bros. Entertainment umgewandelt, die vor allem noch als Marke weiterbesteht. Die weltweiten Vertriebsrechte für Castle-Rock-Produktionen liegen seither ausschließlich bei Warner.&lt;/p&gt;'
  ),
  `dyn_wikipedia`          = COLUMN_CREATE(
    'en', 'http://en.wikipedia.org/wiki/Castle_Rock_Entertainment',
    'de', 'http://de.wikipedia.org/wiki/Castle_Rock_Entertainment'
  ),
  `name`                   = 'Castle Rock Entertainment',
  `aliases`                = 'a:0:{}',
  `founding_date`          = '1994-00-00',
  `dyn_image_descriptions` = ''
;

INSERT INTO `jobs` SET
  `created`                = CURRENT_TIMESTAMP,
  `dyn_descriptions`       = COLUMN_CREATE(
    'en', '&lt;p&gt;A production company provides the physical basis for works in the realms of the performing arts, new media art, film, television, radio, and video.&lt;/p&gt;&lt;p&gt;The production company may be directly responsible for fundraising for the production or may accomplish this through a parent company, partner, or private investor. It handles budgeting, scheduling, scripting, the supply with talent and resources, the organization of staff, the production itself, post-production, distribution, and marketing. Production companies are often either owned or under contract with a media conglomerate, film studio, entertainment company, or Motion Picture Company, who act as the production company&#039;s partner or parent company. This has become known as the &quot;studio system&quot;. They can also be mainstream independent (see Lucasfilms) or completely independent (see Lionsgate). In the case of TV, a TV production company would serve under a television network. Production companies can work together in co-productions.&lt;/p&gt;',
    'de', '&lt;p&gt;Unter einer Filmproduktionsgesellschaft versteht man ein Unternehmen, das seine Einnahmen überwiegend aus der Produktion von Filmen erwirtschaftet.&lt;/p&gt;&lt;p&gt;Das Arbeitsgebiet einer Filmproduktionsgesellschaft umfasst dabei alle Phasen der Filmproduktion, von der Stoffentwicklung und Filmfinanzierung über die Produktionsvorbereitung und die Dreharbeiten bis hin zur Postproduktion. Kleinere Unternehmen, die keinen eigenen Verleihbetrieb haben und fertige Produktionen zur Kinoauswertung einem externen Filmverleih anvertrauen, erledigen in der Übergangszeit, bis ein Verleih gefunden ist, auch Aufgaben der Filmherausbringung (Launch) wie die Anmeldung des Films bei der FSK und der Filmbewertungsstelle (beides nur in Deutschland), Öffentlichkeitsarbeit und Werbung, Festivalbewerbungen und die Organisation einer - oft groß aufgezogenen - Kinopremiere, einschließlich der Einwerbung von Sponsoren- und Fördergeldern zur Finanzierung dieser Arbeiten.&lt;/p&gt;'
  ),
  `dyn_titles`             = COLUMN_CREATE(
    'en', 'Production Company',
    'de', 'Filmproduktionsgesellschaft'
  )
;

SET @production_company_id = LAST_INSERT_ID();

INSERT INTO `movies_crew` SET
  `movie_id`    = 3,
  `company_id`  = 3,
  `job_id`      = @production_company_id
;

-- END "Castle Rock Entertainment"
