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
-- Award seed data.
--
-- @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
-- @copyright © 2013 MovLib
-- @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
-- @link https://movlib.org/
-- @since 0.0.1-dev
-- ---------------------------------------------------------------------------------------------------------------------

TRUNCATE TABLE `awards`;
TRUNCATE TABLE `awards_categories`;
TRUNCATE TABLE `awards_events`;
TRUNCATE TABLE `movies_awards`;

-- START "Oscar"

INSERT INTO `places` SET
  `place_id`     = 5368361,
  `country_code` = 'US',
  `dyn_names`    = COLUMN_CREATE(
    'en', 'Los Angeles',
    'de', 'Los Angeles'
  ),
  `latitude`     = 89,
  `longitude`    = -118.24368
;
SET @oscar_place_id = LAST_INSERT_ID();

INSERT INTO `awards` SET
  `created`                = CURRENT_TIMESTAMP,
  `dyn_descriptions`       = COLUMN_CREATE(
    'en', '&lt;p&gt;The Academy Awards, commonly known as The Oscars, is an annual American awards ceremony honoring achievements in the film industry. Winners are awarded the statuette, officially the Academy Award of Merit, that is much better known by its nickname Oscar. The awards, first presented in 1929 at the Hollywood Roosevelt Hotel, are overseen by the Academy of Motion Picture Arts and Sciences (AMPAS).&lt;/p&gt;&lt;p&gt;The awards ceremony was first televised in 1953 and is now seen live in more than 200 countries. The Oscars is also the oldest entertainment awards ceremony; its equivalents, the Emmy Awards for television, the Tony Awards for theatre, and the Grammy Awards for music and recording, are modeled after the Academy Awards.&lt;/p&gt;&lt;p&gt;The 86th Academy Awards were held on March 2, 2014, at the Dolby Theatre in Los Angeles.&lt;/p&gt;',
    'de', '&lt;p&gt;Der Academy Award, besser bekannt unter seinem Spitznamen Oscar, ist ein Filmpreis. Er wird jährlich von der US-amerikanischen Academy of Motion Picture Arts and Sciences (AMPAS) für die besten Filme des Vorjahres verliehen, wobei wegen der Zulassungsprozedur in der Regel US-Produktionen dominieren. Die letzte Verleihung fand am 2. März 2014 in Los Angeles statt.&lt;/p&gt;&lt;p&gt;Die Auszeichnung wurde am 12. Februar 1929 vom damaligen Präsidenten der MGM Studios, Louis B. Mayer, ins Leben gerufen, fast neun Jahre nach der Verleihung des Photoplay Awards, der als erster Filmpreis der Welt gilt. Der Oscar wird jährlich in einer gemeinsamen Zeremonie in derzeit über 30 verschiedenen Kategorien in Form jeweils einer Statuette vergeben, die einen Ritter mit einem Schwert auf einer Filmrolle darstellt.&lt;/p&gt;&lt;p&gt;In die Auswahl zur Verleihung eines oder auch mehrerer Oscars kommen hauptsächlich amerikanische Spielfilme. In jeweils eigenen Kategorien werden Kurz-, Dokumentar-, Animations- und ausländische Filme prämiert. Für die Qualifikation eines amerikanischen Spielfilms zur Auswahl gilt die Bedingung, dass er im Vorjahr der Verleihung mindestens sieben Tage lang in einem öffentlichen Kino im Gebiet von Los Angeles County – dem Heimatbezirk von Hollywood – gegen Entgelt gezeigt wurde.&lt;/p&gt;'
  ),
  `dyn_wikipedia`          = COLUMN_CREATE(
    'en', 'http://en.wikipedia.org/wiki/Academy_Award',
    'de', 'http://de.wikipedia.org/wiki/Oscar'
  ),
  `dyn_names`              = COLUMN_CREATE(
    'en', 'Academy Awards',
    'de', 'Oscar'
  ),
  `first_awarding_year`    = 1929,
  `aliases`                = 'a:2:{i:0;s:5:"Oscar";i:1;s:14:"Academy Awards";}',
  `links`                  = 'a:1:{i:0;s:22:"http://www.oscars.org/";}',
  `image_width`            = 1875,
  `image_height`           = 1968,
  `image_filesize`         = 383689,
  `image_extension`        = 'jpg',
  `image_changed`          = '2014-03-17 11:59:18',
  `dyn_image_descriptions` = COLUMN_CREATE(
    'en', '&lt;p&gt;Academy Awards Logo | oscars.org&lt;/p&gt;&lt;p&gt;The image has no threshold of originality according to Austrian-law.&lt;/p&gt;&lt;p&gt;The image consists of a simple lettering and simplest forms and therefore has no threshold of originality according to international law.&lt;/p&gt;&lt;p&gt;Can be a registered trade mark or design.&lt;/p&gt;',
    'de', '&lt;p&gt;Academy Awards Logo | oscars.org&lt;/p&gt;&lt;p&gt;Das Logo besitzt nach deutschsprachigem Recht keine Schöpfungshöhe.&lt;/p&gt;&lt;p&gt;Das Logo besteht aus einem einfachen Schriftzug sowie ggf. einfachsten Formen und besitzt daher international keine Schöpfungshöhe.&lt;/p&gt;&lt;p&gt;Das Logo kann dem Marken- oder Gebrauchsmusterrecht unterliegen.&lt;/p&gt;'
  ),
  `image_styles`           = 'a:3:{i:220;a:3:{s:6:"height";i:231;s:5:"width";i:220;s:9:"resizeArg";s:3:"220";}i:140;a:3:{s:6:"height";i:147;s:5:"width";i:140;s:9:"resizeArg";s:3:"140";}i:60;a:3:{s:6:"height";i:63;s:5:"width";i:60;s:9:"resizeArg";s:2:"60";}}',
  `image_uploader_id`      = 1
