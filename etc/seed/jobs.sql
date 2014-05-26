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
-- Job seed data.
--
-- @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
-- @copyright © 2013 MovLib
-- @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
-- @link https://movlib.org/
-- @since 0.0.1-dev
-- ---------------------------------------------------------------------------------------------------------------------

TRUNCATE TABLE `jobs`;

-- START "acting"

INSERT INTO `jobs` SET
  `created`          = CURRENT_TIMESTAMP,
  `changed`          = CURRENT_TIMESTAMP,
  `dyn_titles_sex0`  = COLUMN_CREATE(
    'en', 'Acting',
    'de', 'Schauspiel'
  ),
  `dyn_titles_sex1`  = COLUMN_CREATE(
    'en', 'Actor',
    'de', 'Schauspieler'
  ),
  `dyn_titles_sex2`  = COLUMN_CREATE(
    'en', 'Actress',
    'de', 'Schauspielerin'
  ),
  `dyn_descriptions` = '',
  `dyn_wikipedia`    = COLUMN_CREATE(
    'en', 'http://en.wikipedia.org/wiki/Actor',
    'de', 'http://de.wikipedia.org/wiki/Schauspieler'
  )
;

-- END "acting"

-- START "direction"

INSERT INTO `jobs` SET
  `created`          = CURRENT_TIMESTAMP,
  `changed`          = CURRENT_TIMESTAMP,
  `dyn_titles_sex0`  = COLUMN_CREATE(
    'en', 'Direction',
    'de', 'Regie'
  ),
  `dyn_titles_sex1`  = COLUMN_CREATE(
    'en', 'Director',
    'de', 'Regisseur'
  ),
  `dyn_titles_sex2`  = COLUMN_CREATE(
    'en', 'Director',
    'de', 'Regisseurin'
  ),
  `dyn_descriptions` = '',
  `dyn_wikipedia`    = COLUMN_CREATE(
    'en', 'http://en.wikipedia.org/wiki/Film_director',
    'de', 'http://de.wikipedia.org/wiki/Filmregisseur'
  )
;

-- END "direction"

-- START "production"

INSERT INTO `jobs` SET
  `created`          = CURRENT_TIMESTAMP,
  `changed`          = CURRENT_TIMESTAMP,
  `dyn_descriptions` = COLUMN_CREATE(
    'en', '&lt;p&gt;A production company provides the physical basis for works in the realms of the performing arts, new media art, film, television, radio, and video.&lt;/p&gt;&lt;p&gt;The production company may be directly responsible for fundraising for the production or may accomplish this through a parent company, partner, or private investor. It handles budgeting, scheduling, scripting, the supply with talent and resources, the organization of staff, the production itself, post-production, distribution, and marketing. Production companies are often either owned or under contract with a media conglomerate, film studio, entertainment company, or Motion Picture Company, who act as the production company&#039;s partner or parent company. This has become known as the &quot;studio system&quot;. They can also be mainstream independent (see Lucasfilms) or completely independent (see Lionsgate). In the case of TV, a TV production company would serve under a television network. Production companies can work together in co-productions.&lt;/p&gt;',
    'de', '&lt;p&gt;Unter einer Filmproduktionsgesellschaft versteht man ein Unternehmen, das seine Einnahmen überwiegend aus der Produktion von Filmen erwirtschaftet.&lt;/p&gt;&lt;p&gt;Das Arbeitsgebiet einer Filmproduktionsgesellschaft umfasst dabei alle Phasen der Filmproduktion, von der Stoffentwicklung und Filmfinanzierung über die Produktionsvorbereitung und die Dreharbeiten bis hin zur Postproduktion. Kleinere Unternehmen, die keinen eigenen Verleihbetrieb haben und fertige Produktionen zur Kinoauswertung einem externen Filmverleih anvertrauen, erledigen in der Übergangszeit, bis ein Verleih gefunden ist, auch Aufgaben der Filmherausbringung (Launch) wie die Anmeldung des Films bei der FSK und der Filmbewertungsstelle (beides nur in Deutschland), Öffentlichkeitsarbeit und Werbung, Festivalbewerbungen und die Organisation einer - oft groß aufgezogenen - Kinopremiere, einschließlich der Einwerbung von Sponsoren- und Fördergeldern zur Finanzierung dieser Arbeiten.&lt;/p&gt;'
  ),
  `dyn_titles_sex0`  = COLUMN_CREATE(
    'en', 'Production',
    'de', 'Produktion'
  ),
  `dyn_titles_sex1`  = COLUMN_CREATE(
    'en', 'Producer',
    'de', 'Produzent'
  ),
  `dyn_titles_sex2`  = COLUMN_CREATE(
    'en', 'Producer',
    'de', 'Produzentin'
  ),
  `dyn_wikipedia`          = COLUMN_CREATE(
    'en', 'http://en.wikipedia.org/wiki/Filmmaking',
    'de', 'http://de.wikipedia.org/wiki/Filmproduktion'
  )
