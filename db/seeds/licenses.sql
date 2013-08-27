BEGIN;
INSERT INTO `licenses` (
  `name`,
  `description`,
  `dyn_names`,
  `dyn_descriptions`,
  `url`,
  `abbr`,
  `icon_extension`,
  `admin`
) VALUES (
  'Copyright protected',
  '',
  '',
  '',
  'https://en.wikipedia.org/wiki/Copyright',
  '©',
  'svg',
  TRUE
);
INSERT INTO `licenses` (
  `name`,
  `description`,
  `dyn_names`,
  `dyn_descriptions`,
  `url`,
  `abbr`,
  `icon_extension`,
  `admin`
) VALUES (
  'Creative Commons CC0 1.0 Universal Public Domain Dedication',
  '<p>The person who associated a work with this deed has dedicated the work to the public domain by waiving all of his or her rights to the work worldwide under copyright law, including all related and neighboring rights, to the extent allowed by law. You can copy, modify, distribute and perform the work, even for commercial purposes, all without asking permission.</p>',
  '',
  '',
  'https://creativecommons.org/publicdomain/zero/1.0/',
  'CC0 1.0',
  'svg',
  TRUE
);
INSERT INTO `licenses` (
  `name`,
  `description`,
  `dyn_names`,
  `dyn_descriptions`,
  `url`,
  `abbr`,
  `icon_extension`,
  `admin`
) VALUES (
  'Creative Commons Attribution 3.0 Unported',
  '<p>You are free:</p><ul><li><b>to share</b> – to copy, distribute and transmit the work</li><li><b>to remix</b> – to adapt the work</li></ul><p>Under the following conditions:<ul><li><b>attribution</b> – You must attribute the work in the manner specified by the author or licensor (but not in any way that suggests that they endorse you or your use of the work).</li></ul></p>',
  '',
  '',
  'https://creativecommons.org/licenses/by/3.0/',
  'CC BY 3.0',
  'svg',
  TRUE
);
COMMIT;