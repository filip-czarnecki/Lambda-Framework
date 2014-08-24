<?php

Config::write('logger.enabled', 1);
Config::write('logger.display', 1);
Config::write('logger.write', 0);
Config::write('logger.level', 'ALL');
Config::write('logger.file', 'logger/lmbd_log.txt');
Config::write('logger.autoremove.set', 0);
Config::write('logger.autoremove.size', 1048576);

?>