<?php
require_once "config.global.php";

require_once "{$global_config['path']['system']}/fortnight.php";

$sys = new Fortnight($global_config);
$sys->execute();

?>