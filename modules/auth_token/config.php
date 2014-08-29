<?php

Config::write('authtoken.tokens.table', '');
Config::write('authtoken.users.table', '');

Config::write('authtoken.attempts.field', '');

Config::write('authtoken.token.field', 'TOKEN');
Config::write('authtoken.tokenid.field', 'ID');
Config::write('authtoken.userid.field', 'ID');
Config::write('authtoken.email.field', 'EMAIL');
Config::write('authtoken.expiration.field', 'WAZNOSC');
Config::write('authtoken.expiration.field.type', 'DATETIME');
Config::write('authtoken.expiration.field.format', 'Y-m-d H:i:s');
Config::write('authtoken.default.minval', 10000000);
Config::write('authtoken.default.maxval', 99999999);
Config::write('authtoken.default.expiration', 180);
Config::write('authtoken.mail.from', '');
Config::write('authtoken.lock.messages', 3);
Config::write('authtoken.lock.attempts', 5);

?>