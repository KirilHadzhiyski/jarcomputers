<?php

// The build sets this override while validating the staged release.
$override = getenv('JAR_PUBLIC_PATH');
if (is_string($override) && $override !== '') {
    return $override;
}

// Installed as /home/ACCOUNT/jar-app/public-path.php for the jarbl.bg addon domain.
return dirname(__DIR__).'/public_html/jarbl.bg';
