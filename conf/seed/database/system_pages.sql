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
-- System pages seed data.
--
-- @author Richard Fussenegger <richard@fussenegger.info>
-- @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
-- @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
-- @copyright © 2013 MovLib
-- @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
-- @link https://movlib.org/
-- @since 0.0.1-dev
-- ---------------------------------------------------------------------------------------------------------------------

TRUNCATE TABLE `system_pages`;

-- Contact

INSERT INTO `system_pages` SET
  `dyn_titles` = COLUMN_CREATE(
    'en', 'Contact',
    'de', 'Kontakt'
  ),
  `dyn_texts`  = COLUMN_CREATE(
    'en', '&lt;p&gt;Thank you for your interest in contacting MovLib. Before proceeding, some important disclaimers:&lt;/p&gt;&lt;ul&gt;&lt;li&gt;MovLib has no central editorial board; contributions are made by a large number of volunteers at their own discretion. Edits are not the responsibility of MovLib (the organisation that hosts the site) nor of its staff.&lt;/li&gt;&lt;li&gt;If you have questions about the concept of MovLib rather than a specific problem, the &lt;a href=&quot;/about-movlib&quot;&gt;About MovLib&lt;/a&gt; page may help.&lt;/li&gt;&lt;/ul&gt;',
    'de', ''
  ),
  `presenter`  = 'Contact'
;

-- Imprint

