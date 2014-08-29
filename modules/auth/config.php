<?php

Config::write('auth.login.minlength', 3);
Config::write('auth.login.maxlength', 24);
#Config::write('auth.login.allowed', '/[^A-Za-z0-9.#\\-$]/');
Config::write('auth.login.field', 'login');
Config::write('auth.pass.field', 'haslo');
Config::write('auth.salt.field', 'sol');
Config::write('auth.privledge.field', 'uprawnienie');
Config::write('auth.id.field', 'id');
Config::write('auth.authcode.field', 'kodautoryzacji');
Config::write('auth.privcode.field', 'kod');
Config::write('auth.group.field', 'grupa');
Config::write('auth.user.field', 'uzytkownik');
Config::write('auth.users.table', 'uzytkownicy');
Config::write('auth.privledges.table', 'uprawnienia');
Config::write('auth.groups.table', 'grupy');
Config::write('auth.authcodes.table', 'kodyautoryzacji');
Config::write('auth.pages.table', 'strony');
Config::write('auth.page.field', 'strona');
Config::write('auth.name.field', 'nazwa');
Config::write('auth.parent.field', 'rodzic');
Config::write('auth.address.field', 'adres');
Config::write('auth.register.field', 'czasrejestracji');
Config::write('auth.last.login.field', 'ostatnilogin');
Config::write('auth.active.field', 'aktywny');
Config::write('auth.active.y.field', 'T');
Config::write('auth.active.n.field', 'N');

?>