;
SET @production_company_id = LAST_INSERT_ID();

-- END "production"

-- START "screenwriting"

INSERT INTO `jobs` SET
  `created`          = CURRENT_TIMESTAMP,
  `changed`          = CURRENT_TIMESTAMP,
  `dyn_titles_sex0`  = COLUMN_CREATE(
    'en', 'Screenwriting',
    'de', 'Drehbuch'
  ),
  `dyn_titles_sex1`  = COLUMN_CREATE(
    'en', 'Screenwriter',
    'de', 'Drehbuchautor'
  ),
  `dyn_titles_sex2`  = COLUMN_CREATE(
    'en', 'Screenwriter',
    'de', 'Drehbuchautorin'
  ),
  `dyn_descriptions` = '',
  `dyn_wikipedia`    = COLUMN_CREATE(
    'en', 'http://en.wikipedia.org/wiki/Screenwriter',
    'de', 'http://de.wikipedia.org/wiki/Drehbuchautor'
  )
;

-- END "screenwriting"

-- START "makeup artist"

INSERT INTO `jobs` SET
  `created`          = CURRENT_TIMESTAMP,
  `changed`          = CURRENT_TIMESTAMP,
  `deleted`          = true,
  `dyn_descriptions` = COLUMN_CREATE(
    'en', '&lt;p&gt;A make-up artist (or &#039;makeup artist&#039;) is an artist whose medium is the human body, applying makeup and prosthetics for theatrical, television, film, fashion, magazines and other similar productions including all aspects of the modeling industry. Awards given for this profession in the entertainment industry include the Academy Award for Best Makeup and Hairstyling and even several entertainment industry awards such as the Emmy Awards and the Golden Globes to name a few. In the United States as well as the other parts of the globe, professional licenses are required by agencies in order for them to hire the MUA. Bigger production companies[5] have in-house makeup artists on their payroll although most MUA’s generally are freelance and their times remain flexible depending on the projects.The use of digital cameras may have made the use of bridal make up more popular.&lt;/p&gt;',
    'de', '&lt;p&gt;Visagist (vom franz. Visage, das Gesicht) ist eine Tätigkeitsbezeichnung für jemanden, der andere Menschen schminkt und stylt. Im Gegensatz zu anerkannten Ausbildungsberufen wie Kosmetiker oder Maskenbildner ist die Tätigkeitsbezeichnung Visagist nicht gesetzlich geschützt. Private Kosmetikschulen bieten Visagistenkurse und vermitteln oft in nur wenigen Wochen Kenntnisse in Make-up, Typberatung und auch Haarstyling. Der Schwerpunkt der Tätigkeiten liegt dabei im Gesicht des Menschen. Am Ende werden verschiedene Zertifikate für die Teilnahme verteilt.&lt;/p&gt;'
  ),
  `dyn_titles_sex0`  = COLUMN_CREATE(
    'en', 'Makeup',
    'de', 'Makeup'
  ),
  `dyn_titles_sex1`  = COLUMN_CREATE(
    'en', 'Makeup Artist',
    'de', 'Visagist'
  ),
  `dyn_titles_sex2`  = COLUMN_CREATE(
    'en', 'Makeup Artist',
    'de', 'Visagistin'
  ),
  `dyn_wikipedia`    = COLUMN_CREATE(
    'en', 'http://en.wikipedia.org/wiki/Make-up_artist',
    'de', 'http://de.wikipedia.org/wiki/Visagist'
  )
;
SET @makeup_company_id = LAST_INSERT_ID();

-- END "makeup artist"

INSERT INTO `movies_crew` SET
  `movie_id`    = 3,
  `company_id`  = 3,
  `job_id`      = @production_company_id,
  `dyn_role`    = ''
;

INSERT INTO `movies_crew` SET
  `movie_id`    = 3,
  `company_id`  = 3,
  `job_id`      = @makeup_company_id,
  `dyn_role`    = ''
;