;

SET @oscar_award_id = LAST_INSERT_ID();

INSERT INTO `awards_categories` SET
  `award_id`            = @oscar_award_id,
  `created`             = CURRENT_TIMESTAMP,
  `dyn_names`           = COLUMN_CREATE(
    'en', 'Best Assistant Director',
    'de', 'Beste Regieassistenz'
  ),
  `dyn_descriptions`    = COLUMN_CREATE(
    'en', '&lt;p&gt;With the Oscar for best assistant director, the assistant directors of films were honored. The prize was awarded in this category between 1933 and 1937.&lt;/p&gt;',
    'de', '&lt;p&gt;Mit dem Oscar für die beste Regieassistenz wurden die Regieassistenten eines Films geehrt. Der Preis wurde in dieser Kategorie zwischen 1933 und 1937 vergeben.&lt;/p&gt;'
  ),
  `dyn_wikipedia`       = COLUMN_CREATE(
    'en', 'http://en.wikipedia.org/wiki/Academy_Award_for_Best_Assistant_Director',
    'de', 'http://de.wikipedia.org/wiki/Oscar/Beste_Regieassistenz'
  ),
  `first_awarding_year` = 1933,
  `last_awarding_year`  = 1937
;

INSERT INTO `awards_categories` SET
  `award_id`            = @oscar_award_id,
  `created`             = CURRENT_TIMESTAMP,
  `dyn_names`           = COLUMN_CREATE(
    'en', 'Best Picture',
    'de', 'Bester Film'
  ),
  `dyn_descriptions`    = COLUMN_CREATE(
    'en', '&lt;p&gt;The Academy Award for Best Picture is one of the Academy Awards of Merit presented annually by the Academy of Motion Picture Arts and Sciences (AMPAS) to producers working in the film industry and is the only category in which every member is eligible to submit a nomination. Best Picture is considered the most important of the Academy Awards, as it represents all the directing, acting, music composing, writing, editing and other efforts put forth into a film. Consequently, Best Picture is the final award of every Academy Awards ceremony. The Grand Staircase columns at the Dolby Theatre in Los Angeles, where the Academy Awards ceremonies have been held since 2002, showcase every film that has won the Best Picture title since the award&#039;s inception. As of the 86th Academy Awards nominations, there have been 512 films nominated for the Best Picture award.&lt;/p&gt;',
    'de', '&lt;p&gt;Mit dem Oscar für den besten Film werden die Produzenten eines Films ausgezeichnet. In der Regel können drei Personen als Produzenten eines Films nominiert werden. Ab der 80. Verleihung des Oscars können unter bestimmten Umständen mehr als drei Personen als Produzenten nominiert werden. Bis 1936 gingen die Auszeichnungen an die Produktionsgesellschaft.&lt;/p&gt;&lt;p&gt;Die Anzahl der nominierten Filme wurde erstmals zur Oscarverleihung 2010 von fünf auf zehn erhöht, um Fantasy- oder Animationsfilmen wie Oben eine Nominierung in dieser Kategorie zu ermöglichen. Nach zweimaliger Durchführung wurde zur Oscarverleihung 2012 entschieden, dass künftig zwischen fünf und zehn Filme nominiert werden. Nominiert werden Filme, die mindestens fünf Prozent der Stimmen des bei der Vorauswahl beliebtesten Films erhalten haben.&lt;/p&gt;'
  ),
  `dyn_wikipedia`       = COLUMN_CREATE(
    'en', 'http://en.wikipedia.org/wiki/Academy_Award_for_Best_Picture',
    'de', 'http://de.wikipedia.org/wiki/Oscar/Bester_Film'
  ),
  `first_awarding_year` = 1929
