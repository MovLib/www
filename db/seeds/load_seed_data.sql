USE `movlib`;
-- Import all seed scripts.
source licenses.sql
source genres.sql
source users.sql
source persons.sql
source awards.sql
source movies.sql
-- source labels.sql
source aspect_ratios.sql
source packaging.sql
source sound_formats.sql
source releases.sql
source posters.sql

SHOW WARNINGS;
