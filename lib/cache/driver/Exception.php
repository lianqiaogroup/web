Exception<?php

namespace lib\cache\driver;

use lib\cache\Driver;

class Exception extends Driver {
	public function __construct($msg = '') {
		die($msg);
	}
}