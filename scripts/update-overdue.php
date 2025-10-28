<?php

date_default_timezone_set('America/Sao_Paulo');

require_once __DIR__ . '/../services/utils/LoadEnv.php';
require_once __DIR__ . '/../services/auth/models/BorrowModel.php';

LoadEnv::load(__DIR__ . '/../.env');

try {
    echo "[" . date('Y-m-d H:i:s') . "] Iniciando atualização de empréstimos atrasados...\n";
    
    $borrowModel = new BorrowModel();
    $result = $borrowModel->updateOverdueStatus();
    
    if ($result['success']) {
        echo "[" . date('Y-m-d H:i:s') . "] Sucesso: {$result['message']}\n";
        echo "[" . date('Y-m-d H:i:s') . "] Empréstimos atualizados: {$result['updated_count']}\n";
    } else {
        echo "[" . date('Y-m-d H:i:s') . "] Erro: {$result['message']}\n";
        exit(1);
    }
    
    echo "[" . date('Y-m-d H:i:s') . "] Atualização concluída.\n";
    
} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] Erro fatal: " . $e->getMessage() . "\n";
    exit(1);
}
