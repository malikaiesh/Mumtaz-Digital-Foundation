<?php
require_once __DIR__ . '/../config/init.php';

session_destroy();
redirect('/');
