USE `movlib`;
-- Roundhay Garden Scene (The world's first movie)
BEGIN;
INSERT INTO `movies` (`year`, `runtime`, `original_title`, `dyn_synopses`) VALUES (
  '1888',
  1,
  'Roundhay Garden Scene',
  COLUMN_CREATE('en', 'This is the first movie ever.')
);
INSERT INTO `movies_countries` (`movie_id`, `country_id`) VALUES (1, 77);
INSERT INTO `movies_genres` (`movie_id`, `genre_id`) VALUES (1, 7), (1, 18), (1, 19);
INSERT INTO `movies_languages` (`movie_id`, `language_id`) VALUES (1, 185);
COMMIT;

-- Léon: The Professional
BEGIN;
INSERT INTO `movies` (`year`, `runtime`, `original_title`, `dyn_synopses`) VALUES (
  '1994',
  110,
  'Léon',
  COLUMN_CREATE('en',
    'As visually stylish as it is graphically violent, this thriller directed by Luc Besson concerns Mathilda (Natalie Portman), a 12-year-old girl living in New York City who has been exposed to the sordid side of life from an early age: her family lives in a slum and her abusive father works for drug dealers, cutting and storing dope. Mathilda doesn&apos;t care much for her parents, but she has a close bond with her four-year-old brother. One day, she returns from running an errand to discover that most of her family, including her brother, have been killed in a raid by corrupt DEA agents, led by the psychotic Stansfield (Gary Oldman). Mathilda takes refuge in the apartment of her secretive neighbor, Leon (Jean Reno), who takes her in with a certain reluctance. She discovers that Leon is a professional assassin, working for Tony (Danny Aiello), a mob kingpin based in Little Italy. Wanting to avenge the death of her brother, Mathilda makes a deal with Leon to become his protégée in exchange for work as a domestic servant, hoping to learn the hitman&apos;s trade and take out the men who took her brother&apos;s life. However, an affection develops between Leon and Mathilda that changes his outlook on his life and career. Besson&apos;s first American film boasted a strong performance from Jean Reno, a striking debut by Natalie Portman, and a love-it-or-hate-it, over-the-top turn by Gary Oldman. Léon was originally released in the U.S. in 1994 as The Professional, with 26 minutes cut in response to audience preview tests. Those 26 minutes were restored in the director&apos;s preferred cut, released in 1996 in France as Léon: Version Intégrale and in the U.S. on DVD as Léon: The Professional in 2000.',
    'de',
    'Für Léon (Jean Reno) gibt es nur seinen Job. Seit seiner Jugend arbeitet er als professioneller Auftragskiller. Er tötet ohne mit der Wimper zu zucken. Das einzig Menschliche an ihm ist seine Liebe zu einer Zimmerpflanze. Diese hegt und pflegt er nach Kräften. Die kleine Mathilda (Natalie Portman) wohnt direkt neben Leon. Das zwölfjährige Mädchen hat keine schöne Kindheit. Ihre Mutter geht auf den Strich und der Vater ist ein miefiger kleiner Drogendealer. Drogenfahnder Norman Stansfield (Gary Oldman) fühlt sich von Mathildas Vater betrogen und bringt die ganze Familie kurzerhand um. Nur Mathilda überlebt das Massaker. In ihrer Angst wendet sie sich an Léon. Der zurückgezogene Leon kann mit Menschen nicht besonders gut umgehen. Er versucht Mathilda so schnell wie möglich wieder los zu werden - bis die Kleine ihm einen Deal vorschlägt: Sie verspricht Léon den Haushalt zu führen, wenn er ihr beibringt professionell zu töten. Mathilda will sich an Stansfield rächen, der ihren kleinen Bruder kaltblütig erschossen hat. Zuerst lehnt Leon ab, doch nach und nach lockt Mathilda den wortkargen Einzelgänger aus der Reserve bis er sie schließlich in die "Kunst" eines "Cleaners" einweiht.'
  )
);
INSERT INTO `movies_titles` (`movie_id`, `language_id`, `title`, `dyn_comments`, `is_display_title`) VALUES (2, 41, 'Léon: The Professional', '', true);
INSERT INTO `movies_countries` (`movie_id`, `country_id`) VALUES (2, 75), (2, 233);
INSERT INTO `movies_genres` (`movie_id`, `genre_id`) VALUES (2, 8), (2, 1), (2, 21);
INSERT INTO `movies_directors` (`movie_id`, `person_id`) VALUES (2, 1);
INSERT INTO `movies_cast` (`movie_id`, `person_id`, `roles`) VALUES (2, 2, 'Léon'), (2, 3, 'Mathilda'), (2, 4, 'Stansfield');
INSERT INTO `movies_awards` (`award_id`, `movie_id`, `year`, `won`) VALUES (1, 2, 1996, false), (2, 2, 1996, true), (3, 2, 1995, false), (4, 2, 1995, true);
INSERT INTO `movies_taglines` (`movie_id`, `language_id`, `tagline`, `dyn_comments`)
VALUES (2, 41, 'If you want a job done well hire a professional.', ''),
(2, 41, 'A perfect assassin. An innocent girl. They have nothing left to lose except each other. He moves without sound. Kills without emotion. Disappears without trace. Only a 12 year old girl... knows his weakness.', ''),
(2, 41, 'He moves without sound. Kills without emotion. Disappears without trace.', ''),
(2, 41, 'You can&apos;t stop what you can&apos;t see.', '');
INSERT INTO `movies_languages` (`movie_id`, `language_id`) VALUES (2, 41);
COMMIT;
