<?php

// Only do this for a docksal request.
if (substr($_SERVER['VIRTUAL_HOST'] ?? '', -13) == '.docksal.site') {
  require('../.docksal/includes/docksal_sites.php');
}