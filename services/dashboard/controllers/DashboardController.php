<?php
/**
 * Dashboard Controller - Serviço de Dashboard e Estatísticas
 */

// Include the View utility
require_once __DIR__ . '/../../utils/View.php';

class DashboardController {
    private $statsModel;
    
    public function __construct() {
        $this->statsModel = new StatsModel();
        
        // Set the base path for views
        View::setBasePath(__DIR__ . '/../views/');
    }
    
    /**
     * Renderizar view de dashboard
     */
    public function showDashboard() {
        $stats = $this->statsModel->getGeneralStats();
        
        $data = [
            'title' => 'Dashboard Service - Virtual Library',
            'stats' => $stats
        ];
        
        // Render the view
        View::display('dashboard', $data);
    }
}
