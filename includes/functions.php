<?php
// دالة مساعدة لحماية النص عند عرضه داخل HTML.
function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect($path) {
    header('Location: ' . $path);
    exit;
}