;

INSERT INTO `awards_categories` SET
  `award_id`            = @oscar_award_id,
  `created`             = CURRENT_TIMESTAMP,
  `dyn_names`           = COLUMN_CREATE(
    'en', 'Best Director',
    'de', 'Beste Regie'
  ),
  `dyn_descriptions`    = COLUMN_CREATE(
    'en', '&lt;p&gt;The Academy Award for Best Directing (Best Director), usually known as the Best Director Oscar, is one of the Awards of Merit presented by the Academy of Motion Picture Arts and Sciences (AMPAS) to directors working in the motion picture industry. While nominations for Best Director are made by members in the Academy&#039;s Directing branch, the award winners are selected by the Academy membership as a whole.&lt;/p&gt;',
    'de', '&lt;p&gt;Mit dem Oscar für die Beste Regie werden die Leistungen der Regisseure eines Films geehrt.&lt;/p&gt;&lt;p&gt;Acht Regisseure wurden als bester Regisseur und bester Hauptdarsteller für denselben Film nominiert: Warren Beatty für (Heaven Can Wait und Reds), Clint Eastwood für (Unforgiven und Million Dollar Baby), Orson Welles (Citizen Kane), Laurence Olivier (Hamlet), Woody Allen (Annie Hall), Kenneth Branagh (Henry V), Kevin Costner (Dances with Wolves) und Roberto Benigni (Life Is Beautiful).&lt;/p&gt;&lt;p&gt;Die einzigen Brüder, die gemeinsam nominiert wurden, waren Joel und Ethan Coen für No Country for Old Men (2007) und True Grit (2010).&lt;/p&gt;&lt;p&gt;John Ford und Joseph L. Mankiewicz sind die einzigen Titelverteidiger&lt;/p&gt;'
  ),
  `dyn_wikipedia`       = COLUMN_CREATE(
    'en', 'http://en.wikipedia.org/wiki/Academy_Award_for_Best_Director',
    'de', 'http://de.wikipedia.org/wiki/Oscar/Beste_Regie'
  ),
  `first_awarding_year` = 1929
;

INSERT INTO `awards_categories` SET
  `award_id`            = @oscar_award_id,
  `created`             = CURRENT_TIMESTAMP,
  `dyn_names`           = COLUMN_CREATE(
    'en', 'Best Actor in a Leading Role',
    'de', 'Bester Hauptdarsteller'
  ),
  `dyn_descriptions`    = '',
  `dyn_wikipedia`       = '',
  `first_awarding_year` = 1929
