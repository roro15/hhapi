<?php

namespace hh\exception;

use Exception;

class BaseException extends Exception {

    const ERROR_UNKNOWN = 1000;
    const ERROR_AUTH = 1001;
    const ERROR_NETWORK = 1002;
    const ERROR_VALIDATION = 1003;
    const ERROR_SERVER = 1004;
    const ERROR_EXTERNAL = 1005;

}
