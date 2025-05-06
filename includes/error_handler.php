<?php
function handleError($e, $customMessage = 'Une erreur est survenue') {
    error_log($e->getMessage());
    http_response_code(500);
    return [
        'success' => false,
        'message' => $customMessage,
        'error' => DEBUG_MODE ? $e->getMessage() : null
    ];
}