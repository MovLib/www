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
-- Styles seed data.
--
-- @author Richard Fussenegger <richard@fussenegger.info>
-- @copyright © 2013 MovLib
-- @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
-- @link https://movlib.org/
-- @since 0.0.1-dev
-- ---------------------------------------------------------------------------------------------------------------------

INSERT INTO `genres` (`dyn_names`, `dyn_descriptions`) VALUES
(
  COLUMN_CREATE(
    'en', 'Action',
    'de', 'Action'
  ),
  COLUMN_CREATE(
    'en', '&lt;p&gt;Action film is a film genre in which one or more heroes are thrust into a series of challenges that typically include physical feats, extended fight scenes, violence, and frenetic chases. Action films tend to feature a resourceful character struggling against incredible odds, including life-threatening situations, a villain, or a pursuit, which generally conclude in victory for the hero.&lt;/p&gt;',
    'de', '&lt;p&gt;Der Actionfilm (von engl. action: Tat, Handlung, Bewegung) ist ein Filmgenre des Unterhaltungskinos, in welchem der Fortgang der äußeren Handlung von zumeist spektakulär inszenierten Kampf- und Gewaltszenen vorangetrieben und illustriert wird. Hauptbestandteile von Actionfilmen sind daher meist aufwendig gedrehte Stunts, Schlägereien, Schießereien, Explosionen und Verfolgungsjagden.&lt;/p&gt;'
  )
),
(
  COLUMN_CREATE(
    'en', 'Adventure',
    'de', 'Abenteuer'
  ),
  COLUMN_CREATE(
    'en', '&lt;p&gt;Adventure films are a genre of film. Unlike action films, they often use their action scenes preferably to display and explore exotic locations in an energetic way.&lt;/p&gt;&lt;p&gt;The subgenres of adventure films include, swashbuckler film, disaster films, and historical dramas - which is similar to the epic film genre. Main plot elements include quests for lost continents, a jungle and/or desert settings, characters going on a treasure hunts and heroic journeys for the unknown. Adventure films are mostly set in a period background and may include adapted stories of historical or fictional adventure heroes within the historical context. Kings, battles, rebellion or piracy are commonly seen in adventure films. Adventure films may also be combined with other movie genres such as, science fiction, fantasy and sometimes war films.&lt;/p&gt;',
    'de', '&lt;p&gt;Als Abenteuerfilm bezeichnet man einen Film, in dem die Protagonisten in eine ereignisreiche Handlung, mitunter mit vielen Schauplatzwechseln, verstrickt sind. In der Regel sind die Erzählstränge auf eine Ebene reduziert, um dem Zuschauer die Identifikation mit der Hauptrolle zu vereinfachen. Im Vordergrund steht nicht die Entwicklung der Charaktere an sich, sondern die diese Entwicklung hervorrufenden Ereignisse. Mit Motiven wie dem Kampf des Helden gegen das Böse oder für die Liebe einer Frau, verbunden mit oft exotischen Schauplätzen, appelliert der Abenteuerfilm an die eskapistischen Bedürfnisse des Zuschauers und verfolgt als reines Illusionsprodukt weniger den Anspruch, realistisch zu sein.&lt;/p&gt;'
  )
),
(
  COLUMN_CREATE(
    'en', 'Animation',
    'de', 'Animation'
  ),
  COLUMN_CREATE(
    'en', '&lt;p&gt;Animation is the rapid display of a sequence of images to create an illusion of movement. The most common method of presenting animation is as a motion picture or video program, although there are other methods. This type of presentation is usually accomplished with a camera and a projector or a computer viewing screen which can rapidly cycle through images in a sequence. Animation can be made with either hand rendered art, computer generated imagery, or three-dimensional objects, e.g., puppets or clay figures, or a combination of techniques. The position of each object in any particular image relates to the position of that object in the previous and following images so that the objects each appear to fluidly move independently of one another. The viewing device displays these images in rapid succession, usually 24, 25, or 30 frames per second.&lt;/p&gt;',
    'de', '&lt;p&gt;Animation (von lat. animare, „zum Leben erwecken“; animus, „Geist, Seele“) ist im engeren Sinne jede Technik, bei der durch das Erstellen und Anzeigen von Einzelbildern für den Betrachter ein bewegtes Bild geschaffen wird. Ein Animationsfilm entsteht, wenn ein unbelebter und unbeweglicher Gegenstand mittels der Einzelbildschaltung zu scheinbarer Bewegung gebracht wird.&lt;/p&gt;&lt;p&gt;In der Anfangszeit des Animationsfilms wurden die Objekte für jedes Einzelbild in eine neue Lage gebracht, die fotografiert wird, so dass in der Projektion eine Scheinbewegung entsteht.&lt;/p&gt;&lt;p&gt;1906 gilt als Geburtsjahr des animierten Filmes.&lt;/p&gt;'
  )
),
(
  COLUMN_CREATE(
    'en', 'Biography',
    'de', 'Biografie'
  ),
  COLUMN_CREATE(
    'en', '&lt;p&gt;A biographical film, or biopic (/ˈbaɪɵpɪk/; abbreviation for biographical motion picture), is a film that dramatizes the life of an actual person or people. Such films show the life of a historical person and the central character’s real name is used. They differ from films “based on a true story” or “historical films” in that they attempt to comprehensively tell a person’s life story or at least the most historically important years of their lives.&lt;/p&gt;',
    'de', '&lt;p&gt;Eine Filmbiografie, auch Biopic (vom engl. biographical und engl. motion picture), bezeichnet einen Film, der in fiktionalisierter Form das Leben einer geschichtlich belegbaren Figur erzählt. Das Biopic ist eines der ältesten Filmgenres. Der Begriff entstand 1951 und wurde zum ersten Mal im US-Fachblatt Variety verwendet. In einem Biopic muss nicht die Lebensgeschichte einer realen Person von der Geburt bis zum Tod erzählt werden, es genügt vielmehr, dass ein oder mehrere Lebensabschnitte zu einem filmischen Ganzen dramaturgisch verknüpft werden. Ein zentrales Kriterium des Biopics ist die Nennung des Namens der realen Person. Meistens wird im Biopic vorausgesetzt, dass die dargestellte Person gesellschaftliche Relevanz besitzt.&lt;/p&gt;'
  )
),
(
  COLUMN_CREATE(
    'en', 'Comedy',
    'de', 'Komödie'
  ),
  COLUMN_CREATE(
    'en', '&lt;p&gt;Comedy is a genre of film in which the main emphasis is on humour. These films are designed to entertain the audience through amusement, and often work by exaggerating characteristics of real life for humorous effect.&lt;/p&gt;&lt;p&gt;Films in this style traditionally have a happy ending (the black comedy being an exception). One of the oldest genres in film, some of the very first silent movies were comedies, as slapstick comedy often relies on visual depictions, without requiring sound. Comedy, unlike other film genres, puts much more focus on individual stars, with many former stand-up comics transitioning to the film industry due to their popularity. While many comic films are lighthearted stories with no intent other than to amuse, others contain political or social commentary (such as Wag the Dog and Man of the Year).&lt;/p&gt;',
    'de', '&lt;p&gt;Filmkomödie bezeichnet ein Filmgenre, bei dem der Zuschauer zum Lachen bewegt werden soll. Das Genre hat zahlreiche Subgenres, zum Beispiel die Schwarze Komödie, die Actionkomödie, die Horrorkomödie, die Kriminalkomödie, die Musikkomödie, die Liebeskomödie, die Screwball-Komödie, die Slapstick-Komödie, die Verwechslungskomödie oder die Tragikomödie.&lt;/p&gt;'
  )
),
(
  COLUMN_CREATE(
    'en', 'Crime',
    'de', 'Krimi'
  ),
  COLUMN_CREATE(
    'en', '&lt;p&gt;Crime films are films which focus on the lives of criminals. The stylistic approach to a crime film varies from realistic portrayals of real-life criminal figures, to the far-fetched evil doings of imaginary arch-villains. Criminal acts are almost always glorified in these movies.&lt;/p&gt;',
    'de', '&lt;p&gt;Der Kriminalfilm, kurz auch Krimi, ist ein übergeordnetes Filmgenre mit einer Vielzahl von Subgenres, deren Gemeinsamkeit in der zentralen Rolle eines Verbrechens liegt. Charakteristisch sind Täter (Gangsterfilm), Polizei (Polizeifilm) oder Justiz (Gerichtsfilm) als Identifikationsfiguren bzw. die Tat an sich als Handlungsmittelpunkt. Neben dem Fokus auf die Figurenkonstellation lassen sich Kriminalfilme hinsichtlich der Art und Weise ihrer Inszenierung kategorisieren, etwa pessimistisch (Film noir), parodistisch (Kriminalkomödie) oder mitreißend (Thriller). Ein wiederkehrendes Element des Kriminalfilms ist das Konzept des Whodunit (deutsch: „Wer ist es gewesen?“).&lt;/p&gt;'
  )
),
(
  COLUMN_CREATE(
    'en', 'Documentary',
    'de', 'Dokumentation'
  ),
  COLUMN_CREATE(
    'en', '&lt;p&gt;Documentary films constitute a broad category of nonfictional motion pictures intended to document some aspect of reality, primarily for the purposes of instruction or maintaining a historical record.&lt;/p&gt;',
    'de', '&lt;p&gt;Der Dokumentarfilm ist eine Filmgattung, die sich mit tatsächlichem Geschehen befasst. Im Gegensatz zum Spielfilm geschieht dies in der Regel ohne bezahlte Darsteller.&lt;/p&gt;&lt;p&gt;Es gibt eine große Bandbreite von verschiedenen Dokumentarfilmarten, die sich vom Versuch, ein möglichst reines Dokument zu erschaffen, über die Doku-Soap bis hin zum Doku-Drama erstreckt. Ein weiterer Schritt ist das Nachspielen von Szenen, die so hätten stattfinden können, oder zum Teil auch so stattgefunden haben (Reenactment).&lt;/p&gt;'
  )
),
(
  COLUMN_CREATE(
    'en', 'Drama',
    'de', 'Drama'
  ),
  COLUMN_CREATE(
    'en', '&lt;p&gt;A drama film is a film genre that depends mostly on in-depth development of realistic characters dealing with emotional themes. Dramatic themes such as alcoholism, drug addiction, infidelity, moral dilemmas, racial prejudice, religious intolerance, sexuality, poverty, class divisions, violence against women and corruption put the characters in conflict with themselves, others, society and even natural phenomena. Drama is the most broad of movies genres and includes subgenres as romantic drama, sport films, period drama, courtroom drama and crime.&lt;/p&gt;',
    'de', '&lt;p&gt;Das Drama ist nach antiker Definition eine Gattung der Dichtung und bezeichnet eine Handlung mit verteilten Rollen.&lt;/p&gt;&lt;p&gt;Andere Definitionen des Filmdramas im Deutschen sind stärker eingegrenzt, so im Deutschen Fremdwörterbuch: „Bald besann sich die Filmkunst darauf, die dem deutschen Wesen so sehr anhaftende Charaktereigenschaft der Sentimentalität für sich in vorteilhafter Weise wirtschaftlich auszunutzen. Dieser Erwägung verdankt das Filmdrama seine Entstehung.“&lt;/p&gt;&lt;p&gt;Für das Lexikon der Filmbegriffe der Universität Kiel wird der Begriff Drama in der Filmkritik „als Sammelbezeichnung für Filme verwendet, die zwischen Melo- und Sozialdrama angesiedelt sind. Die somit wohl unspezifischste Genrebezeichnung des Films umfasst neben anderen Subgenres das romantische Drama, den period film, zahlreiche historische Melodramen, viele Gerichtsfilme, manche Abenteuerdramen und ähnliches mehr. Im Zentrum des Dramas stehen Figuren, die eine Lebenskrise durchmachen, vor eine lebensverändernde Entscheidung gestellt sind, ihr Leben auf Grund von Verlust, Verfolgung, zufälligem Glück oder ähnlichem neu formieren müssen.“&lt;/p&gt;'
  )
),
(
  COLUMN_CREATE(
    'en', 'Children',
    'de', 'Kinder'
  ),
  COLUMN_CREATE(
    'en', '&lt;p&gt;A children’s film (or family film) is a film genre that contains children or relates to them in the context of home and family. Children’s films refer to films that are made specifically for children and not necessarily for the general audience while family films are made for a wider appeal with a general audience in mind. Children’s films come in several major forms like realism, fantasy, animation, war, musicals, and literary adaptations.&lt;/p&gt;',
    'de', '&lt;p&gt;Kinderfilme sind fürs Fernsehen, Kino oder für die DVD- bzw. Videoauswertung produzierte Filme, die sich in erster Linie an Kinder richten. In thematischer und stilistischer Hinsicht gibt es kaum Beschränkungen, ihre Präsentation passt sich jedoch den Ansprüchen und Bedürfnissen der Zielgruppe an.&lt;/p&gt;&lt;p&gt;Filme, die speziell für Kinder produziert werden, handeln oft von jungen Menschen. Mehr noch als Filme für Erwachsene benötigen Kinder Identifikationsfiguren, die ihnen gleichaltrige Figuren bieten. Sind die Hauptfiguren jedoch Erwachsene, dann eher in Märchen wie Drei Haselnüsse für Aschenbrödel (1973), oder Verwünscht (2007).&lt;/p&gt;'
  )
),
(
  COLUMN_CREATE(
    'en', 'Fantasy',
    'de', 'Fantasy'
  ),
  COLUMN_CREATE(
    'en', '&lt;p&gt;Fantasy films are films with fantastic themes, usually involving magic, supernatural events, make-believe creatures, or exotic fantasy worlds. The genre is considered to be distinct from science fiction film and horror film, although the genres do overlap. Fantasy films often have an element of magic, myth, wonder, escapism, and the extraordinary.&lt;/p&gt;',
    'de', '<p>Das Filmgenre des Phantastischen Films, aus dem englischen auch Fantasyfilm, umfasst im weitesten Sinne sämtliche Filme, deren Handlung Elemente enthält, welche nur in der menschlichen Fantasie existieren und in der Realität eigentlich so nicht vorstellbar sind. Eng verwandt ist mit ihm der Märchenfilm, die meisten Märchenfilme sind gleichzeitig Fantasyfilme, aber nicht alle Fantasyfilme sind Märchenfilme. Grundsätzlich lassen sich verschiedene Arten von Fantasyfilm unterscheiden.</p>'
  )
),
(
  COLUMN_CREATE(
    'en', 'Film Noir',
    'de', 'Film-Noir'
  ),
  COLUMN_CREATE(
    'en', '&lt;p&gt;Film noir is a cinematic term used primarily to describe stylish crime dramas, particularly those that emphasize cynical attitudes and sexual motivations. Hollywood’s classical film noir period is generally regarded as extending from the early 1940s to the late 1950s. Film noir of this era is associated with a low-key black-and-white visual style that has roots in German Expressionist cinematography. Many of the prototypical stories and much of the attitude of classic noir derive from the hardboiled school of crime fiction that emerged in the United States during the Great Depression.&lt;/p&gt;',
    'de', '&lt;p&gt;Film noir [filmˈnwaʀ] (französisch für „schwarzer Film“) ist ein Terminus aus dem Bereich der Filmkritik. Ursprünglich wurde mit diesem Begriff eine Reihe von zynischen, durch eine pessimistische Weltsicht gekennzeichneten US-amerikanischen Kriminalfilmen der 1940er und 1950er Jahre klassifiziert, die im deutschen Sprachraum auch unter dem Begriff „Schwarze Serie“ zusammengefasst werden. Üblicherweise wird Die Spur des Falken von 1941 als erster und Im Zeichen des Bösen von 1958 als letzter Vertreter dieser klassischen Ära angesehen.&lt;/p&gt;&lt;p&gt;Die Wurzeln des Film noir liegen in erster Linie im deutschen expressionistischen Stummfilm und der US-amerikanischen Hardboiled-Kriminalliteratur der 1920er und 1930er Jahre. Dementsprechend sind die Filme der klassischen Ära üblicherweise durch eine von starken Hell-Dunkel-Kontrasten dominierte Bildgestaltung, entfremdete oder verbitterte Protagonisten sowie urbane Schauplätze gekennzeichnet.&lt;/p&gt;&lt;p&gt;Stil und Inhalte des Film noir fanden auch nach 1958 Verwendung. Diese später produzierten Filme mit Charakteristika der klassischen Ära werden häufig als „Neo-Noir“ bezeichnet. Die Verwendungsbeschränkung des Begriffs Film noir auf Filme US-amerikanischer Herkunft wurde zunehmend aufgegeben, so dass das Produktionsland für die Einordnung heutzutage oft keine Rolle mehr spielt.&lt;/p&gt;'
  )
),
(
  COLUMN_CREATE(
    'en', 'History',
    'de', 'Historie'
  ),
  COLUMN_CREATE(
    'en', '&lt;p&gt;The historical drama is a film genre in which stories are based upon historical events and famous people. Some historical dramas are docudramas, which attempt an accurate portrayal of a historical event or biography, to the degree that the available historical research will allow. Other historical dramas are fictionalized tales that are based on an actual person and their deeds, such as Braveheart, which is loosely based on the 13th century knight William Wallace’s fight for Scotland’s independence.&lt;/p&gt;',
    'de', '&lt;p&gt;Historienfilme sind Spielfilme, deren Inhalt auf historischen Figuren, Ereignissen oder Bewegungen basiert. Fiktive Filmerzählungen, deren Handlungen an einem historischen Schauplatz angesiedelt sind, werden ebenfalls als Historienfilme bezeichnet.&lt;/p&gt;'
  )
),
(
  COLUMN_CREATE(
    'en', 'Horror',
    'de', 'Horror'
  ),
  COLUMN_CREATE(
    'en', '&lt;p&gt;Horror is a film genre seeking to elicit a negative emotional reaction from viewers by playing on the audience’s primal fears. Horror films often feature scenes that startle the viewer; the macabre and the supernatural are frequent themes. Thus they may overlap with the fantasy, supernatural, and thriller genres.&lt;/p&gt;',
    'de', '&lt;p&gt;Der Horrorfilm ist ein Filmgenre, das beim Zuschauer Gefühle der Angst, des Schreckens und Verstörung auszulösen versucht. Oftmals, jedoch nicht zwangsläufig, treten dabei übernatürliche Akteure oder Phänomene auf, von denen eine zumeist lebensbedrohliche und traumatische Wirkung auf die Protagonisten ausgeht. Die deutsche Bezeichnung Gruselfilm wird tendenziell eher für ältere Horrorfilme verwendet.&lt;/p&gt;'
  )
),
(
  COLUMN_CREATE(
    'en', 'Musical',
    'de', 'Musik'
  ),
  COLUMN_CREATE(
    'en', '&lt;p&gt;The musical film is a film genre in which songs sung by the characters are interwoven into the narrative, sometimes accompanied by dancing. The songs usually advance the plot or develop the film’s characters, though in some cases they serve merely as breaks in the storyline, often as elaborate “production numbers”.&lt;/p&gt;',
    'de', '&lt;p&gt;Musikfilm ist ein Film, der von vielen musikalischen Darbietungen geprägt ist. Die verwendeten Musiknummern sind dabei - im Gegensatz zu „normalen Filmen“ - integraler Handlungsbestandteil.&lt;/p&gt;'
  )
),
(
  COLUMN_CREATE(
    'en', 'Mystery',
    'de', 'Mystery'
  ),
  COLUMN_CREATE(
    'en', '&lt;p&gt;Mystery film is a sub-genre of the more general category of crime film and at times the thriller genre. It focuses on the efforts of the detective, private investigator or amateur sleuth to solve the mysterious circumstances of a crime by means of clues, investigation, and clever deduction.&lt;/p&gt;',
    'de', '&lt;p&gt;Mystery (von engl. mystery für „Geheimnis“, „Rätsel“) ist im Deutschen die ursprünglich englische Bezeichnung für ein Genre in der Trivialliteratur, das sich am besten als eine Mischung aus Horror- und Fantasy-Elementen fassen lässt; seltener kommen auch Bezüge zur Science Fiction vor (z. B. Dark City von Alex Proyas, 1998). Als Auslöser für die gegenwärtige Mysterywelle werden oft die Fernsehserie Akte X – Die unheimlichen Fälle des FBI (1993–2002), die Fernsehserie Lost (2004–2010) und der Film The Sixth Sense (1999) von M. Night Shyamalan genannt.&lt;/p&gt;'
  )
),
(
  COLUMN_CREATE(
    'en', 'Romance',
    'de', 'Liebe'
  ),
  COLUMN_CREATE(
    'en', '&lt;p&gt;Romance films (or romance movies) are romantic love stories recorded in visual media for broadcast in theaters and on television that focus on passion, emotion, and the affectionate romantic involvement of the main characters and the journey that their genuinely strong, true and pure romantic love takes them through dating, courtship or marriage. Romance films make the romantic love story or the search for strong and pure love and romance the main plot focus. Occasionally, romance lovers face obstacles such as finances, physical illness, various forms of discrimination, psychological restraints or family that threaten to break their union of love. As in all quite strong, deep, and close romantic relationships, tensions of day-to-day life, temptations (of infidelity), and differences in compatibility enter into the plots of romantic films.&lt;/p&gt;',
    'de', '&lt;p&gt;Ein Liebesfilm ist ein Film, dessen Thema die Liebe zwischen zwei Menschen ist. Erfüllt sich diese Liebe in einem Happy End, stehen die romantischen Aspekte der Geschichte im Vordergrund. Bleibt die Liebe unerfüllt, hat der Liebesfilm einen melodramatischen Charakter.&lt;/p&gt;'
  )
),
(
  COLUMN_CREATE(
    'en', 'Science Fiction',
    'de', 'Science-Fiction'
  ),
  COLUMN_CREATE(
    'en', '&lt;p&gt;Science fiction film is a film genre that uses science fiction: speculative, science-based depictions of phenomena that are not necessarily accepted by mainstream science, such as extraterrestrial life forms, alien worlds, extrasensory perception, and time travel, often along with futuristic elements such as spacecraft, robots, cyborgs, interstellar space travel or other technologies. Science fiction films have often been used to focus on political or social issues, and to explore philosophical issues like the human condition. In many cases, tropes derived from written science fiction may be used by filmmakers ignorant of or at best indifferent to the standards of scientific plausibility and plot logic to which written science fiction is traditionally held.&lt;/p&gt;',
    'de', '&lt;p&gt;Science-Fiction ist ein Filmgenre, dem Filme zugeordnet werden, die sich mit fiktionalen Techniken sowie wissenschaftlichen Leistungen und deren möglichen Auswirkungen auf die Zukunft beschäftigen.&lt;/p&gt;'
  )
),
(
  COLUMN_CREATE(
    'en', 'Short',
    'de', 'Kurz'
  ),
  COLUMN_CREATE(
    'en', '&lt;p&gt;A short film is any film not long enough to be considered a feature film. No consensus exists as to where that boundary is drawn: the Academy of Motion Picture Arts and Sciences defines a short film as &quot;an original motion picture that has a running time of 40 minutes or less, including all credits&quot;. The term featurette originally applied to a film longer than a short subject, but shorter than a standard feature film.&lt;/p&gt;',
    'de', '&lt;p&gt;Ein Kurzfilm (englisch short (film) oder short subject) definiert sich als Gegenstück zum Langfilm ausschließlich über seine Länge. Ein Film, der bis zu 30 Minuten lang ist, kann als Kurzfilm gelten, wobei der Begriff an sich erst um 1915 herum generiert wurde. Ein Kurzfilm kann also ebenso wie der programmfüllende Spielfilm sämtliche Filmgenres bedienen.&lt;/p&gt;'
  )
),
(
  COLUMN_CREATE(
    'en', 'Silent',
    'de', 'Stumm'
  ),
  COLUMN_CREATE(
    'en', '&lt;p&gt;A silent film is a film with no synchronized recorded sound, especially with no spoken dialogue. In silent films for entertainment the dialogue is transmitted through muted gestures, mime (US: pantomime) and title cards. The idea of combining motion pictures with recorded sound is nearly as old as film itself, but because of the technical challenges involved, synchronized dialogue was only made practical in the late 1920s with the perfection of the Audion amplifier tube and the introduction of the Vitaphone system. After the release of The Jazz Singer in 1927, &quot;talkies&quot; became more and more commonplace. Within a decade, popular widespread production of silent films had ceased.&lt;/p&gt;',
    'de', '&lt;p&gt;Als Stummfilm wird seit der Verbreitung des Tonfilms in den 1920er-Jahren ein Film ohne technisch-mechanisch vorbereitete Tonbegleitung bezeichnet. Die Aufführung solcher Filme wurde zeitgenössisch fast ausnahmslos wenigstens musikalisch untermalt. Der Stummfilm entstand gegen Ende des 19. Jahrhunderts in Westeuropa und in den Vereinigten Staaten von Amerika. Grundlage für die Herstellung und Wiedergabe der ersten Stummfilme waren Erfindungen im Bereich der Technik und der Fotografie (siehe den Artikel zur Filmgeschichte).&lt;/p&gt;&lt;p&gt;Während der Frühzeit des Kinos gab es noch keine zufriedenstellende Möglichkeit, Bild und Ton synchron aufzunehmen und abzuspielen. Die Filme wurden vor Publikum je nach Art der Vorführstätte von Orchester, Klavier bzw. Pianola, Grammophon u. a. begleitet. Stummfilme wurden auch mit einmontierten Texten, den Zwischentiteln, erzählt. Oft begleitete auch ein Filmerzähler oder -erklärer die Vorstellung. Trotzdem musste der Großteil der Handlung und Gefühle über die Filmbilder transportiert werden. Das Schauspiel der Akteure früher Filme war aus diesem Grund meistens sehr körperbetont. Gestik und Mimik der Schauspieler vor allem in Dramen wirken vom heutigen Blickpunkt aus oft übertrieben.&lt;/p&gt;&lt;p&gt;Die Wirkung des Stummfilms liegt darin, dass er universell verständlich ist. Die Sprache der Schauspieler spielt keine Rolle, da sie nicht zu hören sind und Zwischentitel mit geringem Aufwand in andere Sprachen übersetzt werden können. Besonders in den USA war diese universelle Verständlichkeit ausschlaggebend, da dort sehr viele Einwanderer lebten, die des Englischen nicht mächtig waren.&lt;/p&gt;'
  )
),
(
  COLUMN_CREATE(
    'en', 'Sport',
    'de', 'Sport'
  ),
  COLUMN_CREATE(
    'en', '&lt;p&gt;A Sport Film revolves around a sport setting, event, or an athlete. Often, these films will center on a single sporting event that carries significant importance. Sports films traditionally have a simple plot that builds up to the significant sporting event. This genre is known for incorporating film techniques to build anticipation and intensity. Sport films have a large range of sub-genres, from comedies to dramas, and are more likely than other genres to be based true-life events.&lt;/p&gt;',
    'de', '&lt;p&gt;Ein Sportfilm ist ein Film, in dem Sportler, Sportarten oder sportliche Ereignisse im Mittelpunkt der Handlung stehen.&lt;/p&gt;&lt;p&gt;„Sportfilme im eigentlichen Sinne sind Filme, die durch den besonders gestalteten sportbezogenen Inhalt wesentlich geprägt sind. In einem weiteren unspezifischen Sinne können auch solche Filme als Sportfilme bezeichnet werden, die in einem gewissen Grade Themen aus dem Sport aufgreifen, die jedoch für den Film nicht bestimmend sind.“&lt;/p&gt;&lt;p&gt;Die Definition lässt erahnen, dass Elemente des Sports gerne zusätzlich in die Dramaturgie von Filmen eingebaut werden, ohne dass diese Elemente für die Handlung eines Films eine wesentliche Bedeutung erlangen.&lt;/p&gt;'
  )
),
(
  COLUMN_CREATE(
    'en', 'Thriller',
    'de', 'Thriller'
  ),
  COLUMN_CREATE(
    'en', '&lt;p&gt;Thriller is a broad genre of literature, film, and television programming that uses suspense, tension and excitement as the main elements. Thrillers heavily stimulate the viewer’s moods giving them a high level of anticipation, ultra-heightened expectation, uncertainty, surprise, anxiety and/or terror. Thriller films tend to be adrenaline-rushing, gritty, rousing and fast-paced.&lt;/p&gt;',
    'de', '&lt;p&gt;Der Thriller ist sowohl ein Roman- als auch ein Filmgenre mit verschiedenen, sich teilweise überlappenden, Subgenres. Charakteristisch für Thriller ist das Erzeugen eines Thrills, einer Spannung, die nicht nur in kurzen Passagen, sondern während des gesamten Handlungsverlaufs präsent ist, ein beständiges Spiel zwischen Anspannung und Erleichterung. Häufig anzutreffen sind weitläufige Spannungsbögen, Cliffhanger und Red Herrings.&lt;/p&gt;'
  )
),
(
  COLUMN_CREATE(
    'en', 'War',
    'de', 'Krieg'
  ),
  COLUMN_CREATE(
    'en', '&lt;p&gt;War films are a film genre concerned with warfare, usually about naval, air or land battles, sometimes focusing instead on prisoners of war, covert operations, military training or other related subjects. At times war films focus on daily military or civilian life in wartime without depicting battles. Their stories may be fiction, based on history, docudrama, biographical, or even alternate history fiction. The term anti-war film is sometimes used to describe films which bring to the viewer the pain and horror of war, often from a political or ideological perspective.&lt;/p&gt;',
    'de', '&lt;p&gt;Der Kriegsfilm als Filmgenre umfasst diejenigen Spielfilme, also Kino- oder Fernsehfilme, in denen die kriegerischen Auseinandersetzungen den Hintergrund für die handelnden Personen abgeben und deren Handlungsstränge ganz oder zum großen Teil in einem Kriegsszenario verlaufen.&lt;/p&gt;'
  )
),
(
  COLUMN_CREATE(
    'en', 'Western',
    'de', 'Western'
  ),
  COLUMN_CREATE(
    'en', '&lt;p&gt;The Western is a genre of various arts, such as film, television, radio, literature, painting and others. Westerns are devoted to telling stories set primarily in the latter half of the 19th century in the American Old West, hence the name. Some Westerns are set as early as the Battle of the Alamo in 1836. There are also a number of films about Western-type characters in contemporary settings, such as Junior Bonner set in the 1970s and The Three Burials of Melquiades Estrada in the 21st century.&lt;/p&gt;',
    'de', '&lt;p&gt;Der Western ist ein Kino-Genre, in dessen Mittelpunkt der zentrale US-amerikanische Mythos der Eroberung des (wilden) Westens der Vereinigten Staaten im neunzehnten Jahrhundert steht. Entsprechende Werke der Literatur werden meist als Trivialliteratur gewertet. Wesentliche Merkmale sind Handlungsort und Zeit: der westliche Teil des nordamerikanischen Kontinents während seiner Besiedlung durch die von Osten kommenden Siedler.&lt;/p&gt;'
  )
),
(
  COLUMN_CREATE(
    'en', 'Erotic',
    'de', 'Erotik'
  ),
  COLUMN_CREATE(
    'en', '&lt;p&gt;Sex in film refers to the presentation in motion pictures of sexuality or eroticism and sex acts, including love scenes.&lt;/p&gt;&lt;p&gt;
Erotic sex scenes have been presented in films since the silent era of cinematography. Many actors and actresses have exposed at least parts of their bodies or dressed and behaved in ways considered sexually provocative by contemporary standards at some point in their careers. Some films containing sex scenes have been criticized by religious groups or banned by governments, or both.&lt;/p&gt;&lt;p&gt;Sex scenes have been presented in many genres of film; while in some genres sexuality is rarely depicted.&lt;/p&gt;',
    'de', '&lt;p&gt;Als Erotikfilm oder Softporno werden Spiel- oder Fernsehfilme bezeichnet, die hauptsächlich erotische Inhalte zeigen. Üblicherweise in Spielfilm-Länge ist ihre Handlung von periodischen Darstellungen simulierten, nicht explizit gezeigten Geschlechtsverkehrs durchsetzt. Das Softcore-Genre wird oft als Middlebrow, also für den Ottonormalverbraucher zugängliche Kunst, bezeichnet.&lt;/p&gt;'
  )
)
;
