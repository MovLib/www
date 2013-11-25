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
  COLUMN_CREATE('en', 'Action', 'de', 'Action'),
  COLUMN_CREATE('en', 'Action film is a film genre in which one or more heroes are thrust into a series of challenges that typically include physical feats, extended fight scenes, violence, and frenetic chases. Action films tend to feature a resourceful character struggling against incredible odds, including life-threatening situations, a villain, or a pursuit, which generally conclude in victory for the hero.')
),
(
  COLUMN_CREATE('en', 'Adventure', 'de', 'Abenteuer'),
  COLUMN_CREATE('en', 'Adventure films are a genre of film. Unlike action films, they often use their action scenes preferably to display and explore exotic locations in an energetic way.')
),
(
  COLUMN_CREATE('en', 'Animation', 'de', 'Animation'),
  COLUMN_CREATE('en', 'Animation is the rapid display of a sequence of images to create an illusion of movement. The most common method of presenting animation is as a motion picture or video program, although there are other methods. This type of presentation is usually accomplished with a camera and a projector or a computer viewing screen which can rapidly cycle through images in a sequence. Animation can be made with either hand rendered art, computer generated imagery, or three-dimensional objects, e.g., puppets or clay figures, or a combination of techniques. The position of each object in any particular image relates to the position of that object in the previous and following images so that the objects each appear to fluidly move independently of one another. The viewing device displays these images in rapid succession, usually 24, 25, or 30 frames per second.')
),
(
  COLUMN_CREATE('en', 'Biography', 'de', 'Biografie'),
  COLUMN_CREATE('en', 'A biographical film, or biopic (/ˈbaɪɵpɪk/; abbreviation for biographical motion picture), is a film that dramatizes the life of an actual person or people. Such films show the life of a historical person and the central character’s real name is used. They differ from films “based on a true story” or “historical films” in that they attempt to comprehensively tell a person’s life story or at least the most historically important years of their lives.')
),
(
  COLUMN_CREATE('en', 'Comedy', 'de', 'Komödie'),
  COLUMN_CREATE('en', 'Comedy film is a genre of film in which the main emphasis is on humour. These films are designed to elicit laughter from the audience. Comedies are generally light-hearted dramas and are made to amuse and entertain the audiences. The comedy genre often humorously exaggerates situations, ways of speaking, or the action and characters.')
),
(
  COLUMN_CREATE('en', 'Crime', 'de', 'Krimi'),
  COLUMN_CREATE('en', 'Crime films are films which focus on the lives of criminals. The stylistic approach to a crime film varies from realistic portrayals of real-life criminal figures, to the far-fetched evil doings of imaginary arch-villains. Criminal acts are almost always glorified in these movies.')
),
(
  COLUMN_CREATE('en', 'Documentary', 'de', 'Dokumentation'),
  COLUMN_CREATE('en', 'Documentary films constitute a broad category of nonfictional motion pictures intended to document some aspect of reality, primarily for the purposes of instruction or maintaining a historical record.')
),
(
  COLUMN_CREATE('en', 'Drama', 'de', 'Drama'),
  COLUMN_CREATE('en', 'A drama film is a film genre that depends mostly on in-depth development of realistic characters dealing with emotional themes. Dramatic themes such as alcoholism, drug addiction, infidelity, moral dilemmas, racial prejudice, religious intolerance, sexuality, poverty, class divisions, violence against women and corruption put the characters in conflict with themselves, others, society and even natural phenomena.[1] Drama is the most broad of movies genres and includes subgenres as romantic drama, sport films, period drama, courtroom drama and crime.')
),
(
  COLUMN_CREATE('en', 'Children', 'de', 'Kinder'),
  COLUMN_CREATE('en', 'A children’s film (or family film) is a film genre that contains children or relates to them in the context of home and family. Children’s films refer to films that are made specifically for children and not necessarily for the general audience while family films are made for a wider appeal with a general audience in mind. Children’s films come in several major forms like realism, fantasy, animation, war, musicals, and literary adaptations.')
),
(
  COLUMN_CREATE('en', 'Fantasy', 'de', 'Fantasy'),
  COLUMN_CREATE('en', 'Fantasy films are films with fantastic themes, usually involving magic, supernatural events, make-believe creatures, or exotic fantasy worlds. The genre is considered to be distinct from science fiction film and horror film, although the genres do overlap. Fantasy films often have an element of magic, myth, wonder, escapism, and the extraordinary.')
),
(
  COLUMN_CREATE('en', 'Film Noir', 'de', 'Film-Noir'),
  COLUMN_CREATE('en', 'Film noir is a cinematic term used primarily to describe stylish crime dramas, particularly those that emphasize cynical attitudes and sexual motivations. Hollywood’s classical film noir period is generally regarded as extending from the early 1940s to the late 1950s. Film noir of this era is associated with a low-key black-and-white visual style that has roots in German Expressionist cinematography. Many of the prototypical stories and much of the attitude of classic noir derive from the hardboiled school of crime fiction that emerged in the United States during the Great Depression.')
),
(
  COLUMN_CREATE('en', 'History', 'de', 'Historie'),
  COLUMN_CREATE('en', 'The historical drama is a film genre in which stories are based upon historical events and famous people. Some historical dramas are docudramas, which attempt an accurate portrayal of a historical event or biography, to the degree that the available historical research will allow. Other historical dramas are fictionalized tales that are based on an actual person and their deeds, such as Braveheart, which is loosely based on the 13th century knight William Wallace’s fight for Scotland’s independence.')
),
(
  COLUMN_CREATE('en', 'Horror', 'de', 'Horror'),
  COLUMN_CREATE('en', 'Horror is a film genre seeking to elicit a negative emotional reaction from viewers by playing on the audience’s primal fears. Horror films often feature scenes that startle the viewer; the macabre and the supernatural are frequent themes. Thus they may overlap with the fantasy, supernatural, and thriller genres.')
),
(
  COLUMN_CREATE('en', 'Musical', 'de', 'Musik'),
  COLUMN_CREATE('en', 'The musical film is a film genre in which songs sung by the characters are interwoven into the narrative, sometimes accompanied by dancing. The songs usually advance the plot or develop the film’s characters, though in some cases they serve merely as breaks in the storyline, often as elaborate “production numbers”.')
),
(
  COLUMN_CREATE('en', 'Mystery', 'de', 'Mystery'),
  COLUMN_CREATE('en', 'Mystery film is a sub-genre of the more general category of crime film and at times the thriller genre. It focuses on the efforts of the detective, private investigator or amateur sleuth to solve the mysterious circumstances of a crime by means of clues, investigation, and clever deduction.')
),
(
  COLUMN_CREATE('en', 'Romance', 'de', 'Liebe'),
  COLUMN_CREATE('en', 'Romance films (or romance movies) are romantic love stories recorded in visual media for broadcast in theaters and on television that focus on passion, emotion, and the affectionate romantic involvement of the main characters and the journey that their genuinely strong, true and pure romantic love takes them through dating, courtship or marriage. Romance films make the romantic love story or the search for strong and pure love and romance the main plot focus. Occasionally, romance lovers face obstacles such as finances, physical illness, various forms of discrimination, psychological restraints or family that threaten to break their union of love. As in all quite strong, deep, and close romantic relationships, tensions of day-to-day life, temptations (of infidelity), and differences in compatibility enter into the plots of romantic films.')
),
(
  COLUMN_CREATE('en', 'Science Fiction', 'de', 'Science-Fiction'),
  COLUMN_CREATE('en', 'Science fiction film is a film genre that uses science fiction: speculative, science-based depictions of phenomena that are not necessarily accepted by mainstream science, such as extraterrestrial life forms, alien worlds, extrasensory perception, and time travel, often along with futuristic elements such as spacecraft, robots, cyborgs, interstellar space travel or other technologies. Science fiction films have often been used to focus on political or social issues, and to explore philosophical issues like the human condition. In many cases, tropes derived from written science fiction may be used by filmmakers ignorant of or at best indifferent to the standards of scientific plausibility and plot logic to which written science fiction is traditionally held.')
),
(
  COLUMN_CREATE('en', 'Short', 'de', 'Kurz'),
  COLUMN_CREATE('en', 'A short film is any film not long enough to be considered a feature film. No consensus exists as to where that boundary is drawn: the Academy of Motion Picture Arts and Sciences defines a short film as "an original motion picture that has a running time of 40 minutes or less, including all credits". The term featurette originally applied to a film longer than a short subject, but shorter than a standard feature film.')
),
(
  COLUMN_CREATE('en', 'Silent', 'de', 'Stumm'),
  COLUMN_CREATE('en', 'A silent film is a film with no synchronized recorded sound, especially with no spoken dialogue. In silent films for entertainment the dialogue is transmitted through muted gestures, mime (US: pantomime) and title cards. The idea of combining motion pictures with recorded sound is nearly as old as film itself, but because of the technical challenges involved, synchronized dialogue was only made practical in the late 1920s with the perfection of the Audion amplifier tube and the introduction of the Vitaphone system. After the release of The Jazz Singer in 1927, "talkies" became more and more commonplace. Within a decade, popular widespread production of silent films had ceased.')
),
(
  COLUMN_CREATE('en', 'Sport', 'de', 'Sport'),
  COLUMN_CREATE('en', 'A Sport Film revolves around a sport setting, event, or an athlete. Often, these films will center on a single sporting event that carries significant importance. Sports films traditionally have a simple plot that builds up to the significant sporting event. This genre is known for incorporating film techniques to build anticipation and intensity. Sport films have a large range of sub-genres, from comedies to dramas, and are more likely than other genres to be based true-life events.')
),
(
  COLUMN_CREATE('en', 'Thriller', 'de', 'Thriller'),
  COLUMN_CREATE('en', 'Thriller is a broad genre of literature, film, and television programming that uses suspense, tension and excitement as the main elements. Thrillers heavily stimulate the viewer’s moods giving them a high level of anticipation, ultra-heightened expectation, uncertainty, surprise, anxiety and/or terror. Thriller films tend to be adrenaline-rushing, gritty, rousing and fast-paced.')
),
(
  COLUMN_CREATE('en', 'War', 'de', 'Krieg'),
  COLUMN_CREATE('en', 'War films are a film genre concerned with warfare, usually about naval, air or land battles, sometimes focusing instead on prisoners of war, covert operations, military training or other related subjects. At times war films focus on daily military or civilian life in wartime without depicting battles. Their stories may be fiction, based on history, docudrama, biographical, or even alternate history fiction. The term anti-war film is sometimes used to describe films which bring to the viewer the pain and horror of war, often from a political or ideological perspective.')
),
(
  COLUMN_CREATE('en', 'Western', 'de', 'Western'),
  COLUMN_CREATE('en', 'The Western is a genre of various arts, such as film, television, radio, literature, painting and others. Westerns are devoted to telling stories set primarily in the latter half of the 19th century in the American Old West, hence the name. Some Westerns are set as early as the Battle of the Alamo in 1836. There are also a number of films about Western-type characters in contemporary settings, such as Junior Bonner set in the 1970s and The Three Burials of Melquiades Estrada in the 21st century.')
),
(
  COLUMN_CREATE('en', 'Erotic', 'de', 'Erotik'),
  COLUMN_CREATE('en', 'Erotic film genre …')
)
;
