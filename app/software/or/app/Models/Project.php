<?php
namespace App\Software\Or\Models;
use App\Software\Or\Core\Model;

class Project extends Model
{
    protected $table = 'projects';

    public function getWithType($userId = null)
    {
        $t  = $this->getTableName();
        $pt = $this->tablePrefix . 'problem_types'; // یا 'or_problem_types' بسته به تنظیمات Core
        $m  = $this->tablePrefix . 'methods';
        
        $sql = "SELECT p.*, pt.name_fa AS problem_type_name, pt.code AS problem_type_code,
                       m.name_fa AS method_name, m.code AS method_code
                FROM {$t} p
                LEFT JOIN {$pt} pt ON p.problem_type_id = pt.id
                LEFT JOIN {$m} m ON p.method_id = m.id";
        $params = [];
        if ($userId) { $sql .= " WHERE p.user_id = ?"; $params[] = $userId; }
        $sql .= " ORDER BY p.updated_at DESC";
        return $this->query($sql, $params);
    }

    /**
     * دریافت پروژه‌ها بر اساس کد نوع مسئله (مثل TRANS, ASSIGN, LP)
     */
    public function getByProblemTypeCode($code, $userId = null)
    {
        $t  = $this->getTableName(); // اینجا مجاز است چون درون کلاس Model هستیم
        $pt = $this->tablePrefix . 'problem_types';
        
        $sql = "SELECT p.*, pt.name_fa AS problem_type_name, pt.code AS problem_type_code
                FROM {$t} p
                LEFT JOIN {$pt} pt ON p.problem_type_id = pt.id
                WHERE pt.code = ?";
        $params = [$code];
        
        if ($userId) {
            $sql .= " AND p.user_id = ?";
            $params[] = $userId;
        }
        $sql .= " ORDER BY p.updated_at DESC";
        
        return $this->query($sql, $params);
    }

