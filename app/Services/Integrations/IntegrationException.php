<?php

namespace App\Services\Integrations;

use RuntimeException;

// Only deliberately safe messages from this exception may be shown in the admin log.
class IntegrationException extends RuntimeException {}
