<?php
// Se já houve saída, não é seguro alterar ini nem iniciar sessão
if (headers_sent()) {
	return;
}

// Configurações vindas do ambiente para alinhar com o serviço auth
$handler = getenv('SESSION_SAVE_HANDLER') ?: 'redis';
$savePath = getenv('SESSION_SAVE_PATH') ?: 'tcp://redis:6379?database=0&prefix=PHPSESSID:';
$sessionName = getenv('SESSION_NAME') ?: 'PHPSESSID';

// Se handler for redis mas extensão não estiver disponível, não tenta iniciar
if ($handler === 'redis' && !extension_loaded('redis')) {
	// Opcional: error_log('Redis extension not loaded; skipping shared session bootstrap');
	return;
}

// Detecta PHPSESSID via cookie ou cabeçalho e só então inicia sessão
$cookieId = $_COOKIE[$sessionName] ?? null;
$headerId = $_SERVER['HTTP_PHPSESSID'] ?? null; // suporte a cabeçalho PHPSESSID
$hasId = ($cookieId || $headerId);

if ($hasId && session_status() === PHP_SESSION_NONE) {
	ini_set('session.save_handler', $handler);
	ini_set('session.save_path', $savePath);
	session_name($sessionName);
	if ($headerId && !$cookieId) {
		// Evita Set-Cookie; usa explicitamente o ID vindo em header
		session_id($headerId);
	}
	ini_set('session.use_strict_mode', '1');
	ini_set('session.use_cookies', $cookieId ? '1' : '0');
	ini_set('session.use_only_cookies', $cookieId ? '1' : '0');
	ini_set('session.use_trans_sid', '0');

	// Abre sessão e fecha imediatamente para leitura sem lock duradouro
	session_start([
		'read_and_close' => true,
	]);

	$userId = $_SESSION['user_id'] ?? null;
	$userRole = $_SESSION['role'] ?? null;

	if (!defined('GATEWAY_USER_ID')) define('GATEWAY_USER_ID', $userId);
	if (!defined('GATEWAY_USER_ROLE')) define('GATEWAY_USER_ROLE', $userRole);

	if ($userId !== null) {
		$_SERVER['HTTP_X_USER_ID'] = (string)$userId;
	}
	if ($userRole !== null) {
		$_SERVER['HTTP_X_USER_ROLE'] = (string)$userRole;
	}
}