INSERT INTO `system_pages` SET
  `dyn_titles` = COLUMN_CREATE(
    'en', 'Impressum',
    'de', 'Impressum'
  ),
  `dyn_texts`  = COLUMN_CREATE(
    'en', '&lt;p&gt;An &lt;strong&gt;Impressum&lt;/strong&gt; is a legally mandated statement of the ownership and authorship of a document, which must be included in websites published in Austria.&lt;/p&gt;&lt;div itemscope itemtype=&quot;http://schema.org/Organization&quot;&gt;&lt;table&gt;&lt;tr&gt;&lt;th class=&quot;s2 tar&quot;&gt;Association Name&lt;/th&gt;&lt;td itemprop=&quot;name legalName&quot;&gt;MovLib&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;th class=&quot;s2 tar&quot;&gt;Slogan&lt;/th&gt;&lt;td itemprop=&quot;description&quot;&gt;The free movie library.&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;th class=&quot;s2 tar&quot;&gt;Association Seat&lt;/th&gt;&lt;td&gt;Bad Vigaun&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;th class=&quot;s2 tar&quot;&gt;Postal Address&lt;/th&gt;&lt;td itemprop=&quot;address location&quot; itemscope itemtype=&quot;http://schema.org/PostalAddress&quot;&gt;&lt;span itemprop=&quot;streetAddress&quot;&gt;Langgasse 182 / 5&lt;/span&gt;&lt;br&gt; &lt;span itemprop=&quot;postalCode&quot;&gt;5424&lt;/span&gt; &lt;span itemprop=&quot;addressLocality&quot;&gt;Bad Vigaun&lt;/span&gt;&lt;br&gt; &lt;span itemprop=&quot;addressCountry&quot;&gt;Austria&lt;/span&gt;&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;th class=&quot;s2 tar&quot;&gt;Founding Date&lt;/th&gt;&lt;td&gt;&lt;time itemprop=&quot;foundingDate&quot; datetime=&quot;2013-07-23&quot;&gt;7-23-2013&lt;/time&gt;&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;th class=&quot;s2 tar&quot;&gt;Email&lt;/th&gt;&lt;td&gt;&lt;a href=&quot;mailto:webmaster@movlib.org&quot; itemprop=&quot;email&quot;&gt;webmaster@movlib.org&lt;/a&gt;&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;th class=&quot;s2 tar&quot;&gt;Jurisdiction&lt;/th&gt;&lt;td&gt;Bezirkshauptmannschaft Hallein&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;th class=&quot;s2 tar&quot;&gt;ZVR-Number&lt;/th&gt;&lt;td&gt;769030582&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;th class=&quot;s2 tar&quot;&gt;DVR&lt;/th&gt;&lt;td&gt;0085111&lt;/td&gt;&lt;/tr&gt;&lt;/table&gt;&lt;h2&gt;Information according to § 25 par. 2 MedienG&lt;/h2&gt;&lt;table&gt;&lt;tr&gt;&lt;th class=&quot;s2 tar&quot;&gt;Responsibility&lt;/th&gt;&lt;td&gt;MovLib&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;th class=&quot;s2 tar&quot;&gt;Purpose of the association&lt;/th&gt;&lt;td&gt;The purpose of this non-profit association is the development and operation of a free and open source online movie database that can be edited by anyone. Contributions are made by users from around the world.&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;th class=&quot;s2 tar&quot;&gt;Board of Directors&lt;/th&gt;&lt;td&gt;&lt;span itemprop=&quot;founder member&quot; itemscope itemtype=&quot;http://schema.org/Person&quot;&gt;&lt;a href=&quot;http://richard.fussenegger.info/&quot; itemprop=&quot;url sameAs&quot; target=&quot;_blank&quot;&gt;&lt;span itemprop=&quot;name&quot;&gt;&lt;span itemprop=&quot;givenName&quot;&gt;Richard&lt;/span&gt; &lt;span itemprop=&quot;familyName&quot;&gt;Fussenegger&lt;/span&gt;&lt;/span&gt;&lt;/a&gt;&lt;/span&gt;&lt;br&gt; &lt;span itemprop=&quot;founder member&quot; itemscope itemtype=&quot;http://schema.org/Person&quot;&gt;&lt;span itemprop=&quot;name&quot;&gt;&lt;span itemprop=&quot;givenName&quot;&gt;Markus&lt;/span&gt; &lt;span itemprop=&quot;familyName&quot;&gt;Deutschl&lt;/span&gt;&lt;/span&gt;&lt;/span&gt;&lt;/td&gt;&lt;/tr&gt;&lt;/table&gt;&lt;h2&gt;Information according to § 25 par. 4 MedienG&lt;/h2&gt;&lt;table&gt;&lt;tr&gt;&lt;th class=&quot;s2 tar&quot;&gt;Scope of this web site&lt;/th&gt;&lt;td&gt;&lt;a href=&quot;https://movlib.org/&quot; itemprop=&quot;url&quot;&gt;movlib.org&lt;/a&gt; is the official platform of “MovLib” for operating a free and open source online movie database that can be edited by anyone. The website is primarily meant as a free source of all information related to movies.&lt;/td&gt;&lt;/tr&gt;&lt;/table&gt;&lt;/div&gt;',
    'de', '&lt;p&gt;Ein &lt;strong&gt;Impressum&lt;/strong&gt; ist eine in Österreich gesetzlich vorgeschriebene Angabe zu den Eigentümern und Urhebern einer Website.&lt;/p&gt;&lt;div itemscope itemtype=&quot;http://schema.org/Organization&quot;&gt;&lt;table&gt;&lt;tr&gt;&lt;th class=&quot;s2 tar&quot;&gt;Vereinsname&lt;/th&gt;&lt;td itemprop=&quot;name legalName&quot;&gt;MovLib&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;th class=&quot;s2 tar&quot;&gt;Slogan&lt;/th&gt;&lt;td itemprop=&quot;description&quot;&gt;Die freie Kinemathek.&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;th class=&quot;s2 tar&quot;&gt;Vereinssitz&lt;/th&gt;&lt;td&gt;Bad Vigaun&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;th class=&quot;s2 tar&quot;&gt;Zustellanschrift&lt;/th&gt;&lt;td itemprop=&quot;address location&quot; itemscope itemtype=&quot;http://schema.org/PostalAddress&quot;&gt;&lt;span itemprop=&quot;streetAddress&quot;&gt;Langgasse 182 / 5&lt;/span&gt;&lt;br&gt; &lt;span itemprop=&quot;postalCode&quot;&gt;5424&lt;/span&gt; &lt;span itemprop=&quot;addressLocality&quot;&gt;Bad Vigaun&lt;/span&gt;&lt;br&gt; &lt;span itemprop=&quot;addressCountry&quot;&gt;Österreich&lt;/span&gt;&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;th class=&quot;s2 tar&quot;&gt;Entstehungsdatum&lt;/th&gt;&lt;td&gt;&lt;time itemprop=&quot;foundingDate&quot; datetime=&quot;2013-07-23&quot;&gt;23.07.2013&lt;/time&gt;&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;th class=&quot;s2 tar&quot;&gt;E-Mail&lt;/th&gt;&lt;td&gt;&lt;a href=&quot;mailto:webmaster@movlib.org&quot; itemprop=&quot;email&quot;&gt;webmaster@movlib.org&lt;/a&gt;&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;th class=&quot;s2 tar&quot;&gt;Zuständigkeit&lt;/th&gt;&lt;td&gt;Bezirkshauptmannschaft Hallein&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;th class=&quot;s2 tar&quot;&gt;ZVR-Zahl&lt;/th&gt;&lt;td&gt;769030582&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;th class=&quot;s2 tar&quot;&gt;DVR&lt;/th&gt;&lt;td&gt;0085111&lt;/td&gt;&lt;/tr&gt;&lt;/table&gt;&lt;h2&gt;Angaben nach § 25 Abs. 2 MedienG&lt;/h2&gt;&lt;table&gt;&lt;tr&gt;&lt;th class=&quot;s2 tar&quot;&gt;Medieninhaber und Herausgeber&lt;/th&gt;&lt;td&gt;MovLib&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;th class=&quot;s2 tar&quot;&gt;Vereinszweck&lt;/th&gt;&lt;td&gt;Der Verein, dessen Tätigkeit nicht auf Gewinn gerichtet ist, bezweckt die Entwicklung und den Betrieb einer freien und quelloffenen Online-Filmdatenbank, die von jeder/m editiert werden kann. Die Daten hierzu kommen von BenutzerInnen aus aller Welt.&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;th class=&quot;s2 tar&quot;&gt;Vorstand&lt;/th&gt;&lt;td&gt;&lt;span itemprop=&quot;founder member&quot; itemscope itemtype=&quot;http://schema.org/Person&quot;&gt;&lt;a href=&quot;http://richard.fussenegger.info/&quot; itemprop=&quot;url sameAs&quot; target=&quot;_blank&quot;&gt;&lt;span itemprop=&quot;name&quot;&gt;&lt;span itemprop=&quot;givenName&quot;&gt;Richard&lt;/span&gt; &lt;span itemprop=&quot;familyName&quot;&gt;Fussenegger&lt;/span&gt;&lt;/span&gt;&lt;/a&gt;&lt;/span&gt;&lt;br&gt; &lt;span itemprop=&quot;founder member&quot; itemscope itemtype=&quot;http://schema.org/Person&quot;&gt;&lt;span itemprop=&quot;name&quot;&gt;&lt;span itemprop=&quot;givenName&quot;&gt;Markus&lt;/span&gt; &lt;span itemprop=&quot;familyName&quot;&gt;Deutschl&lt;/span&gt;&lt;/span&gt;&lt;/span&gt;&lt;/td&gt;&lt;/tr&gt;&lt;/table&gt;&lt;h2&gt;Angaben nach § 25 Abs. 4 MedienG&lt;/h2&gt;&lt;table&gt;&lt;tr&gt;&lt;th class=&quot;s2 tar&quot;&gt;Grundlegende Richtung&lt;/th&gt;&lt;td&gt;&lt;a href=&quot;https://movlib.org/&quot; itemprop=&quot;url&quot;&gt;movlib.org&lt;/a&gt; ist die offizielle Plattform des Vereins „MovLib” zum Betrieb einer freien und quelloffenen Online-Filmdatenbank, die von jeder/m editiert werden kann. Die Website versteht sich vor allem als freie Quelle zu allen Informationen rund um das Thema Film.&lt;/td&gt;&lt;/tr&gt;&lt;/table&gt;&lt;/div&gt;'
  )
