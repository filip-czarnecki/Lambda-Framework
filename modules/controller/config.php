<?php
/*
LMBDV1 configuration file
*/

/*Default module to be called by using autoload. Use 'none' to disable */
Config::write('lmbd.default.module', 'none');
/*Whether to force Lambda to use default module, ignoring any user calls. Default: 0 */
Config::write('lmbd.default.module.force', 0);
/*Mode in which Lambda controller loads modules.
Available options:
automatic - Default option. It has a possibility of defining time interval after which modules will be re-discovered using discovery mode.
discovery - Option recommended while developing application. It discovers modules every time page reloads and uses their ini files.
performance - Option recommended for production environment. For discovering modules, it uses only modules_enabled.php file, which can be generated by auto mode or by developer.*/
Config::write('lmbd.controller.workmode', 'discovery');
/*Used only in automatic controller workmode. Defines time interval (in seconds) for module discovery. */
Config::write('lmbd.controller.scan.interval', 3600);

?>