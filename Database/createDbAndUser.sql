CREATE DATABASE `seek-r`;

CREATE USER 'seek-r'@'localhost' IDENTIFIED BY '***';

GRANT USAGE ON *.* TO 'seek-r'@'localhost' IDENTIFIED BY PASSWORD '--PASSWORD--';

GRANT SELECT, INSERT, UPDATE ON `seek-r`.`scan` TO 'seek-r'@'localhost';

GRANT SELECT, INSERT, UPDATE ON `seek-r`.`directory` TO 'seek-r'@'localhost';
GRANT SELECT, INSERT, UPDATE ON `seek-r`.`directoryHistory` TO 'seek-r'@'localhost';

GRANT SELECT, INSERT, UPDATE ON `seek-r`.`file` TO 'seek-r'@'localhost';
GRANT SELECT, INSERT, UPDATE ON `seek-r`.`fileHistory` TO 'seek-r'@'localhost';

GRANT SELECT, INSERT ON `seek-r`.`log` TO 'seek-r'@'localhost';
