<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link https://movlib.org/ MovLib}.
 *
 * MovLib is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public
 * License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
 * version.
 *
 * MovLib is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License along with MovLib.
 * If not, see {@link http://www.gnu.org/licenses/ gnu.org/licenses}.
 */

/**
 * These are translations for the user interface and not meant for HTTP headers!
 *
  // The list of codes is complete according to the Hypertext Transfer Protocol (HTTP) Status Code Registry (last
  // updated 2012-02-13). Unless otherwise noted, the status code is defined in RFC7231.
 *
 * @link http://www.iana.org/assignments/http-status-codes/
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
// @codeCoverageIgnoreStart
return [
  100 => "Weiter",
  101 => "Wechsle Protokolle",
  102 => "Verarbeite", // RFC2518
  200 => "OK",
  201 => "Erstellt",
  202 => "Akzeptiert",
  203 => "Nicht Autorisierende Information",
  204 => "Kein Inhalt",
  205 => "Inhalt Zurückgesetzt",
  206 => "Unvollständiger Inhalt",
  207 => "Multi-Status", // RFC4918
  208 => "Bereits Berichtet", // RFC5842
  226 => "IM verwendet", // RFC3229
  300 => "Multiple Auswahl",
  301 => "Permanent Verschoben",
  302 => "Gefunden",
  303 => "Siehe Woanders",
  304 => "Nicht Modifiziert",
  305 => "Verwende Proxy",
  306 => "Reserviert",
  307 => "Temporäre Umleitung",
  308 => "Permanente Umleitung", // RFC-reschke-http-status-308-07
  400 => "Fehlerhafte Anfrage",
  401 => "Unbefugt",
  402 => "Zahlung erforderlich",
  403 => "Verboten",
  404 => "Nicht gefunden",
  405 => "Methode nicht erlaubt",
  406 => "Nicht akzeptabel",
  407 => "Proxyauthentifikation erforderlich",
  408 => "Anfragenzeitüberschreitung",
  409 => "Konflikt",
  410 => "Gegangen",
  411 => "Länge erforderlich",
  412 => "Vorbedingungen fehlgeschlagen",
  413 => "Angeforderte Entität zu groß",
  414 => "Anforderungs-URI zu lang",
  415 => "Nicht unterstützter Medientyp",
  416 => "Anforderungsbereich nicht erfüllbar",
  417 => "Erwartung fehlgeschlagen",
  418 => "Ich bin eine Teekanne", // RFC2324
  422 => "Nicht verarbeitbare Entität", // RFC4918
  423 => "Gesperrt", // RFC4918
  424 => "Fehlgeschlagene Abhängigkeit", // RFC4918
  425 => "Reserviert für WebDAV advanced collections expired Vorschlag", // RFC2817
  426 => "Aktualisierung erforderlich", // RFC2817
  428 => "Vorbedingungen erforderlich", // RFC6585
  429 => "Zu viele Anforderungen", // RFC6585
  431 => "Anforderungsüberschriftenfeld zu groß", // RFC6585
  500 => "Interner Serverfehler",
  501 => "Nicht implementiert",
  502 => "Beschädigtes Gateway",
  503 => "Service nicht verfügbar",
  504 => "Gatewayzeitüberschreitung",
  505 => "HTTP-Version nicht unterstützt",
  506 => "Variant Also Negotiates (Experimental)", // RFC2295
  507 => "Ungenügend Speicher", // RFC4918
  508 => "Schleife entdeckt", // RFC5842
  510 => "Nicht erweitert", // RFC2774
  511 => "Netzwerkauthentifikation erforderlich", // RFC6585
];
// @codeCoverageIgnoreEnd
