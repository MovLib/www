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
  `dyn_names`          = COLUMN_CREATE(
    'en', 'Academy Awards',
    'de', 'Oscar'
  ),
  `links`                  = 'a:1:{i:0;s:22:"http://www.oscars.org/";}',
  `place_id`               = 5368361,
  `image_width`            = 1536,
  `image_height`           = 2560,
  `image_filesize`         = 3200007,
  `image_extension`        = 'jpg',
  `image_changed`          = '2013-11-28 15:13:42',
  `dyn_image_descriptions` = COLUMN_CREATE(
    'en', '&lt;p&gt;Cate Blanchett&#039;s Oscar for playing Katharine Hepburn in The Aviator in 2004. It is on permanent display at the Australian Centre for the Moving Image.&lt;/p&gt;',
    'de', '&lt;p&gt;Cate Blanchett Oscar für die Rolle von Katharine Hepburn in The Aviator im Jahr 2004, ausgestellt in Australiens Zentrum für bewegte Bilder.&lt;/p&gt;'
  ),
  `image_styles`           = 'a:3:{i:220;a:3:{s:6:"height";i:132;s:5:"width";i:220;s:9:"resizeArg";s:8:"220x220>";}i:140;a:3:{s:6:"height";i:84;s:5:"width";i:140;s:9:"resizeArg";s:8:"140x140>";}i:60;a:3:{s:6:"height";i:36;s:5:"width";i:60;s:9:"resizeArg";s:6:"60x60>";}}',
  `image_uploader_id`      = 1
;

-- END "Oscar"