    public function getWithDetails($id)
    {
        $t  = $this->getTableName();
        $pt = $this->tablePrefix . 'problem_types';
        $m  = $this->tablePrefix . 'methods';
        return $this->queryOne("SELECT p.*, pt.name_fa AS problem_type_name, pt.code AS problem_type_code,
                m.name_fa AS method_name, m.code AS method_code
                FROM {$t} p
                LEFT JOIN {$pt} pt ON p.problem_type_id = pt.id
                LEFT JOIN {$m} m ON p.method_id = m.id
                WHERE p.id = ?", [$id]);
    }

    // ---- Nodes ----
    public function getNodes($pid, $type = null)
    {
        $t = $this->tablePrefix . 'project_nodes';
        $sql = "SELECT * FROM {$t} WHERE project_id = ?"; $params = [$pid];
        if ($type) { $sql .= " AND type = ?"; $params[] = $type; }
        $sql .= " ORDER BY sort_order ASC, id ASC";
        return $this->query($sql, $params);
    }
    public function getSources($pid)      { return $this->getNodes($pid, 'source'); }
    public function getDestinations($pid) { return $this->getNodes($pid, 'destination'); }

    public function addNode($pid, $type, $name, $capacity, $sortOrder = 0)
    {
        $t = $this->tablePrefix . 'project_nodes';
        $this->query("INSERT INTO {$t} (project_id, type, name, capacity, sort_order) VALUES (?,?,?,?,?)",
            [$pid, $type, $name, $capacity, $sortOrder]);        
        return $this->queryOne("SELECT LAST_INSERT_ID() as id")['id'];
    }

    public function deleteNode($nodeId, $pid)
    {
        return $this->query("DELETE FROM {$this->tablePrefix}project_nodes WHERE id=? AND project_id=?", [$nodeId, $pid]);
    }

    public function getSupplyDemand($pid)
    {
        $rows = $this->query("SELECT type, SUM(capacity) AS total FROM {$this->tablePrefix}project_nodes
            WHERE project_id=? AND type IN ('source','destination') GROUP BY type", [$pid]);
        $r = ['supply' => 0, 'demand' => 0];
        foreach ($rows as $row) {
            if ($row['type'] === 'source')      $r['supply'] = (int)$row['total'];
            if ($row['type'] === 'destination') $r['demand'] = (int)$row['total'];
        }
        return $r;
    }

    // ---- Edges ----
    public function getEdges($pid)
    {
        return $this->query("SELECT * FROM {$this->tablePrefix}project_edges WHERE project_id=? ORDER BY id ASC", [$pid]);
    }

    public function getCostMatrix($pid)
    {
        $srcs  = $this->getSources($pid);
        $dsts  = $this->getDestinations($pid);
        $edges = $this->getEdges($pid);
        $si = array_flip(array_column($srcs, 'id'));
        $di = array_flip(array_column($dsts, 'id'));
        $n = count($srcs); $m = count($dsts);
        $matrix   = array_fill(0, $n, array_fill(0, $m, null));
        $prohib   = array_fill(0, $n, array_fill(0, $m, false));
        foreach ($edges as $e) {
            $i = $si[$e['source_id']] ?? null;
            $j = $di[$e['destination_id']] ?? null;
            if ($i !== null && $j !== null) {
                $matrix[$i][$j] = ($e['cost'] !== null) ? (float)$e['cost'] : null;
                $prohib[$i][$j] = (bool)$e['is_prohibited'];
            }
        }
        return ['matrix'=>$matrix, 'prohibited'=>$prohib, 'sources'=>$srcs, 'dests'=>$dsts];
    }

    public function setEdge($pid, $srcId, $dstId, $cost, $isProhib = 0)
    {
        $t = $this->tablePrefix . 'project_edges';
        return $this->query("INSERT INTO {$t} (project_id, source_id, destination_id, cost, is_prohibited)
            VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE cost=VALUES(cost), is_prohibited=VALUES(is_prohibited)",
            [$pid, $srcId, $dstId, $cost, $isProhib]);
    }

    // ---- Allocations ----
    public function saveAllocations($pid, array $allocs)
    {
        $t = $this->tablePrefix . 'project_allocations';
        $this->query("DELETE FROM {$t} WHERE project_id=?", [$pid]);
        foreach ($allocs as $a) {
            $this->query("INSERT INTO {$t} (project_id, source_id, destination_id, allocated_amount, unit_cost, total_cost, is_basic_cell)
                VALUES (?,?,?,?,?,?,?)",
                [$pid, $a['source_id'], $a['destination_id'], $a['allocated_amount'],
                 $a['unit_cost'], $a['total_cost'], $a['is_basic_cell'] ?? 0]);
        }
        return true;
    }

    public function getAllocations($pid)
    {
        return $this->query("SELECT * FROM {$this->tablePrefix}project_allocations WHERE project_id=? ORDER BY id ASC", [$pid]);
    }

    // ---- Results ----
    public function saveResult($pid, $totalCost, $iterations, $status)
    {
        $t = $this->tablePrefix . 'project_results';
        $this->query("DELETE FROM {$t} WHERE project_id=?", [$pid]);
        return $this->query("INSERT INTO {$t} (project_id, total_cost, iterations_count, solution_status) VALUES (?,?,?,?)",
            [$pid, $totalCost, $iterations, $status]);
    }

    public function getResult($pid)
    {
        return $this->queryOne("SELECT * FROM {$this->tablePrefix}project_results WHERE project_id=? ORDER BY id DESC LIMIT 1", [$pid]);
    }

    public function updateStatus($pid, $status)
    {
        return $this->update($pid, ['status' => $status]);
    }

    public function updateBalance($pid, $isBalanced, $totalSupply, $totalDemand)
    {
        return $this->update($pid, ['is_balanced'=>$isBalanced, 'total_supply'=>$totalSupply, 'total_demand'=>$totalDemand]);
    }

    /**
     * دریافت تمام پروژه‌های حل‌شده کاربر (برای ماژول تحلیل حساسیت)
     */
    public function getSolvedProjects($userId)
    {
        $t = $this->getTableName(); // اینجا مجاز است چون درون کلاس Model هستیم
        $pt = $this->tablePrefix . 'problem_types';
        
        return $this->query("SELECT p.*, pt.name_fa AS problem_type_name, pt.code AS problem_type_code
                 FROM {$t} p
                 LEFT JOIN {$pt} pt ON p.problem_type_id = pt.id
                 WHERE p.user_id = ? AND p.status = 'solved'
                 ORDER BY p.updated_at DESC", [$userId]);
    }

    /**
     * شمارش کل پروژه‌های یک کاربر
     */
    public function countByUser($userId)
    {
        $t = $this->getTableName();
        $result = $this->query("SELECT COUNT(*) AS cnt FROM {$t} WHERE user_id = ?", [$userId]);
        return (int)($result[0]['cnt'] ?? 0);
    }

    /**
     * شمارش پروژه‌های یک کاربر با وضعیت مشخص
     */
    public function countByUserAndStatus($userId, $status)
    {
        $t = $this->getTableName();
        $result = $this->query(
            "SELECT COUNT(*) AS cnt FROM {$t} WHERE user_id = ? AND status = ?",
            [$userId, $status]
        );
        return (int)($result[0]['cnt'] ?? 0);
    }

    /**
     * دریافت آخرین پروژه‌های حل‌شده کاربر با اطلاعات نوع مسئله
     */
    public function getRecentSolvedProjects($userId, $limit = 5)
    {
        $t = $this->getTableName();
        $pt = $this->tablePrefix . 'problem_types';
        
        return $this->query(
            "SELECT p.*, pt.name_fa AS problem_type_name, pt.code AS problem_type_code
             FROM {$t} p
             LEFT JOIN {$pt} pt ON p.problem_type_id = pt.id
             WHERE p.user_id = ? AND p.status = 'solved'
             ORDER BY p.updated_at DESC
             LIMIT ?",
            [$userId, (int)$limit]
        );
    }
    
    /**
     * دریافت تمام پروژه‌ها به همراه نام نوع مسئله
     */
    public function getAllWithProblemType()
    {
        $t  = $this->getTableName();
        $pt = $this->tablePrefix . 'problem_types';
        
        $sql = "SELECT p.id, p.name, p.status, p.optimal_value, p.created_at, pt.name_fa AS problem_type_name
                FROM {$t} p
                LEFT JOIN {$pt} pt ON p.problem_type_id = pt.id
                ORDER BY p.created_at DESC";
        
        return $this->query($sql);
    }

    /**
     * دریافت یک پروژه به همراه نام نوع مسئله
     */
    public function getByIdWithProblemType($id)
    {
        $t  = $this->getTableName();
        $pt = $this->tablePrefix . 'problem_types';
        
        $sql = "SELECT p.*, pt.name_fa AS problem_type_name 
                FROM {$t} p 
                LEFT JOIN {$pt} pt ON p.problem_type_id = pt.id 
                WHERE p.id = ?";
        
        return $this->queryOne($sql, [$id]);
    }
}