;

INSERT INTO `awards_categories` SET
  `award_id`            = @oscar_award_id,
  `created`             = CURRENT_TIMESTAMP,
  `dyn_names`           = COLUMN_CREATE(
    'en', 'Best Actress in a Leading Role',
    'de', 'Beste Hauptdarstellerin'
  ),
  `dyn_descriptions`    = '',
  `dyn_wikipedia`       = '',
  `first_awarding_year` = 1929
;

INSERT INTO `awards_categories` SET
  `award_id`            = @oscar_award_id,
  `created`             = CURRENT_TIMESTAMP,
  `dyn_names`           = COLUMN_CREATE(
    'en', 'Best Actor in a Supporting Role',
    'de', 'Bester Nebendarsteller'
  ),
  `dyn_descriptions`    = '',
  `dyn_wikipedia`       = '',
  `first_awarding_year` = 1937
;

INSERT INTO `awards_categories` SET
  `award_id`            = @oscar_award_id,
  `created`             = CURRENT_TIMESTAMP,
  `dyn_names`           = COLUMN_CREATE(
    'en', 'Best Actress in a Supporting Role',
    'de', 'Beste Nebendarstellerin'
  ),
  `dyn_descriptions`    = '',
  `dyn_wikipedia`       = '',
  `first_awarding_year` = 1929
;

INSERT INTO `awards_categories` SET
  `award_id`            = @oscar_award_id,
  `created`             = CURRENT_TIMESTAMP,
  `dyn_names`           = COLUMN_CREATE(
    'en', 'Best Writing (Adapted Screenplay)',
    'de', 'Bestes adaptiertes Drehbuch'
  ),
  `dyn_descriptions`    = COLUMN_CREATE(
    'en', '&lt;p&gt;The Academy Award for Best Adapted Screenplay is one of the Academy Awards, the most prominent film awards in the United States. It is awarded each year to the writer of a screenplay adapted from another source (usually a novel, play, short story, or TV show but sometimes another film). All sequels are automatically considered adaptations by this standard (since the sequel must be based on the original story).&lt;/p&gt;',
    'de', '&lt;p&gt;Als Drehbuch nach literarischer Vorlage (Adapted Screenplay) wird eine Form des Drehbuchs bezeichnet, bei dem das Skript auf einer zuvor veröffentlichten Publikation beruht (wie einem Roman, einem Theaterstück oder einem bereits verfilmten Drehbuch), ist also Basis von sogenannten Literaturverfilmungen. Anfangs unregelmäßig, vergibt die Filmakademie der USA seit 1929 ihren Preis (Oscar) in dieser Kategorie. Im Gegensatz dazu steht die Kategorie Originaldrehbuch, das ohne Vorlage verfasst wird.&lt;/p&gt;'
  ),
  `dyn_wikipedia`       = COLUMN_CREATE(
    'en', 'http://en.wikipedia.org/wiki/Academy_Award_for_Best_Writing_(Adapted_Screenplay)',
    'de', 'http://de.wikipedia.org/wiki/Oscar/Bestes_adaptiertes_Drehbuch'
  ),
  `first_awarding_year` = 1929
;
SET @oscar_award_category_id = LAST_INSERT_ID();

