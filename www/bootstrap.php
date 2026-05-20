<?php
require_once __DIR__ . '/includes/constants.php';

require_once __DIR__ . '/exceptions/ValidationException.php';
require_once __DIR__ . '/exceptions/AuthorizationException.php';

require_once __DIR__ . '/services/LoggerService.php';
require_once __DIR__ . '/services/ValidationService.php';
require_once __DIR__ . '/services/ResponseService.php';
require_once __DIR__ . '/services/CsrfService.php';
require_once __DIR__ . '/services/AuthorizationService.php';
require_once __DIR__ . '/services/RateLimitService.php';
require_once __DIR__ . '/services/MongoStatsService.php';

require_once __DIR__ . '/classes/BaseManager.php';
require_once __DIR__ . '/classes/UserManager.php';
require_once __DIR__ . '/classes/VehiculeManager.php';
require_once __DIR__ . '/classes/VoyageManager.php';
require_once __DIR__ . '/classes/CovoiturageManager.php';
require_once __DIR__ . '/classes/ContactManager.php';
require_once __DIR__ . '/classes/RoleManager.php';
require_once __DIR__ . '/classes/MenuBuilder.php';

require_once __DIR__ . '/config.php';
