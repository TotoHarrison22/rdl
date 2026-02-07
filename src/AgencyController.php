<?php
require_once __DIR__ . '/../config/database.php';

class AgencyController {
    private $pdo;

    public function __construct() {
        $this->pdo = getDB();
    }

    public function searchAgencies($query) {
        $stmt = $this->pdo->prepare("
            SELECT id_agencia, nombre_agencia, sede, distrito, zona, estado, nivel_accesibilidad
            FROM agencias
            WHERE nombre_agencia LIKE ? OR distrito LIKE ? OR zona LIKE ?
        ");
        $term = "%$query%";
        $stmt->execute([$term, $term, $term]);
        return $stmt->fetchAll();
    }

    public function getAgencyDetails($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM agencias WHERE id_agencia = ?");
        $stmt->execute([$id]);
        $agency = $stmt->fetch();

        if (!$agency) return null;

        // Compute KPIs
        // 1. Avg Wait Time
        $stmtWait = $this->pdo->prepare("SELECT AVG(tiempo_espera_minutos) as avg_wait FROM visitas_agencia WHERE id_agencia = ?");
        $stmtWait->execute([$id]);
        $agency['avg_wait_time'] = round($stmtWait->fetchColumn(), 0);

        // 2. Incidents
        $stmtInc = $this->pdo->prepare("SELECT COUNT(*) FROM incidencias i JOIN visitas_agencia v ON i.id_visita = v.id_visita WHERE v.id_agencia = ?");
        $stmtInc->execute([$id]);
        $agency['incident_count'] = $stmtInc->fetchColumn();

        // 3. Efficiency Rate
        $stmtEff = $this->pdo->prepare("SELECT COUNT(*) FROM visitas_agencia WHERE id_agencia = ? AND eficiencia_envio = 1");
        $stmtEff->execute([$id]);
        $efficient_count = $stmtEff->fetchColumn();

        $stmtTotal = $this->pdo->prepare("SELECT COUNT(*) FROM visitas_agencia WHERE id_agencia = ?");
        $stmtTotal->execute([$id]);
        $total_visits = $stmtTotal->fetchColumn();

        $agency['efficiency_rate'] = ($total_visits > 0) ? round(($efficient_count / $total_visits) * 100, 1) : 0;

        // 4. Recent Conditions
        $stmtCond = $this->pdo->prepare("
            SELECT DISTINCT c.tipo_condicion, c.nivel_impacto
            FROM condiciones_acceso c
            JOIN visitas_agencia v ON c.id_visita = v.id_visita
            WHERE v.id_agencia = ?
            ORDER BY v.id_visita DESC
            LIMIT 5
        ");
        $stmtCond->execute([$id]);
        $agency['recent_conditions'] = $stmtCond->fetchAll();

        return $agency;
    }
}