INSERT INTO `awards_events` SET
  `award_id`            = @oscar_award_id,
  `dyn_names`           = COLUMN_CREATE(
    'en', '67th Academy Awards',
    'de', 'Oscarverleihung 1995'
  ),
  `dyn_descriptions`    = COLUMN_CREATE(
    'en', '&lt;p&gt;The 67th Academy Awards, honoring the best films of 1994, were held on March 27, 1995, at the Shrine Auditorium, Los Angeles, California. They were hosted by comedian and talk show host David Letterman.&lt;/p&gt;&lt;p&gt;The ceremony is perhaps best remembered for Letterman&#039;s performance as the host. Although some thought of him as different but good, most critics labeled his performance as terrible and expressed a wish for him never to host the Oscars again. This negative criticism arose from Letterman&#039;s absurdist brand of comedy, and it was followed by Late Show with David Letterman losing in the ratings to The Tonight Show with Jay Leno by the summer of 1995.&lt;/p&gt;&lt;p&gt;Letterman seems to have a sense of humor about it, however, because around Academy Award season he frequently references his lackluster appearance at the Academy awards on his show in a humorous tone.&lt;/p&gt;&lt;p&gt;Forrest Gump won Best Picture, as well as an additional five Oscars, including Tom Hanks&#039; second consecutive Academy Award for Best Actor. Hanks became only the second person in Oscar history to accomplish the feat of winning consecutive awards in the Best Actor category, the first being Spencer Tracy. Also, Jessica Lange, winner of the 1982 Academy Award for Best Supporting Actress for Tootsie, won the Academy Award for Best Actress for Tony Richardson&#039;s last film, Blue Sky, joining an elite group of thespians who have won Oscars in both the supporting and lead categories. Dianne Wiest won her second Academy Award for Best Supporting Actress in a Woody Allen film, becoming the first person to win two Oscars in the same category where the films were directed by the same person (she won another Best Supporting Actress in 1986 for Hannah and Her Sisters).&lt;/p&gt;&lt;p&gt;This year had the rarity of producing a tie. When Tim Allen opened the envelope for Best Live Action Short, much to his surprise there was a tie. There would not be another tie in an Academy Award category for another 18 years, when the award for Best Sound Editing went to both Skyfall and Zero Dark Thirty during the 85th Academy Awards.&lt;/p&gt;&lt;p&gt;The awards this year were also notable for the near inclusion of a documentary as Best Picture. The documentary category was then, as always, nominated by a special committee. The critically acclaimed film Hoop Dreams failed to make the documentary committee&#039;s short list, even though it was on more critics&#039; top ten lists than any other film that year, including Forrest Gump, The Shawshank Redemption, Pulp Fiction and Quiz Show. Many prominent critics, most notably Gene Siskel and Roger Ebert campaigned for Academy members to vote to nominate Hoop Dreams for Best Picture, something that had never happened before. The effort failed, yet Hoop Dreams was nominated for Best Film Editing, one of the few documentaries ever to be nominated in a craft category.&lt;/p&gt;&lt;p&gt;This was only the second, and most recent, time in Oscar history where three of the four acting winners were repeats; the other time was during the 1938 Oscars. Interestingly enough, the only first timer was Martin Landau who was the oldest of the bunch.&lt;/p&gt;',
    'de', '&lt;p&gt;Die Oscarverleihung 1995 fand am 27. März 1995 im Shrine Auditorium in Los Angeles statt. Es waren die 67th Annual Academy Awards. Im Jahr der Auszeichnung werden immer Filme des vergangenen Jahres ausgezeichnet, in diesem Fall also die Filme des Jahres 1994.&lt;/p&gt;'
  ),
  `dyn_wikipedia`       = COLUMN_CREATE(
    'en', 'http://en.wikipedia.org/wiki/67th_Academy_Awards',
    'de', 'http://de.wikipedia.org/wiki/Oscarverleihung_1995'
  ),
  `start_date`          = '1995-03-27',
  `links`               = 'a:2:{i:0;s:80:"http://www.oscars.org/awards/academyawards/oscarlegacy/1990-1999/67nominees.html";i:1;s:112:"http://www.nytimes.com/1995/01/29/movies/bear-hunting-in-oscar-season-five-strategies.html?pagewanted=all&src=pm";}',
  `place_id`            = @oscar_place_id
;
SET @oscar_award_event_id = LAST_INSERT_ID();

INSERT INTO `movies_awards` SET
  `movie_id`          = 3, /* The Shawshank Redemption */
  `award_id`          = @oscar_award_id,
  `award_category_id` = @oscar_award_category_id,
  `award_event_id`    = @oscar_award_event_id,
  `person_id`         = 7, /* Frank Darabont */
  `won`               = false,
  `year`              = 1995
;

-- END "Oscar"
