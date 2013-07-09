BEGIN;
INSERT INTO `awards` (`name`, `dyn_names`, `dyn_descriptions`) VALUES ('日本アカデミー賞', COLUMN_CREATE('en', 'Japan Academy Prize', 'de', 'Japanese Academy Award'), '');
INSERT INTO `awards` (`name`, `dyn_names`, `dyn_descriptions`) VALUES ('Český lev', COLUMN_CREATE('en', 'Czech Lion', 'de', 'Tschechischer Löwe'), '');
INSERT INTO `awards` (`name`, `dyn_names`, `dyn_descriptions`) VALUES ('César', COLUMN_CREATE('en', 'César Award', 'de', 'César'), '');
INSERT INTO `awards` (`name`, `dyn_names`, `dyn_descriptions`) VALUES ('Golden Reel Award (Motion Picture Sound Editors)', COLUMN_CREATE('en', 'Golden Reel Award (Motion Picture Sound Editors)', 'de', 'Golden Reel Award (Motion Picture Sound Editors)'), '');
COMMIT;
