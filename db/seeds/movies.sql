USE `movlib`;
-- Roundhay Garden Scene (The world's first movie)
BEGIN;
INSERT INTO `movies` (`year`, `runtime`, `original_title`, `dyn_synopses`, `created`, `deleted`) VALUES (
  '1888',
  1,
  'Roundhay Garden Scene',
  COLUMN_CREATE('en', 'This is the first movie ever.'),
  '2013-06-01 00:00:00',
  TRUE
);
INSERT INTO `movies_countries` (`movie_id`, `country_id`) VALUES (1, 77);
INSERT INTO `movies_genres` (`movie_id`, `genre_id`) VALUES (1, 7), (1, 18), (1, 19);
INSERT INTO `movies_languages` (`movie_id`, `language_id`) VALUES (1, 185);
COMMIT;

-- The Shawshank Redemption
BEGIN;
INSERT INTO `movies` (`year`, `runtime`, `original_title`, `dyn_synopses`, `created`) VALUES (
  '1994',
  142,
  'The Shawshank Redemption',
  COLUMN_CREATE('en',
    'In 1947, banker Andy Dufresne (Tim Robbins) is convicted of murdering his wife and her lover, based on circumstantial evidence, and is sentenced to two consecutive life sentences at Shawshank State Penitentiary. Andy quickly befriends contraband smuggler Ellis "Red" Redding (Morgan Freeman), an inmate serving a life sentence. Red procures a rock hammer for Andy, allowing him to create small stone chess pieces. Red later gets him a large poster of Rita Hayworth, followed in later years by images of Marilyn Monroe and Raquel Welch. Andy works in the prison laundry, but is regularly assaulted by the "bull queer" gang "the Sisters" and their leader Bogs (Mark Rolston).

In 1949, Andy overhears the brutal chief guard Byron Hadley (Clancy Brown) complaining about taxes on a forthcoming inheritance and informs him about a financial loophole. After another vicious assault by the Sisters nearly kills Andy, Hadley severely beats Bogs resulting in Bogs being sent to another prison. Andy is not attacked again. Warden Samuel Norton (Bob Gunton) meets with Andy and reassigns him to the prison library to assist elderly inmate Brooks Hatlen (James Whitmore), a pretext for Andy to manage financial duties for the prison. His advice and expertise are soon sought by other guards at Shawshank and from nearby prisons. Andy begins writing weekly letters to the state government for funds to improve the decrepit library.

In 1954, Brooks is freed on parole but, unable to adjust to the outside world after 50 years in prison, he hangs himself. Andy receives a library donation that includes a recording of The Marriage of Figaro. He plays an excerpt over the public address system, resulting in his receiving solitary confinement. After his release, Andy explains that he holds onto hope as something that the prison cannot take from him, but Red dismisses the idea. In 1963, Norton begins exploiting prison labor for public works, profiting by undercutting skilled labor costs and receiving kickbacks. He has Andy launder the money using the alias "Randall Stephens".

In 1965, Tommy Williams (Gil Bellows) is incarcerated for burglary. He joins Andy&apos;s and Red&apos;s circle of friends, and Andy helps him pass his General Educational Development (G.E.D.) examinations. In 1966, after hearing the details of Andy&apos;s case, Tommy reveals that an inmate at another prison claimed responsibility for an identical murder, suggesting Andy&apos;s innocence. Andy approaches Norton with this information, but the warden refuses to listen. Norton places Andy in solitary confinement and has Hadley murder Tommy, under the guise of an escape attempt. Andy refuses to continue with the scam, but Norton threatens to destroy the library and take away his protection and preferential treatment. After Andy is released from solitary confinement, he tells Red of his dream of living in Zihuatanejo, a Mexican Pacific coastal town. While Red shrugs it off as being unrealistic, Andy instructs him, should he ever be freed, to visit a specific hayfield near Buxton to retrieve a package.

The next day at roll call, upon finding Andy&apos;s cell empty, an irate Norton throws one of Andy&apos;s rocks at the poster of Raquel Welch hanging on the wall. The rock tears through the poster, revealing a tunnel that Andy had dug with his rock hammer over the previous two decades. The previous night, Andy escaped through the tunnel and the prison&apos;s sewage pipe with Norton&apos;s ledger, containing details of the money laundering. While guards search for him the following morning, Andy, posing as Randall Stephens, visits several banks to withdraw the laundered money. Finally, he sends the ledger and evidence of the corruption and murders at Shawshank to a local newspaper. The police arrive at Shawshank and take Hadley into custody, while Norton commits suicide to avoid arrest.

After serving 40 years, Red receives parole. He struggles to adapt to life outside prison and fears he never will. Remembering his promise to Andy, he visits Buxton and finds a cache containing money and a letter asking him to come to Zihuatanejo. Red violates his parole and travels to Fort Hancock, Texas to cross the border to Mexico, admitting he finally feels hope. On a beach in Zihuatanejo, he finds Andy, and the two friends are happily reunited.',
    'de',
    'Die Handlung beginnt 1947, als der Bankmanager Andy Dufresne anhand von Indizien wegen Mordes an seiner Frau und deren Liebhaber zu zweimal lebenslanger Haft verurteilt wird. Diese Strafe soll er in dem gefürchteten Gefängnis von Shawshank in Maine absitzen. Von Beginn an passt er überhaupt nicht in die Szenerie von Strafgefangenen und das Leben wird ihm durch Vergewaltigungen und Übergriffe durch Mithäftlinge sowie durch die strengen Aufseher erschwert.

Im Gefängnis lernt er mit der Zeit einige Mithäftlinge besser kennen – unter ihnen Red, der wegen Mordes schon zwanzig Jahre im Gefängnis sitzt und bei den Insassen den Ruf genießt, dass er für einen entsprechenden Gegenwert alles Mögliche besorgen kann. Andy ist Hobby-Geologe und bittet Red darum, ihm einen Geologenhammer (Steinhammer) zu besorgen, bald darauf auch noch um ein Poster von Rita Hayworth.

Nach anfänglichen Schwierigkeiten steigt Andy stetig im Ansehen der Mithäftlinge und Aufseher. Während Andy und einige Mithäftlinge das Dach der Schilderfabrik teeren, erzählt der leitende Aufseher Byron Hadley seinen Kollegen von einer eben gemachten Erbschaft und echauffiert sich hörbar über die Steuerbelastung, welche auf ihn zukommen wird. Dies veranlasst Andy dazu, dem Aufseher zu erklären, dass er mit einer Überschreibung der Erbschaft auf dessen Ehefrau die ganze Summe behalten kann. Er bietet ihm an, die notwendigen Urkunden und Formulare für ihn auszufüllen und erbittet als Gegenleistung nur je drei Bier für sich und seine Kumpels. Hadley nimmt das Angebot an. Andy kommt für einen Monat auf die Krankenstation, nachdem er von Mithäftlingen zusammengeschlagen wird, die ihn ein weiteres Mal vergewaltigen wollten. Hadley rächt Andy, indem er den Rädelsführer brutal zusammenschlägt, der daraufhin gelähmt ist.

Später wird Andy von der Wäscherei in die Gefängnisbibliothek versetzt, wo er dem Gefängnisbibliothekar Brooks Hatlen unter die Arme greifen soll. Die Aufseher suchen Andy von diesem Zeitpunkt an immer wieder auf, um sich in finanziellen Angelegenheiten beraten zu lassen. So kommt es, dass er für die Aufseher die Steuererklärungen macht. Zunächst für jene im eigenen Gefängnis, ein Jahr darauf auch für die der umliegenden Anstalten. Außerdem gibt er Unterricht mit Abschlussprüfung zur Erlangung des GED (amerikanische Hochschulreife auf dem 2. Bildungsweg).

Brooks Hatlen wird plötzlich nach über fünfzig Jahren aus dem Gefängnis entlassen und bekommt eine Stelle in einem Supermarkt. Da er sich nach einem halben Jahrhundert im Gefängnis in der Freiheit nicht mehr zurechtfindet und den Druck der Einsamkeit nicht aushält, begeht er Selbstmord, wovon seine ehemaligen Mithäftlinge in einem Abschiedsbrief erfahren.

Andy wird zunehmend in die illegalen finanziellen Machenschaften des Direktors Norton hineingezogen und wäscht für ihn in großem Umfang Bestechungsgelder. Durch den Bericht von Tommy Williams, einem Neuzugang, über einen Täter namens Elmo Blatch, der ein ähnliches Verbrechen, wie Andy es begangen haben sollte, ihm in einer anderen Strafanstalt mit dem Hinweis gestanden hat, der Ehemann der Frau, ein Banker, sei dafür verurteilt worden, stellt sich heraus, dass Andy tatsächlich unschuldig ist und durch Tommys Aussagen für ihn auch die vage Möglichkeit bestehe, freizukommen. Andy versucht mit dem Versprechen, über die illegalen Geschäfte totales Stillschweigen zu bewahren, den Direktor dazu zu bewegen, seinen Prozess neu aufzurollen. Dieser weigert sich jedoch, weil er Andy und seine Geheimnisse im Gefängnis behalten und seinen guten Buchhalter nicht verlieren will. Dadurch kommt es zum verbalen Streit mit dem Direktor, woraufhin Andy erst einen, dann einen weiteren Monat Einzelhaft in einer fensterlosen Zelle verbringen muss. In der Zwischenzeit lässt der Direktor den möglichen Entlastungszeugen Tommy von Hadley unter Vortäuschung eines Fluchtversuches erschießen, um Andy jede Chance auf eine Entlassung zu nehmen.

Als Andy aus der Einzelhaft entlassen wird, wirkt er gebrochen. Er spricht mit Red über seine Träume, einen kleinen Ort in Mexiko am Pazifik, Zihuatanejo, an dem er sich ein altes Boot kaufen möchte, um dieses zu renovieren, von einem kleinen Hotel, das er betreiben wolle. Er nimmt Red das Versprechen ab – falls dieser je freikommen sollte – nach Buxton in Maine zu fahren, um dort in einer Steinmauer an einem Baum nach einem bestimmten schwarzen Stein zu suchen, unter dem er etwas finden wird.

Red befürchtet, Andy könnte wie Brooks Selbstmord begehen. Aber am nächsten Morgen ist Andy spurlos aus seiner Zelle verschwunden. Der Direktor tobt, niemand, auch nicht Red, weiß eine Antwort. Vor Wut wirft Norton Steine aus Andys Sammlung durch die Zelle. Einer davon durchschlägt ein Poster, das Raquel Welch abbildet, worauf ein mehrfaches Aufschlagen des Steines zu hören ist. Völlig überrascht reißt Norton das Poster weg und entdeckt dahinter einen Tunnel. Andy hatte mit seinem kleinen Geologenhammer 19 Jahre lang heimlich an einem, über die Jahre von verschiedenen Postern mit einer berühmten Schauspielerin verdeckten, runden Gang gegraben, der von seiner Zelle aus zwischen zwei Gefängnisgebäuden endet, von welcher Stelle aus ein 500 Meter langes, dickes Abwasserrohr aus Steinzeug zu einem nahen Fluss und in die Freiheit führt. Den Schutt hatte Andy durch die Hosenbeine bei Spaziergängen auf dem Gefängnishof unbemerkt verteilt. Während eines Gewitters beschließt er den Ausbruch, kriecht durch den engen Tunnel und schlägt das bewusste Rohr mit einem schweren Stein ungehört ein, durch das er mit seinen wasserdicht verpackten Utensilien nach draußen gelangt. Während die Polizei erfolglos nach ihm sucht, hebt er tadellos gekleidet unter der erfundenen Strohmann-Identität des Konteninhabers die reingewaschenen Bestechungsgelder des Direktors von diversen Banken ab und macht sich in einem roten Cabrio auf den Weg nach Mexiko. Vor seiner Abreise lässt er aber noch Informationen über die Delikte von Norton und Hadley verschicken. Als die Behörden im Gefängnis ankommen, um Byron Hadley und Direktor Norton zu verhaften, erschießt sich dieser im eigenen Büro. Vorher muss er erkennen, dass Andy seine blankgeputzten Schuhe und alle Unterlagen unbemerkt mitgenommen hatte. Stattdessen findet er Andys alte Schuhe und im Tresor die Bibel aus Andys Zelle mit einer Widmung und deren im Umriss des Geologenhammers ausgeschnittenen Seiten, die als Versteck für das Werkzeug dienten.

Der Poststempel auf einer Karte ohne Text sagt Red wenige Tage später, dass Andy über Fort Hancock, Texas, die Staaten verlassen hat. Red kommt kurze Zeit später auf Bewährung frei und erhält wie Brooks eine Stelle im Supermarkt. Er übernachtet auch im selben Zimmer der Pension, in der sich Brooks erhängte. Auch Red kommt anfangs nicht mit der neu gewonnenen Freiheit und Einsamkeit zurecht. Er denkt darüber nach, sich zu erschießen oder eine Straftat zu begehen, um so wieder ins Gefängnis zu kommen. Doch eines hält ihn immer noch am Leben: Das Versprechen, diesen bestimmten Baum in Buxton an einer Feldsteinmauer zu finden und dort nach einem schwarzen Stein zu suchen, den es sonst dort nirgends gibt. So macht sich Red per Anhalter auf den Weg, um diese Stelle aufzusuchen. Er wird unter besagtem schwarzen Stein fündig, in einer Blechdose sind Geld und ein Brief versteckt, in dem Andy ihn einlädt, ihn in Mexiko aufzusuchen und ihm dabei zu helfen, sich eine neue, gemeinsame Existenz aufzubauen. Red kehrt in sein Zimmer zurück, ritzt in den Deckenbalken, in den Brooks „BROOKS WAS HERE“ (Brooks war hier) eingeschnitzt hatte, den Zusatz „SO WAS RED“ (Red auch) und machte sich auf die Reise, in der Hoffnung, nicht sofort von der Polizei wegen Verstoßes gegen die Bewährungsauflagen gesucht zu werden.

Am Ende treffen sich Andy und Red am Pazifikstrand in Mexiko.'
  ),
'2013-06-01 00:05:00'
);
INSERT INTO `movies_titles` (`movie_id`, `language_id`, `title`, `dyn_comments`, `is_display_title`) VALUES (2, 52, 'Die Verurteilten', '', true);
INSERT INTO `movies_countries` (`movie_id`, `country_id`) VALUES (2, 233);
INSERT INTO `movies_genres` (`movie_id`, `genre_id`) VALUES (2, 6), (2, 8);
INSERT INTO `movies_directors` (`movie_id`, `person_id`) VALUES (2, 5); -- Frank Darabont
INSERT INTO `movies_cast` (`movie_id`, `person_id`, `roles`) VALUES
  (2, 6, 'Andy Dufresne'),                      -- Tim Robbins
  (2, 7, 'Ellis Boyd &quot;Red&quot; Redding')  -- Morgan Freeman
;
INSERT INTO `movies_awards` (`award_count`, `award_id`, `award_category_id`, `movie_id`, `person_id`, `year`, `won`) VALUES
  (1, 5, 1, 2, 7, 1995, false),       -- Oscar (Best Actor in a Leading Role - Morgan Freeman)
  (2, 5, 2, 2, NULL, 1995, false),    -- Oscar (Best Cinematography)
  (3, 5, 3, 2, NULL, 1995, false),    -- Oscar (Best Film Editing)
  (4, 5, 4, 2, NULL, 1995, false),    -- Oscar (Best Music, Original Score)
  (5, 5, 5, 2, NULL, 1995, false),    -- Oscar (Best Picture)
  (6, 5, 6, 2, NULL, 1995, false),    -- Oscar (Best Sound)
  (7, 5, 7, 2, 5, 1995, false),       -- Oscar (Best Writing (Adapted Screenplay) - Frank Darabont)
  (8, 6, 1, 2, NULL, 1995, false),    -- Oscar (Best Action/Adventure/Thriller Film)
  (9, 6, 2, 2, 5, 1995, false),       -- Oscar (Best Writing - Frank Darabont)
  (10, 7, 1, 2, NULL, 1995, false),   -- Eddie (Best Edited Feature Film)
  (11, 8, 1, 2, NULL, 1995, true),    -- ASC Award (Outstanding Achievement in Cinematography in Theatrical Releases)
  (12, 1, 1, 2, NULL, 1996, true),    -- Japanese Academy Award (Best Foreign Film)
  (13, 9, 1, 2, NULL, 1995, true),    -- Plus Camerimage (Bronze Frog)
  (14, 9, 3, 2, NULL, 1995, false),   -- Plus Camerimage (Golden Frog)
  (15, 10, 1, 2, NULL, 1995, false),  -- Artios Award (Best Casting for Feature Film, Drama)
  (16, 11, 1, 2, NULL, 1995, false),  -- CFCA Award (Best Picture)
  (17, 11, 2, 2, 7, 1995, false),     -- CFCA Award (Best Supporting Actor - Morgan Freeman)
  (18, 12, 1, 2, 7, 1995, true),      -- Chlotrudis Award (Best Actor - Morgan Freeman)
  (19, 12, 1, 2, 6, 1995, false),     -- Chlotrudis Award (Best Actor - Tim Robbins)
  (20, 13, 1, 2, 7, 1995, false),     -- DFWFCA Award (Best Actor - Morgan Freeman)
  (21, 13, 2, 2, NULL, 1995, false),  -- DFWFCA Award (Best Picture)
  (22, 14, 1, 2, 5, 1995, false),     -- DGA Award (Outstanding Directorial Achievement in Motion Pictures)
  (23, 15, 1, 2, 7, 1995, false),     -- Golden Globe (Best Actor - Motion Picture Drama - Morgan Freeman)
  (24, 15, 2, 2, 5, 1995, false),     -- Golden Globe (Best Screenplay - Motion Picture - Frank Darabont)
  (25, 16, 1, 2, NULL, 1995, false),  -- Grammy (Best Instrumental Composition Written for a Motion Picture or for Television)
  (26, 17, NULL, 2, 5, 1995, true),   -- Studio Crystal Heart Award (Frank Darabont)
  (27, 18, 1, 2, 5, 1995, true),      -- Hochi Film Award (Best International Picture - Frank Darabont)
  (28, 19, 1, 2, 5, 1995, true),      -- Humanitas Prize (Film - Frank Darabont)
  (29, 20, 1, 2, 5, 1996, true),      -- Kinema Junpo Award (Best Foreign Language Film - Frank Darabont)
  (30, 20, 2, 2, 5, 1996, true),      -- Kinema Junpo Award (Reader's Choice - Frank Darabont)
  (31, 21, 1, 2, 5, 1996, true),      -- Manichi Film Concours (Best Foreign Language Film - Frank Darabont)
  (32, 22, 1, 2, NULL, 1994, true),   -- NBR Award (Top Ten Films)
  (33, 23, 1, 2, 5, 1995, true),      -- PEN Center USA West Literary Award (Screenplay - Frank Darabont)
  (34, 24, 1, 2, 7, 1995, false),     -- Screen Actors Guild Awards (Outstanding Performance by a Male Actor in a Leading Role - Morgan Freeman)
  (35, 24, 1, 2, 6, 1995, false),     -- Screen Actors Guild Awards (Outstanding Performance by a Male Actor in a Leading Role - Tim Robbins)
  (36, 25, 1, 2, 5, 1995, true),      -- USC Scripter Award (Frank Darabont)
  (37, 26, 1, 2, 5, 1995, false)      -- WGA Award (Best Adapted Screenplay - Frank Darabont)
;
INSERT INTO `movies_taglines` (`movie_id`, `language_id`, `tagline`, `dyn_comments`) VALUES (2, 41, 'Fear can hold you prisoner. Hope can set you free.', '');
INSERT INTO `movies_languages` (`movie_id`, `language_id`) VALUES (2, 41);
COMMIT;

-- Léon: The Professional
BEGIN;
INSERT INTO `movies` (`year`, `runtime`, `original_title`, `dyn_synopses`, `created`) VALUES (
  '1994',
  110,
  'Léon',
  COLUMN_CREATE('en',
    'As visually stylish as it is graphically violent, this thriller directed by Luc Besson concerns Mathilda (Natalie Portman), a 12-year-old girl living in New York City who has been exposed to the sordid side of life from an early age: her family lives in a slum and her abusive father works for drug dealers, cutting and storing dope. Mathilda doesn&apos;t care much for her parents, but she has a close bond with her four-year-old brother. One day, she returns from running an errand to discover that most of her family, including her brother, have been killed in a raid by corrupt DEA agents, led by the psychotic Stansfield (Gary Oldman). Mathilda takes refuge in the apartment of her secretive neighbor, Leon (Jean Reno), who takes her in with a certain reluctance. She discovers that Leon is a professional assassin, working for Tony (Danny Aiello), a mob kingpin based in Little Italy. Wanting to avenge the death of her brother, Mathilda makes a deal with Leon to become his protégée in exchange for work as a domestic servant, hoping to learn the hitman&apos;s trade and take out the men who took her brother&apos;s life. However, an affection develops between Leon and Mathilda that changes his outlook on his life and career. Besson&apos;s first American film boasted a strong performance from Jean Reno, a striking debut by Natalie Portman, and a love-it-or-hate-it, over-the-top turn by Gary Oldman. Léon was originally released in the U.S. in 1994 as The Professional, with 26 minutes cut in response to audience preview tests. Those 26 minutes were restored in the director&apos;s preferred cut, released in 1996 in France as Léon: Version Intégrale and in the U.S. on DVD as Léon: The Professional in 2000.',
    'de',
    'Für Léon (Jean Reno) gibt es nur seinen Job. Seit seiner Jugend arbeitet er als professioneller Auftragskiller. Er tötet ohne mit der Wimper zu zucken. Das einzig Menschliche an ihm ist seine Liebe zu einer Zimmerpflanze. Diese hegt und pflegt er nach Kräften. Die kleine Mathilda (Natalie Portman) wohnt direkt neben Leon. Das zwölfjährige Mädchen hat keine schöne Kindheit. Ihre Mutter geht auf den Strich und der Vater ist ein miefiger kleiner Drogendealer. Drogenfahnder Norman Stansfield (Gary Oldman) fühlt sich von Mathildas Vater betrogen und bringt die ganze Familie kurzerhand um. Nur Mathilda überlebt das Massaker. In ihrer Angst wendet sie sich an Léon. Der zurückgezogene Leon kann mit Menschen nicht besonders gut umgehen. Er versucht Mathilda so schnell wie möglich wieder los zu werden - bis die Kleine ihm einen Deal vorschlägt: Sie verspricht Léon den Haushalt zu führen, wenn er ihr beibringt professionell zu töten. Mathilda will sich an Stansfield rächen, der ihren kleinen Bruder kaltblütig erschossen hat. Zuerst lehnt Leon ab, doch nach und nach lockt Mathilda den wortkargen Einzelgänger aus der Reserve bis er sie schließlich in die "Kunst" eines "Cleaners" einweiht.'
  ),
  '2013-06-01 00:10:00'
);
INSERT INTO `movies_titles` (`movie_id`, `language_id`, `title`, `dyn_comments`, `is_display_title`) VALUES (3, 41, 'Léon: The Professional', '', true);
INSERT INTO `movies_countries` (`movie_id`, `country_id`) VALUES (3, 75), (3, 233); -- France and USA
INSERT INTO `movies_genres` (`movie_id`, `genre_id`) VALUES (3, 8), (3, 1), (3, 21); -- Drama, Action, Thriller
INSERT INTO `movies_directors` (`movie_id`, `person_id`) VALUES (3, 1); -- Luc Besson
INSERT INTO `movies_cast` (`movie_id`, `person_id`, `roles`) VALUES (3, 2, 'Léon'), (3, 3, 'Mathilda'), (3, 4, 'Stansfield'); -- Jean Reno, Natalie Portman, Gary Oldman
INSERT INTO `movies_awards` (`award_count`, `award_id`, `award_category_id`, `movie_id`, `person_id`, `year`, `won`) VALUES
  (1, 1, 1, 3, NULL, 1996, false),  -- Japanese Academy Award (Best Foreign Film)
  (2, 2, 1, 3, NULL, 1996, true),   -- Czech Lion (Best Foreign Language Film)
  (3, 3, 1, 3, 2, 1995, false),     -- César Award (Best Actor - Jean Reno)
  (4, 3, 2, 3, NULL, 1995, false),  -- César Award (Best Cinematography)
  (5, 3, 3, 3, 1, 1995, false),     -- César Award (Best Director - Luc Besson)
  (6, 3, 4, 3, NULL, 1995, false),  -- César Award (Best Editing)
  (7, 3, 5, 3, 1, 1995, false),     -- César Award (Best Film - Luc Besson)
  (8, 3, 6, 3, NULL, 1995, false),  -- César Award (Best Music)
  (9, 3, 7, 3, NULL, 1995, false),  -- César Award (Best Sound)
  (10, 4, 1, 3, NULL, 1995, false)  -- Golden Reel Award (Best Sound Editing - Foreign Feature)
;
INSERT INTO `movies_taglines` (`movie_id`, `language_id`, `tagline`, `dyn_comments`)
VALUES (3, 41, 'If you want a job done well hire a professional.', ''),
(3, 41, 'A perfect assassin. An innocent girl. They have nothing left to lose except each other. He moves without sound. Kills without emotion. Disappears without trace. Only a 12 year old girl... knows his weakness.', ''),
(3, 41, 'He moves without sound. Kills without emotion. Disappears without trace.', ''),
(3, 41, 'You can&apos;t stop what you can&apos;t see.', '');
INSERT INTO `movies_languages` (`movie_id`, `language_id`) VALUES (3, 41);
COMMIT;
