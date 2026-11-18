
<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . "/../config.php";

function response_json($status, $message, $data = null, $httpCode = 200) {
    http_response_code($httpCode);

    $response = [
        "status" => $status,
        "message" => $message
    ];

    if ($data !== null) {
        $response["data"] = $data;
    }

    echo json_encode(
        $response,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    );
    exit;
}

function cek_method($method) {
    if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
        http_response_code(204);
        exit;
    }

    if ($_SERVER["REQUEST_METHOD"] !== $method) {
        response_json(
            false,
            "Method tidak diizinkan. Gunakan " . $method,
            null,
            405
        );
    }
}

function baca_json() {
    $isi = file_get_contents("php://input");
    $data = json_decode($isi, true);

    if (!is_array($data)) {
        response_json(false, "Format JSON tidak valid", null, 400);
    }

    return $data;
}
?>
