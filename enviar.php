<?php
header('Content-Type: application/json; charset=utf-8');

// ===== CONFIGURA CORREOS =====
$destinatarios = ['kevin.melchor@automaq-mexico.com', 'ventas@automaq-mexico.com', 'contacto@automaq-mexico.com'];
$remitente     = 'no-responder@automaq-mexico.com';
$asunto        = 'Nuevo mensaje desde el sitio web';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'msg' => 'Método no permitido.']);
  exit;
}

if (!empty($_POST['sitio_web'])) {
  echo json_encode(['ok' => true]);
  exit;
}

function limpiar($v) {
  return trim(str_replace(["\r", "\n"], ' ', strip_tags($v ?? '')));
}

$nombre   = limpiar($_POST['nombre'] ?? '');
$correo   = limpiar($_POST['correo'] ?? '');
$telefono = limpiar($_POST['telefono'] ?? '');
$mensaje  = trim(strip_tags($_POST['mensaje'] ?? ''));

if ($nombre === '' || $mensaje === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'msg' => 'Revisa los campos obligatorios.']);
  exit;
}

$cuerpo  = "Nombre / Empresa: $nombre\n";
$cuerpo .= "Correo: $correo\n";
$cuerpo .= 'Teléfono: ' . ($telefono !== '' ? $telefono : 'No proporcionado') . "\n\n";
$cuerpo .= "Mensaje:\n$mensaje\n";

$headers  = "From: Sitio web Automaq <$remitente>\r\n";
$headers .= "Reply-To: $correo\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$subject = '=?UTF-8?B?' . base64_encode($asunto) . '?=';

if (mail(implode(',', $destinatarios), $subject, $cuerpo, $headers)) {
  echo json_encode(['ok' => true, 'msg' => '¡Mensaje enviado! Nos pondremos en contacto contigo pronto.']);
} else {
  http_response_code(500);
  echo json_encode(['ok' => false, 'msg' => 'No se pudo enviar el mensaje. Inténtalo de nuevo más tarde.']);
}