;

-- Privacy Policy

INSERT INTO `system_pages` SET
  `dyn_titles` = COLUMN_CREATE(
    'en', 'Privacy Policy',
    'de', 'Datenschutzerklärung'
  ),
  `dyn_texts`  = COLUMN_CREATE(
    'en', '',
    'de', ''
  )
;

-- Team

INSERT INTO `system_pages` SET
  `dyn_titles` = COLUMN_CREATE(
    'en', 'Team',
    'de', 'Team'
  ),
  `dyn_texts`  = COLUMN_CREATE(
    'en', '',
    'de', ''
  )
;

-- Terms of Use

INSERT INTO `system_pages` SET
  `dyn_titles` = COLUMN_CREATE(
    'en', 'Terms of Use',
    'de', 'Nutzungsbedingungen'
  ),
  `dyn_texts`  = COLUMN_CREATE(
    'en', '',
    'de', ''
  )
;

-- Association Statutes

INSERT INTO `system_pages` SET
  `dyn_titles` = COLUMN_CREATE(
    'en', 'Association Statutes',
    'de', 'Vereinsstatuten'
  ),
  `dyn_texts`  = COLUMN_CREATE(
    'en', '',
    'de', '&lt;h2&gt;§ 1: Name, Sitz und Tätigkeitsbereich&lt;/h2&gt;&lt;ol&gt;&lt;li&gt;Der Verein  führt den Namen „MovLib“.&lt;/li&gt;&lt;li&gt;Er hat seinen Sitz in Bad Vigaun und erstreckt seine Tätigkeit auf die ganze Welt.&lt;/li&gt;&lt;li&gt;Die Errichtung von Zweigvereinen ist nicht beabsichtigt.&lt;/li&gt;&lt;/ol&gt;&lt;h2&gt;§ 2: Zweck&lt;/h2&gt;&lt;p&gt;Der Verein, dessen Tätigkeit nicht auf Gewinn gerichtet ist, bezweckt die Entwicklung und den Betrieb einer freien und quelloffenen Online-Filmdatenbank, die von jeder/m editiert werden kann. Die Daten hierzu kommen von BenutzerInnen aus aller Welt.&lt;/p&gt;&lt;h2&gt;§ 3: Mittel zur Erreichung des Vereinszwecks&lt;/h2&gt;&lt;ol&gt;&lt;li&gt;Der Vereinszweck soll durch die in den Abs. 2 und 3 angeführten ideellen und materiellen Mittel erreicht werden.&lt;/li&gt;&lt;li&gt;Als ideelle Mittel dienen&lt;ol&gt;&lt;li&gt;Die Arbeit der ordentlichen Mitglieder&lt;/li&gt;&lt;li&gt;Gesellige Zusammenkünfte&lt;/li&gt;&lt;li&gt;Einrichtung einer Kinemathek&lt;/li&gt;&lt;/ol&gt;&lt;/li&gt;&lt;li&gt;Die erforderlichen materiellen Mittel sollen aufgebracht werden durch&lt;ol&gt;&lt;li&gt;Beitrittsgebühren und Mitgliedsbeiträge&lt;/li&gt;&lt;li&gt;Spenden&lt;/li&gt;&lt;li&gt;Förderungen&lt;/li&gt;&lt;li&gt;Sonstige Zuwendungen&lt;/li&gt;&lt;/ol&gt;&lt;/li&gt;&lt;/ol&gt;&lt;h2&gt;§ 4: Arten der Mitgliedschaft&lt;/h2&gt;&lt;ol&gt;&lt;li&gt;Die Mitglieder des Vereins gliedern sich in ordentliche, außerordentliche und Ehrenmitglieder.&lt;/li&gt;&lt;li&gt;Ordentliche Mitglieder sind jene, die sich voll an der Vereinsarbeit beteiligen. Außerordentliche Mitglieder sind solche, die die Vereinstätigkeit vor allem durch Zahlung eines erhöhten Mitgliedsbeitrags fördern. Ehrenmitglieder sind Personen, die hierzu wegen besonderer Verdienste um den Verein ernannt werden.&lt;/li&gt;&lt;/ol&gt;&lt;h2&gt;§ 5: Erwerb der Mitgliedschaft&lt;/h2&gt;&lt;ol&gt;&lt;li&gt;Mitglieder des Vereins können alle physischen Personen, die mündig und geschäftsfähig sind, sowie juristische Personen und rechtsfähige Personengesellschaften werden.&lt;/li&gt;&lt;li&gt;Über die Aufnahme von ordentlichen und außerordentlichen Mitgliedern entscheidet der Vorstand. Die Aufnahme kann ohne Angabe von Gründen verweigert werden.&lt;/li&gt;&lt;li&gt;Bis zur Entstehung des Vereins erfolgt die vorläufige Aufnahme von ordentlichen und außerordentlichen Mitgliedern durch die Vereinsgründer, im Fall eines bereits bestellten Vorstands durch diesen. Diese Mitgliedschaft wird erst mit Entstehung des Vereins wirksam. Wird ein Vorstand erst nach Entstehung des Vereins bestellt, erfolgt auch die (definitive) Aufnahme ordentlicher und außerordentlicher Mitglieder bis dahin durch die Gründer des Vereins.&lt;/li&gt;&lt;li&gt;Die Ernennung zum Ehrenmitglied erfolgt auf Antrag des Vorstands durch die Generalversammlung.&lt;/li&gt;&lt;/ol&gt;&lt;h2&gt;§ 6: Beendigung der Mitgliedschaft&lt;/h2&gt;&lt;ol&gt;&lt;li&gt;Die Mitgliedschaft erlischt durch Tod, bei juristischen Personen und rechtsfähigen Personengesellschaften durch Verlust der Rechtspersönlichkeit, durch freiwilligen Austritt und durch Ausschluss.&lt;/li&gt;&lt;li&gt;Der Austritt kann nur zum 1. eines jeden Monats erfolgen. Er muss dem Vorstand mindestens einen Monat vorher schriftlich mitgeteilt werden. Erfolgt die Anzeige verspätet, so ist sie erst zum nächsten Austrittstermin wirksam. Für die Rechtzeitigkeit ist das Datum der Postaufgabe maßgeblich.&lt;/li&gt;&lt;li&gt;Der Vorstand kann ein Mitglied ausschließen, wenn dieses trotz zweimaliger schriftlicher Mahnung unter Setzung einer angemessenen Nachfrist länger als sechs Monate mit der Zahlung der Mitgliedsbeiträge im Rückstand ist. Die Verpflichtung zur Zahlung der fällig gewordenen Mitgliedsbeiträge bleibt hiervon unberührt.&lt;/li&gt;&lt;li&gt;Der Ausschluss eines Mitglieds aus dem Verein kann vom Vorstand auch wegen grober Verletzung anderer Mitgliedspflichten und wegen unehrenhaften Verhaltens verfügt werden.&lt;/li&gt;&lt;li&gt;Die Aberkennung der Ehrenmitgliedschaft kann aus den im Abs. 4 genannten Gründen von der Generalversammlung über Antrag des Vorstands beschlossen werden.&lt;/li&gt;&lt;/ol&gt;&lt;h2&gt;§ 7: Rechte und Pflichten der Mitglieder&lt;/h2&gt;&lt;ol&gt;&lt;li&gt;Die Mitglieder sind berechtigt, an allen Veranstaltungen des Vereins teilzunehmen und die Einrichtungen des Vereins zu beanspruchen. Das Stimmrecht in der Generalversammlung sowie das aktive und passive Wahlrecht steht nur den ordentlichen und den Ehrenmitgliedern zu.&lt;/li&gt;&lt;li&gt;Jedes Mitglied ist berechtigt, vom Vorstand die Ausfolgung der Statuten zu verlangen.&lt;/li&gt;&lt;li&gt;Mindestens ein Zehntel der Mitglieder kann vom Vorstand die Einberufung einer Generalversammlung verlangen.&lt;/li&gt;&lt;li&gt;Die Mitglieder sind in jeder Generalversammlung vom Vorstand über die Tätigkeit und finanzielle Gebarung des Vereins zu informieren. Wenn mindestens ein Zehntel der Mitglieder dies unter Angabe von Gründen verlangt, hat der Vorstand den betreffenden Mitgliedern eine solche Information auch sonst binnen vier Wochen zu geben.&lt;/li&gt;&lt;li&gt;Die Mitglieder sind vom Vorstand über den geprüften Rechnungsabschluss (Rechnungslegung) zu informieren. Geschieht dies in der Generalversammlung, sind die Rechnungsprüfer einzubinden.&lt;/li&gt;&lt;li&gt;Die Mitglieder sind verpflichtet, die Interessen des Vereins nach Kräften zu fördern und alles zu unterlassen, wodurch das Ansehen und der Zweck des Vereins Abbruch erleiden könnte. Sie haben die Vereinsstatuten und die Beschlüsse der Vereinsorgane zu beachten. Die ordentlichen und außerordentlichen Mitglieder sind zur pünktlichen Zahlung der Beitrittsgebühr und der Mitgliedsbeiträge in der von der Generalversammlung beschlossenen Höhe verpflichtet.&lt;/li&gt;&lt;/ol&gt;&lt;h2&gt;§ 8: Vereinsorgane&lt;/h2&gt;&lt;p&gt;Organe des Vereins sind die Generalversammlung (§§ 9 und 10), der Vorstand (§§ 11 bis 13), die Rechnungsprüfer (§ 14) und das Schiedsgericht (§ 15).&lt;/p&gt;&lt;h2&gt;§ 9: Generalversammlung&lt;/h2&gt;&lt;ol&gt;&lt;li&gt;Die Generalversammlung ist die „Mitgliederversammlung“ im Sinne des Vereinsgesetzes 2002. Eine ordentliche Generalversammlung findet jährlich statt.&lt;/li&gt;&lt;li&gt;Eine außerordentliche Generalversammlung findet auf&lt;ol&gt;&lt;li&gt;Beschluss des Vorstands oder der ordentlichen Generalversammlung,&lt;/li&gt;&lt;li&gt;schriftlichen Antrag von mindestens einem Zehntel der Mitglieder,&lt;/li&gt;&lt;li&gt;Verlangen der Rechnungsprüfer (§ 21 Abs. 5 erster Satz VereinsG),&lt;/li&gt;&lt;li&gt;Beschluss der/eines Rechnungsprüfer/s (§ 21 Abs. 5 zweiter Satz VereinsG, § 11 Abs. 2 dritter Satz dieser Statuten),&lt;/li&gt;&lt;li&gt;Beschluss eines gerichtlich bestellten Kurators (§ 11 Abs. 2 letzter Satz dieser Statuten) binnen vier Wochen statt.&lt;/li&gt;&lt;/ol&gt;&lt;/li&gt;&lt;li&gt;Sowohl zu den ordentlichen wie auch zu den außerordentlichen Generalversammlungen sind alle Mitglieder mindestens zwei Wochen vor dem Termin schriftlich, mittels Telefax oder per E-Mail (an die vom Mitglied dem Verein bekanntgegebene Fax-Nummer oder E-Mail-Adresse) einzuladen. Die Anberaumung der Generalversammlung hat unter Angabe der Tagesordnung zu erfolgen. Die Einberufung erfolgt durch den Vorstand (Abs. 1 und Abs. 2 lit. 1 – 3), durch die/einen Rechnungsprüfer (Abs. 2 lit. 4) oder durch einen gerichtlich bestellten Kurator (Abs. 2 lit. 5).&lt;/li&gt;&lt;li&gt;Anträge zur Generalversammlung sind mindestens drei Tage vor dem Termin der Generalversammlung beim Vorstand schriftlich, mittels Telefax oder per E-Mail einzureichen.&lt;/li&gt;&lt;li&gt;Gültige Beschlüsse – ausgenommen solche über einen Antrag auf Einberufung einer außerordentlichen Generalversammlung – können nur zur Tagesordnung gefasst werden.&lt;/li&gt;&lt;li&gt;Bei der Generalversammlung sind alle Mitglieder teilnahmeberechtigt. Stimmberechtigt sind nur die ordentlichen und die Ehrenmitglieder. Jedes Mitglied hat eine Stimme. Die Übertragung des Stimmrechts auf ein anderes Mitglied im Wege einer schriftlichen Bevollmächtigung ist zulässig.&lt;/li&gt;&lt;li&gt;Die Generalversammlung ist ohne Rücksicht auf die Anzahl der Erschienenen beschlussfähig.&lt;/li&gt;&lt;li&gt;Die Wahlen und die Beschlussfassungen in der Generalversammlung erfolgen in der Regel mit einfacher Mehrheit der abgegebenen gültigen Stimmen. Beschlüsse, mit denen das Statut des Vereins geändert oder der Verein aufgelöst werden soll, bedürfen jedoch einer qualifizierten Mehrheit von zwei Dritteln der abgegebenen gültigen Stimmen.&lt;/li&gt;&lt;li&gt;Den Vorsitz in der Generalversammlung führt der/die Obmann/Obfrau, in dessen/deren Verhinderung sein/e/ihr/e Stellvertreter/in. Wenn auch diese/r verhindert ist, so führt das an Jahren älteste anwesende Vorstandsmitglied den Vorsitz.&lt;/li&gt;&lt;/ol&gt;&lt;h2&gt;§ 10: Aufgaben der Generalversammlung&lt;/h2&gt;&lt;p&gt;Der Generalversammlung sind folgende Aufgaben vorbehalten:&lt;/p&gt;&lt;ol&gt;&lt;li&gt;Beschlussfassung über den Voranschlag;&lt;/li&gt;&lt;li&gt;Entgegennahme und Genehmigung des Rechenschaftsberichts und des Rechnungsabschlusses unter Einbindung der Rechnungsprüfer;&lt;/li&gt;&lt;li&gt;Wahl und Enthebung der Mitglieder des Vorstands und der Rechnungsprüfer;&lt;/li&gt;&lt;li&gt;Genehmigung von Rechtsgeschäften zwischen Rechnungsprüfern und Verein;&lt;/li&gt;&lt;li&gt;Entlastung des Vorstands;&lt;/li&gt;&lt;li&gt;Festsetzung der Höhe der Beitrittsgebühr und der Mitgliedsbeiträge für ordentliche und für außerordentliche Mitglieder;&lt;/li&gt;&lt;li&gt;Verleihung und Aberkennung der Ehrenmitgliedschaft;&lt;/li&gt;&lt;li&gt;Beschlussfassung über Statutenänderungen und die freiwillige Auflösung des Vereins;&lt;/li&gt;&lt;li&gt;Beratung und Beschlussfassung über sonstige auf der Tagesordnung stehende Fragen.&lt;/li&gt;&lt;/ol&gt;&lt;h2&gt;§ 11: Vorstand&lt;/h2&gt;&lt;ol&gt;&lt;li&gt;Der Vorstand besteht aus zwei Mitgliedern, und zwar aus Obmann/Obfrau und Stellvertreter/in.&lt;/li&gt;&lt;li&gt;Der Vorstand wird von der Generalversammlung gewählt. Der Vorstand hat bei Ausscheiden eines gewählten Mitglieds das Recht, an seine Stelle ein anderes wählbares Mitglied zu kooptieren, wozu die nachträgliche Genehmigung in der nächstfolgenden Generalversammlung einzuholen ist. Fällt der Vorstand ohne Selbstergänzung durch Kooptierung überhaupt oder auf unvorhersehbar lange Zeit aus, so ist jeder Rechnungsprüfer verpflichtet, unverzüglich eine außerordentliche Generalversammlung zum Zweck der Neuwahl eines Vorstands einzuberufen. Sollten auch die Rechnungsprüfer handlungsunfähig sein, hat jedes ordentliche Mitglied, das die Notsituation erkennt, unverzüglich die Bestellung eines Kurators beim zuständigen Gericht zu beantragen, der umgehend eine außerordentliche Generalversammlung einzuberufen hat.&lt;/li&gt;&lt;li&gt;Die Funktionsperiode des Vorstands beträgt vier Jahre; Wiederwahl ist möglich. Jede Funktion im Vorstand ist persönlich auszuüben.&lt;/li&gt;&lt;li&gt;Der Vorstand wird vom Obmann/von der Obfrau, bei Verhinderung von seinem/seiner/ihrem/ihrer Stellvertreter/in, schriftlich oder mündlich einberufen. Ist auch diese/r auf unvorhersehbar lange Zeit verhindert, darf jedes sonstige Vorstandsmitglied den Vorstand einberufen.&lt;/li&gt;&lt;li&gt;Der Vorstand ist beschlussfähig, wenn alle seine Mitglieder eingeladen wurden und mindestens die Hälfte von ihnen anwesend ist.&lt;/li&gt;&lt;li&gt;Der Vorstand fasst seine Beschlüsse mit einfacher Stimmenmehrheit; bei Stimmengleichheit gibt die Stimme des/der Vorsitzenden den Ausschlag.&lt;/li&gt;&lt;li&gt;Den Vorsitz führt der/die Obmann/Obfrau, bei Verhinderung sein/e/ihr/e Stellvertreter/in. Ist auch diese/r verhindert, obliegt der Vorsitz dem an Jahren ältesten anwesenden Vorstandsmitglied oder jenem Vorstandsmitglied, das die übrigen Vorstandsmitglieder mehrheitlich dazu bestimmen.&lt;/li&gt;&lt;li&gt;Außer durch den Tod und Ablauf der Funktionsperiode (Abs. 3) erlischt die Funktion eines Vorstandsmitglieds durch Enthebung (Abs. 9) und Rücktritt (Abs. 10).&lt;/li&gt;&lt;li&gt;Die Generalversammlung kann jederzeit den gesamten Vorstand oder einzelne seiner Mitglieder entheben. Die Enthebung tritt mit Bestellung des neuen Vorstands bzw Vorstandsmitglieds in Kraft.&lt;/li&gt;&lt;li&gt;Die Vorstandsmitglieder können jederzeit schriftlich ihren Rücktritt erklären. Die Rücktrittserklärung ist an den Vorstand, im Falle des Rücktritts des gesamten Vorstands an die Generalversammlung zu richten. Der Rücktritt wird erst mit Wahl bzw. Kooptierung (Abs. 2) eines Nachfolgers wirksam.&lt;/li&gt;&lt;/ol&gt;&lt;h2&gt;§ 12: Aufgaben des Vorstands&lt;/h2&gt;&lt;p&gt;Dem Vorstand obliegt die Leitung des Vereins. Er ist das „Leitungsorgan“ im Sinne des Vereinsgesetzes 2002. Ihm kommen alle Aufgaben zu, die nicht durch die Statuten einem anderen Vereinsorgan zugewiesen sind. In seinen Wirkungsbereich fallen insbesondere folgende Angelegenheiten:&lt;/p&gt;&lt;ol&gt;&lt;li&gt;Einrichtung eines den Anforderungen des Vereins entsprechenden Rechnungswesens mit laufender Aufzeichnung der Einnahmen/Ausgaben und Führung eines Vermögensverzeichnisses als Mindesterfordernis;&lt;/li&gt;&lt;li&gt;Erstellung des Jahresvoranschlags, des Rechenschaftsberichts und des Rechnungsabschlusses;&lt;/li&gt;&lt;li&gt;Vorbereitung und Einberufung der Generalversammlung in den Fällen des § 9 Abs. 1 und Abs. 2 lit. 1 – 3 dieser Statuten;&lt;/li&gt;&lt;li&gt;Information der Vereinsmitglieder über die Vereinstätigkeit, die Vereinsgebarung und den geprüften Rechnungsabschluss;&lt;/li&gt;&lt;li&gt;Verwaltung des Vereinsvermögens;&lt;/li&gt;&lt;li&gt;Aufnahme und Ausschluss von ordentlichen und außerordentlichen Vereinsmitgliedern;&lt;/li&gt;&lt;li&gt;Aufnahme und Kündigung von Angestellten des Vereins.&lt;/li&gt;&lt;/ol&gt;&lt;h2&gt;§ 13: Besondere Obliegenheiten einzelner Vorstandsmitglieder&lt;/h2&gt;&lt;ol&gt;&lt;li&gt;Der/die Obmann/Obfrau führt die laufenden Geschäfte des Vereins. Der/die Schriftführer/in unterstützt den/die Obmann/Obfrau bei der Führung der Vereinsgeschäfte.&lt;/li&gt;&lt;li&gt;Der/die Obmann/Obfrau vertritt den Verein nach außen. Schriftliche Ausfertigungen des Vereins bedürfen zu ihrer Gültigkeit der Unterschriften des/der Obmanns/Obfrau und des Schriftführers/der Schriftführerin, in Geldangelegenheiten (vermögenswerte Dispositionen) des/der Obmanns/Obfrau und des Kassiers/der Kassierin. Rechtsgeschäfte zwischen Vorstandsmitgliedern und Verein bedürfen der Zustimmung eines anderen Vorstandsmitglieds.&lt;/li&gt;&lt;li&gt;Rechtsgeschäftliche Bevollmächtigungen, den Verein nach außen zu vertreten bzw. für ihn zu zeichnen, können ausschließlich von den in Abs. 2 genannten Vorstandsmitgliedern erteilt werden.&lt;/li&gt;&lt;li&gt;Bei Gefahr im Verzug ist der/die Obmann/Obfrau berechtigt, auch in Angelegenheiten, die in den Wirkungsbereich der Generalversammlung oder des Vorstands fallen, unter eigener Verantwortung selbständig Anordnungen zu treffen; im Innenverhältnis bedürfen diese jedoch der nachträglichen Genehmigung durch das zuständige Vereinsorgan.&lt;/li&gt;&lt;li&gt;Der/die Obmann/Obfrau führt den Vorsitz in der Generalversammlung und im Vorstand.&lt;/li&gt;&lt;li&gt;Der/die Schriftführer/in führt die Protokolle der Generalversammlung und des Vorstands.&lt;/li&gt;&lt;li&gt;Der/die Kassier/in ist für die ordnungsgemäße Geldgebarung des Vereins verantwortlich.&lt;/li&gt;&lt;li&gt;Im Fall der Verhinderung treten an die Stelle des/der Obmanns/Obfrau, des Schriftführers/der Schriftführerin oder des Kassiers/der Kassierin ihre Stellvertreter/innen.&lt;/li&gt;&lt;/ol&gt;&lt;h2&gt;§ 14: Rechnungsprüfer&lt;/h2&gt;&lt;ol&gt;&lt;li&gt;Zwei Rechnungsprüfer werden von der Generalversammlung auf die Dauer von vier Jahren gewählt. Wiederwahl ist möglich. Die Rechnungsprüfer dürfen keinem Organ – mit Ausnahme der Generalversammlung – angehören, dessen Tätigkeit Gegenstand der Prüfung ist.&lt;/li&gt;&lt;li&gt;Den Rechnungsprüfern obliegt die laufende Geschäftskontrolle sowie die Prüfung der Finanzgebarung des Vereins im Hinblick auf die Ordnungsmäßigkeit der Rechnungslegung und die statutengemäße Verwendung der Mittel. Der Vorstand hat den Rechnungsprüfern die erforderlichen Unterlagen vorzulegen und die erforderlichen Auskünfte zu erteilen. Die Rechnungsprüfer haben dem Vorstand über das Ergebnis der Prüfung zu berichten.&lt;/li&gt;&lt;li&gt;Rechtsgeschäfte zwischen Rechnungsprüfern und Verein bedürfen der Genehmigung durch die Generalversammlung. Im Übrigen gelten für die Rechnungsprüfer die Bestimmungen des § 11 Abs. 8 bis 10 sinngemäß.&lt;/li&gt;&lt;/ol&gt;&lt;h2&gt;§ 15: Schiedsgericht&lt;/h2&gt;&lt;ol&gt;&lt;li&gt;Zur Schlichtung von allen aus dem Vereinsverhältnis entstehenden Streitigkeiten ist das vereinsinterne Schiedsgericht berufen. Es ist eine „Schlichtungseinrichtung“ im Sinne des Vereinsgesetzes 2002 und kein Schiedsgericht nach den §§ 577 ff ZPO.&lt;/li&gt;&lt;li&gt;Das Schiedsgericht setzt sich aus drei ordentlichen Vereinsmitgliedern zusammen. Es wird derart gebildet, dass ein Streitteil dem Vorstand ein Mitglied als Schiedsrichter schriftlich namhaft macht. Über Aufforderung durch den Vorstand binnen sieben Tagen macht der andere Streitteil innerhalb von 14 Tagen seinerseits ein Mitglied des Schiedsgerichts namhaft. Nach Verständigung durch den Vorstand innerhalb von sieben Tagen wählen die namhaft gemachten Schiedsrichter binnen weiterer 14 Tage ein drittes ordentliches Mitglied zum/zur Vorsitzenden des Schiedsgerichts. Bei Stimmengleichheit entscheidet unter den Vorgeschlagenen das Los. Die Mitglieder des Schiedsgerichts dürfen keinem Organ – mit Ausnahme der Generalversammlung – angehören, dessen Tätigkeit Gegenstand der Streitigkeit ist.&lt;/li&gt;&lt;li&gt;Das Schiedsgericht fällt seine Entscheidung nach Gewährung beiderseitigen Gehörs bei Anwesenheit aller seiner Mitglieder mit einfacher Stimmenmehrheit. Es entscheidet nach bestem Wissen und Gewissen. Seine Entscheidungen sind vereinsintern endgültig.&lt;/li&gt;&lt;/ol&gt;&lt;h2&gt;§ 16: Freiwillige Auflösung des Vereins&lt;/h2&gt;&lt;ol&gt;&lt;li&gt;Die freiwillige Auflösung des Vereins kann nur in einer Generalversammlung und nur mit Zweidrittelmehrheit der abgegebenen gültigen Stimmen beschlossen werden.&lt;/li&gt;&lt;li&gt;Diese Generalversammlung hat auch – sofern Vereinsvermögen vorhanden ist – über die Abwicklung zu beschließen. Insbesondere hat sie einen Abwickler zu berufen und Beschluss darüber zu fassen, wem dieser das nach Abdeckung der Passiven verbleibende Vereinsvermögen zu übertragen hat. Dieses Vermögen soll, soweit dies möglich und erlaubt ist, einer Organisation zufallen, die gleiche oder ähnliche Zwecke wie dieser Verein verfolgt, sonst Zwecken der Sozialhilfe.&lt;/li&gt;&lt;/ol&gt;'
  )
;

-- About Us

INSERT INTO `system_pages` SET
  `dyn_titles` = COLUMN_CREATE(
    'en', 'About MovLib',
    'de', 'Über MovLib'
  ),
  `dyn_texts`  = COLUMN_CREATE(
    'en', '',
    'de', ''
  )
;
