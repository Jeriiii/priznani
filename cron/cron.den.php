<?php

require_once 'request.php';

/* odeslání upozornění */
http_get("http://datenode.cz/cron/send-notifies");
