<?php

// be forgiving with error handlers which do not suppress `@` prefixed lines
set_error_handler(function(int $err_lvl, string $err_msg, string $err_file, int $err_line): bool {
    throw new \ErrorException($err_msg, 0, $err_lvl, $err_file, $err_line);
});

