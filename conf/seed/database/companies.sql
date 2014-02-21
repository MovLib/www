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
  `aliases`                = 's:22:"["Synapse Films, Inc"]',
  `founding_date`          = '1997-00-00',
  `links`                  = 's:28:"["http://synapse-films.com"]"',

  `image_width`            = 401,
  `image_height`           = 151,
  `image_filesize`         = 40416,
  `image_extension`        = 'jpg',
  `image_changed`          = '2013-11-28 15:13:42',
  `dyn_image_descriptions` = COLUMN_CREATE(
    'en', '&lt;p&gt;The Synapse Films logo.&lt;/p&gt;',
    'de', '&lt;p&gt;Das Synapse Films Logo.&lt;/p&gt;'
  ),
  `image_styles`           = 'a:2:{i:140;a:3:{s:6:"height";i:140;s:5:"width";i:140;s:9:"resizeArg";s:8:"140x140>";}i:60;a:3:{s:6:"height";i:60;s:5:"width";i:60;s:9:"resizeArg";s:4:"60x>";}} ',
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
  `aliases`                = 's:20:"["20th Century Fox"]";',
  `founding_date`          = '1915-00-00',
  `defunct_date`           = '1935-00-00',
  `dyn_image_descriptions` = ''
;

-- END "Fox Film Corporation"