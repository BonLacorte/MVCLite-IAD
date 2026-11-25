<?php
class QuestionnaireModel extends Model
{
    public function __construct()
    {
        parent::__construct('default');
    }

    public function getAllByType($type = 'general')
    {
        $stmt = $this->db->prepare("
            SELECT q.*,
                   COALESCE(question_counts.total_questions, 0) AS total_questions,
                   COALESCE(respondent_counts.total_respondents, 0) AS total_respondents,
                   COALESCE(question_counts.total_questions, 0) * COALESCE(respondent_counts.total_respondents, 0) AS expected_answer_slots,
                   COALESCE(answer_counts.submitted_answer_slots, 0) AS submitted_answer_slots,
                   CASE
                       WHEN COALESCE(question_counts.total_questions, 0) * COALESCE(respondent_counts.total_respondents, 0) > 0
                       THEN ROUND(
                           COALESCE(answer_counts.submitted_answer_slots, 0) * 100.0 /
                           (COALESCE(question_counts.total_questions, 0) * COALESCE(respondent_counts.total_respondents, 0)),
                           1
                       )
                       ELSE NULL
                   END AS completion_percent
            FROM questionnaires q
            LEFT JOIN (
                SELECT questionnaire_id, COUNT(*) AS total_questions
                FROM questions
                WHERE is_deleted = 0
                GROUP BY questionnaire_id
            ) question_counts ON question_counts.questionnaire_id = q.id
            LEFT JOIN (
                SELECT questionnaire_id, COUNT(DISTINCT user_id) AS total_respondents
                FROM user_questionnaires
                WHERE is_deleted = 0
                GROUP BY questionnaire_id
            ) respondent_counts ON respondent_counts.questionnaire_id = q.id
            LEFT JOIN (
                SELECT latest_answers.questionnaire_id,
                       SUM(CASE
                           WHEN latest_answers.status IN ('for review', 'pending', 'returned', 'accepted', 'rejected')
                           THEN 1 ELSE 0
                       END) AS submitted_answer_slots
                FROM (
                    SELECT ua.questionnaire_id, ua.user_id, ua.question_id, ua.status
                    FROM user_answers ua
                    INNER JOIN (
                        SELECT questionnaire_id, user_id, question_id, MAX(id) AS latest_id
                        FROM user_answers
                        WHERE is_deleted = 0
                        GROUP BY questionnaire_id, user_id, question_id
                    ) latest_ids ON latest_ids.latest_id = ua.id
                    WHERE ua.is_deleted = 0
                ) latest_answers
                GROUP BY latest_answers.questionnaire_id
            ) answer_counts ON answer_counts.questionnaire_id = q.id
            WHERE q.is_deleted = 0 AND q.type = :type
            ORDER BY q.created_at DESC
        ");
        $stmt->execute(['type' => $type]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllQuestionnaires($limit = 10, $offset = 0, $sort = 'created_at', $order = 'DESC', $userId = 0, $search = '')
    {
        $allowedSort = ['title', 'type', 'created_at', 'status', 'completion_percent'];
        $sort = in_array($sort, $allowedSort, true) ? $sort : 'created_at';
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        $where = "q.is_deleted = 0 AND LOWER(q.status) = 'active' AND uq.user_id = :userid AND uq.is_deleted = 0";
        $params = [':userid' => (int)$userId];

        if (!empty($search)) {
            $where .= " AND (q.title LIKE :search OR q.description LIKE :search OR q.type LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $completionSubquery = "
            SELECT COALESCE((COUNT(DISTINCT ua.question_id) * 100.0 / NULLIF(COUNT(DISTINCT qu.id), 0)), 0)
            FROM questions qu
            JOIN sections s ON s.id = qu.section_id
            LEFT JOIN user_answers ua ON ua.question_id = qu.id AND ua.user_id = :userid AND ua.is_deleted = 0
            WHERE s.questionnaire_id = q.id AND qu.is_deleted = 0 AND s.is_deleted = 0
        ";

        $stmt = $this->db->prepare("
            SELECT q.*,
                   (SELECT COUNT(*) FROM questions WHERE questionnaire_id = q.id AND is_deleted = 0) as total_questions,
                   ($completionSubquery) as completion_percent
            FROM questionnaires q
            INNER JOIN user_questionnaires uq ON uq.questionnaire_id = q.id
            WHERE $where
            ORDER BY $sort $order
            LIMIT :limit OFFSET :offset
        ");

        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll($search = '', $userId = 0)
    {
        $where = "q.is_deleted = 0 AND LOWER(q.status) = 'active' AND uq.user_id = :userid AND uq.is_deleted = 0";
        $params = [':userid' => (int)$userId];
        if (!empty($search)) {
            $where .= " AND (q.title LIKE :search OR q.description LIKE :search OR q.type LIKE :search)";
            $params[':search'] = "%$search%";
        }
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT q.id)
            FROM questionnaires q
            INNER JOIN user_questionnaires uq ON uq.questionnaire_id = q.id
            WHERE $where
        ");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM questionnaires WHERE id = :id AND is_deleted = 0");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getQuestionnaireById($id)
    {
        return $this->getById($id);
    }

    public function getQuestionsByQuestionnaireId($questionnaireId)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM questions 
            WHERE questionnaire_id = :qid AND is_deleted = 0 
            ORDER BY id ASC
        ");
        $stmt->execute(['qid' => $questionnaireId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("INSERT INTO questionnaires (title, description, type, status, created_by, parent_questionnaire_id) 
            VALUES (:title, :description, :type, :status, :created_by, :parent_questionnaire_id)");
        return $stmt->execute($data);
    }

    public function update($id, $data)
    {
        $stmt = $this->db->prepare("UPDATE questionnaires SET 
            title = :title,
            description = :description,
            type = :type,
            status = :status,
            parent_questionnaire_id = :parent_questionnaire_id,
            edited_at = NOW()
            WHERE id = :id AND is_deleted = 0");
        $data['id'] = $id;
        return $stmt->execute($data);
    }

    public function softDelete($id)
    {
        $stmt = $this->db->prepare("UPDATE questionnaires SET is_deleted = 1 WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getUserForPdf(int $userId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, name, position, department_office_unit, for_central_office, for_region_center
            FROM users
            WHERE id = :id AND is_deleted = 0
            LIMIT 1
        ");
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }
    public function getCompletionStats($questionnaireId)
    {
        // Get total questions in this questionnaire
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total_questions FROM questions q
            JOIN sections s ON s.id = q.section_id
            WHERE s.questionnaire_id = :qid AND q.is_deleted = 0
        ");
        $stmt->execute(['qid' => $questionnaireId]);
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total_questions'] ?? 0;

        // Get answered questions
        $stmt2 = $this->db->prepare("
            SELECT COUNT(DISTINCT ua.question_id) as answered FROM user_answers ua
            JOIN questions q ON q.id = ua.question_id
            JOIN sections s ON s.id = q.section_id
            WHERE s.questionnaire_id = :qid AND ua.is_deleted = 0
        ");
        $stmt2->execute(['qid' => $questionnaireId]);
        $answered = $stmt2->fetch(PDO::FETCH_ASSOC)['answered'] ?? 0;

        return $total > 0 ? round(($answered / $total) * 100, 1) : 0;
    }

    public function getUserStats($userId, $search = '')
    {
        $where = "q.is_deleted = 0 AND LOWER(q.status) = 'active'";
        $params = [':userid' => (int)$userId];

        if (!empty($search)) {
            $where .= " AND (q.title LIKE :search OR q.description LIKE :search OR q.type LIKE :search)";
            $params[':search'] = "%$search%";
        }

        // This query calculates completion percentage per questionnaire for the user
        // then aggregates them into stats.
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_active,
                SUM(CASE WHEN completion_percent >= 100 THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN completion_percent < 100 THEN 1 ELSE 0 END) as in_progress,
                COALESCE(AVG(completion_percent), 0) as average_completion
            FROM (
                SELECT 
                    q.id,
                    COALESCE((
                        SELECT ROUND(COUNT(DISTINCT ua.question_id) * 100.0 / NULLIF(COUNT(DISTINCT qu.id), 0), 1)
                        FROM questions qu
                        JOIN sections s ON s.id = qu.section_id
                        LEFT JOIN user_answers ua ON ua.question_id = qu.id AND ua.user_id = :userid AND ua.is_deleted = 0
                        WHERE s.questionnaire_id = q.id AND qu.is_deleted = 0 AND s.is_deleted = 0
                    ), 0) as completion_percent
                FROM questionnaires q
                INNER JOIN user_questionnaires uq ON uq.questionnaire_id = q.id AND uq.user_id = :userid AND uq.is_deleted = 0
                WHERE $where
            ) as stats
        ");
        
        $stmt->execute($params);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_active' => (int)($stats['total_active'] ?? 0),
            'completed' => (int)($stats['completed'] ?? 0),
            'in_progress' => (int)($stats['in_progress'] ?? 0),
            'average_completion' => round((float)($stats['average_completion'] ?? 0), 1)
        ];
    }

    public function getAssignedUsers($questionnaireId)
    {
        // First get total questions in this questionnaire
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM questions q
            JOIN sections s ON s.id = q.section_id
            WHERE s.questionnaire_id = :qid AND q.is_deleted = 0 AND s.is_deleted = 0
        ");
        $stmt->execute(['qid' => $questionnaireId]);
        $totalQuestions = (int)$stmt->fetchColumn();

        // Get the assigned users
        $stmt = $this->db->prepare("
            SELECT u.*, uq.assigned_at
            FROM user_questionnaires uq
            JOIN users u ON u.id = uq.user_id
            WHERE uq.questionnaire_id = :qid AND uq.is_deleted = 0 AND u.is_deleted = 0
            ORDER BY u.name ASC
        ");
        $stmt->execute(['qid' => $questionnaireId]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($users as &$user) {
            if ($totalQuestions === 0) {
                $user['user_completion'] = 0;
                $user['answered_questions'] = 0;
                $user['total_questions'] = 0;
                continue;
            }

            // Get answered questions (accepted status)
            $stmtAns = $this->db->prepare("
                SELECT COUNT(DISTINCT ua.question_id) FROM user_answers ua
                JOIN questions q ON q.id = ua.question_id
                JOIN sections s ON s.id = q.section_id
                WHERE ua.user_id = :uid 
                  AND s.questionnaire_id = :qid 
                  AND ua.status = 'accepted' 
                  AND ua.is_deleted = 0
                  AND q.is_deleted = 0
                  AND s.is_deleted = 0
            ");
            $stmtAns->execute([
                'uid' => $user['id'],
                'qid' => $questionnaireId
            ]);
            $answered = (int)$stmtAns->fetchColumn();

            $user['user_completion'] = round(($answered / $totalQuestions) * 100);
            $user['answered_questions'] = $answered;
            $user['total_questions'] = $totalQuestions;
        }

        return $users;
    }

    public function getUnassignedUsers($questionnaireId)
    {
        $stmt = $this->db->prepare("
            SELECT id, name, email, department_office_unit, position, cellphone_number 
            FROM users 
            WHERE role = 'user' 
              AND is_deleted = 0 
              AND id NOT IN (
                  SELECT user_id 
                  FROM user_questionnaires 
                  WHERE questionnaire_id = :qid 
                    AND is_deleted = 0
              )
            ORDER BY name ASC
        ");
        $stmt->execute(['qid' => $questionnaireId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDepartmentProgress($usersAssigned)
    {
        $departmentProgress = [];
        foreach ($usersAssigned as $user) {
            $department = $user['department_office_unit'];
            if (empty($department)) {
                $department = 'Unassigned Department';
            }
            if (!isset($departmentProgress[$department])) {
                $departmentProgress[$department] = [
                    'name' => $department,
                    'total_users' => 0,
                    'total_completion' => 0,
                    'average_completion' => 0
                ];
            }
            $departmentProgress[$department]['total_users']++;
            $departmentProgress[$department]['total_completion'] += $user['user_completion'];
        }
        foreach ($departmentProgress as $dept => $data) {
            if ($data['total_users'] > 0) {
                $departmentProgress[$dept]['average_completion'] = round($data['total_completion'] / $data['total_users']);
            }
        }
        return array_values($departmentProgress);
    }

    public function assignUsers($questionnaireId, $userIds, $assignedBy)
    {
        try {
            $this->db->beginTransaction();

            foreach ($userIds as $userId) {
                $userId = (int)$userId;
                // Check if the user is already assigned to the questionnaire
                $stmtCheck = $this->db->prepare("
                    SELECT * FROM user_questionnaires 
                    WHERE user_id = :user_id AND questionnaire_id = :questionnaire_id
                ");
                $stmtCheck->execute([
                    'user_id' => $userId,
                    'questionnaire_id' => $questionnaireId
                ]);
                $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

                if ($existing) {
                    if ($existing['is_deleted'] == 1) {
                        // Reactivate assignment
                        $stmtUpdate = $this->db->prepare("
                            UPDATE user_questionnaires 
                            SET is_deleted = 0, assigned_by = :assigned_by, assigned_at = NOW() 
                            WHERE user_id = :user_id AND questionnaire_id = :questionnaire_id
                        ");
                        $stmtUpdate->execute([
                            'assigned_by' => $assignedBy,
                            'user_id' => $userId,
                            'questionnaire_id' => $questionnaireId
                        ]);

                        // Reactivate user answers
                        $stmtAns = $this->db->prepare("
                            UPDATE user_answers 
                            SET is_deleted = 0 
                            WHERE user_id = :user_id AND questionnaire_id = :questionnaire_id
                        ");
                        $stmtAns->execute([
                            'user_id' => $userId,
                            'questionnaire_id' => $questionnaireId
                        ]);

                        // Reactivate file uploads
                        $stmtFiles = $this->db->prepare("
                            UPDATE file_uploads 
                            SET is_deleted = 0 
                            WHERE user_id = :user_id AND questionnaire_id = :questionnaire_id
                        ");
                        $stmtFiles->execute([
                            'user_id' => $userId,
                            'questionnaire_id' => $questionnaireId
                        ]);
                    }
                } else {
                    // Create new assignment
                    $stmtInsert = $this->db->prepare("
                        INSERT INTO user_questionnaires (user_id, questionnaire_id, assigned_by, assigned_at) 
                        VALUES (:user_id, :questionnaire_id, :assigned_by, NOW())
                    ");
                    $stmtInsert->execute([
                        'user_id' => $userId,
                        'questionnaire_id' => $questionnaireId,
                        'assigned_by' => $assignedBy
                    ]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function unassignUsers($questionnaireId, $userIds)
    {
        try {
            $this->db->beginTransaction();

            foreach ($userIds as $userId) {
                $userId = (int)$userId;
                // Soft delete assignment
                $stmtUpdate = $this->db->prepare("
                    UPDATE user_questionnaires 
                    SET is_deleted = 1 
                    WHERE user_id = :user_id AND questionnaire_id = :questionnaire_id
                ");
                $stmtUpdate->execute([
                    'user_id' => $userId,
                    'questionnaire_id' => $questionnaireId
                ]);

                // Soft delete user answers
                $stmtAns = $this->db->prepare("
                    UPDATE user_answers 
                    SET is_deleted = 1 
                    WHERE user_id = :user_id AND questionnaire_id = :questionnaire_id
                ");
                $stmtAns->execute([
                    'user_id' => $userId,
                    'questionnaire_id' => $questionnaireId
                ]);

                // Soft delete file uploads
                $stmtFiles = $this->db->prepare("
                    UPDATE file_uploads 
                    SET is_deleted = 1 
                    WHERE user_id = :user_id AND questionnaire_id = :questionnaire_id
                ");
                $stmtFiles->execute([
                    'user_id' => $userId,
                    'questionnaire_id' => $questionnaireId
                ]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getCheckStatistics($questionnaireId, $userId = null)
    {
        $acceptedCount = 0;
        $rejectedCount = 0;
        $pendingCount = 0;
        $noAnswerCount = 0;

        // Get all questions
        $stmtQ = $this->db->prepare("
            SELECT q.id FROM questions q
            JOIN sections s ON s.id = q.section_id
            WHERE s.questionnaire_id = :qid AND q.is_deleted = 0 AND s.is_deleted = 0
        ");
        $stmtQ->execute(['qid' => $questionnaireId]);
        $questions = $stmtQ->fetchAll(PDO::FETCH_ASSOC);

        $userCondition = $userId !== null ? "AND user_id = :uid" : "";

        foreach ($questions as $q) {
            $sql = "
                SELECT status FROM user_answers 
                WHERE question_id = :qid $userCondition AND status IN ('accepted', 'rejected', 'pending') AND is_deleted = 0 
                ORDER BY submitted_at DESC LIMIT 1
            ";
            $stmtAns = $this->db->prepare($sql);
            $params = ['qid' => $q['id']];
            if ($userId !== null) {
                $params['uid'] = $userId;
            }
            $stmtAns->execute($params);
            $ans = $stmtAns->fetch(PDO::FETCH_ASSOC);

            if ($ans) {
                switch ($ans['status']) {
                    case 'accepted': $acceptedCount++; break;
                    case 'rejected': $rejectedCount++; break;
                    case 'pending': $pendingCount++; break;
                }
            } else {
                $noAnswerCount++;
            }
        }

        return [
            'accepted' => $acceptedCount,
            'rejected' => $rejectedCount,
            'pending' => $pendingCount,
            'no_answer' => $noAnswerCount
        ];
    }

    public function getSectionsTreeWithAnswers($parentId, $questionnaireId, $userId = null)
    {
        $stmtS = $this->db->prepare("
            SELECT * FROM sections 
            WHERE parent_section_id <=> :parent_id 
              AND questionnaire_id = :qid 
              AND is_deleted = 0
            ORDER BY id ASC
        ");
        $stmtS->execute([
            'parent_id' => $parentId,
            'qid' => $questionnaireId
        ]);
        $sections = $stmtS->fetchAll(PDO::FETCH_ASSOC);

        $userConditionA = $userId !== null ? "AND ua.user_id = :uid" : "";
        $userJoinA = $userId === null ? "LEFT JOIN users u ON ua.user_id = u.id" : "";
        $userSelectA = $userId === null ? ", u.name as submitter_name" : "";

        $userConditionF = $userId !== null ? "AND fu.user_id = :uid" : "";
        $userJoinF = $userId === null ? "LEFT JOIN users u ON fu.user_id = u.id" : "";
        $userSelectF = $userId === null ? ", u.name as submitter_name" : "";

        foreach ($sections as &$section) {
            // Recursive fetch of subsections
            $section['subsections'] = $this->getSectionsTreeWithAnswers($section['id'], $questionnaireId, $userId);

            // Fetch questions in this section
            $stmtQ = $this->db->prepare("
                SELECT * FROM questions 
                WHERE section_id = :sid AND is_deleted = 0
                ORDER BY id ASC
            ");
            $stmtQ->execute(['sid' => $section['id']]);
            $questions = $stmtQ->fetchAll(PDO::FETCH_ASSOC);

            foreach ($questions as &$question) {
                // Fetch answers with status accepted, rejected, pending
                $sqlA = "
                    SELECT ua.* $userSelectA 
                    FROM user_answers ua
                    $userJoinA
                    WHERE ua.question_id = :qid $userConditionA AND ua.status IN ('accepted', 'rejected', 'pending', 'for review', 'returned') AND ua.is_deleted = 0 
                    ORDER BY ua.submitted_at DESC
                ";
                $stmtAns = $this->db->prepare($sqlA);
                $params = ['qid' => $question['id']];
                if ($userId !== null) {
                    $params['uid'] = $userId;
                }
                $stmtAns->execute($params);
                $answers = $stmtAns->fetchAll(PDO::FETCH_ASSOC);

                // Fetch uploaded files
                $sqlF = "
                    SELECT fu.* $userSelectF 
                    FROM file_uploads fu
                    $userJoinF
                    WHERE fu.question_id = :qid $userConditionF AND fu.status IN ('accepted', 'rejected', 'pending', 'for review', 'returned') AND fu.is_deleted = 0 
                    ORDER BY fu.submitted_at DESC
                ";
                $stmtFiles = $this->db->prepare($sqlF);
                $stmtFiles->execute($params);
                $uploadedFiles = $stmtFiles->fetchAll(PDO::FETCH_ASSOC);

                $question['uploaded_files'] = $uploadedFiles;

                // Merge uploaded files into corresponding answers
                foreach ($answers as &$answer) {
                    $answer['uploaded_files'] = [];
                    foreach ($uploadedFiles as $file) {
                        if ($file['user_answer_id'] == $answer['id']) {
                            $answer['uploaded_files'][] = $file;
                        }
                    }
                }

                $question['answers'] = $answers;

                if (empty($answers)) {
                    $question['latest_answer_status_yes_no'] = 'no answer';
                } else {
                    $question['latest_answer_status_yes_no'] = $answers[0]['status'];
                }

                if (empty($uploadedFiles)) {
                    $question['latest_answer_status_file_upload'] = 'no file uploaded';
                } else {
                    $question['latest_answer_status_file_upload'] = $uploadedFiles[0]['status'];
                }

                $question['number_of_rows'] = count($answers) + count($uploadedFiles);
            }

            $section['questions'] = $questions;
        }

        return $sections;
    }

    public function evaluateAnswer($table, $id, $status, $remark, $checkedBy)
    {
        if (!in_array($table, ['user_answers', 'file_uploads'])) {
            return false;
        }

        $renamedFiles = [];

        try {
            $this->db->beginTransaction();

            if ($table === 'file_uploads') {
                $stmtFile = $this->db->prepare("
                    SELECT *
                    FROM file_uploads
                    WHERE id = :id
                      AND is_deleted = 0
                    LIMIT 1
                ");
                $stmtFile->execute(['id' => $id]);
                $file = $stmtFile->fetch(PDO::FETCH_ASSOC);

                if (!$file) {
                    throw new \RuntimeException('File upload not found.');
                }

                $this->transitionFileUploadRow(
                    $file,
                    $status,
                    [
                        'remark' => $remark,
                        'checked_by' => $checkedBy,
                        'checked_at_now' => true,
                    ],
                    $renamedFiles
                );
            } else {
                $stmt = $this->db->prepare("
                    UPDATE user_answers
                    SET status = :status, remark = :remark, checked_at = NOW(), checked_by = :checked_by
                    WHERE id = :id
                      AND is_deleted = 0
                ");
                $stmt->execute([
                    'status' => $status,
                    'remark' => $remark,
                    'checked_by' => $checkedBy,
                    'id' => $id,
                ]);

                if ($stmt->rowCount() !== 1) {
                    throw new \RuntimeException('Answer not found or not updated.');
                }

                $this->transitionFileUploadsForAnswer(
                    (int)$id,
                    $status,
                    [
                        'remark' => $remark,
                        'checked_by' => $checkedBy,
                        'checked_at_now' => true,
                    ],
                    $renamedFiles
                );
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->rollbackRenamedAnswerFiles($renamedFiles);
            return false;
        }
    }

    public function getReviewableFileUploadById(int $fileId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT fu.*
            FROM file_uploads fu
            INNER JOIN questions q
                ON q.id = fu.question_id
                AND q.questionnaire_id = fu.questionnaire_id
                AND q.is_deleted = 0
            INNER JOIN questionnaires qq
                ON qq.id = fu.questionnaire_id
                AND qq.is_deleted = 0
            WHERE fu.id = :id
              AND fu.is_deleted = 0
              AND fu.status IN ('pending', 'accepted', 'rejected', 'for review', 'returned')
            LIMIT 1
        ");
        $stmt->execute(['id' => $fileId]);
        $file = $stmt->fetch(PDO::FETCH_ASSOC);

        return $file ?: null;
    }

    public function getAccessibleUserFileUploadByName(int $userId, string $requestedFile): ?array
    {
        $safeName = basename(str_replace('\\', '/', $requestedFile));
        if ($safeName === '') {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT fu.*
            FROM file_uploads fu
            INNER JOIN user_questionnaires uq
                ON uq.user_id = fu.user_id
                AND uq.questionnaire_id = fu.questionnaire_id
                AND uq.is_deleted = 0
            INNER JOIN questions q
                ON q.id = fu.question_id
                AND q.questionnaire_id = fu.questionnaire_id
                AND q.is_deleted = 0
            INNER JOIN questionnaires qq
                ON qq.id = fu.questionnaire_id
                AND qq.is_deleted = 0
            WHERE fu.user_id = :uid
              AND fu.is_deleted = 0
              AND (
                  fu.file_path = :requested_file
                  OR fu.file_path = :safe_name
                  OR fu.file_path LIKE :slash_pattern
                  OR fu.file_path LIKE :backslash_pattern
              )
            ORDER BY fu.submitted_at DESC, fu.id DESC
        ");
        $stmt->execute([
            'uid' => $userId,
            'requested_file' => $requestedFile,
            'safe_name' => $safeName,
            'slash_pattern' => '%/' . $safeName,
            'backslash_pattern' => '%\\' . $safeName,
        ]);

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $file) {
            $storedName = basename(str_replace('\\', '/', (string)$file['file_path']));
            if ($storedName === $safeName) {
                return $file;
            }
        }

        return null;
    }

    public function countRespondents($questionnaireId)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT user_id) as total 
            FROM user_questionnaires 
            WHERE questionnaire_id = :qid AND is_deleted = 0
        ");
        $stmt->execute(['qid' => $questionnaireId]);
        return (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
    }

    public function countAcceptedAnswersForQuestionnaire($questionnaireId)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total 
            FROM user_answers 
            WHERE questionnaire_id = :qid AND status = 'accepted' AND is_deleted = 0
        ");
        $stmt->execute(['qid' => $questionnaireId]);
        return (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
    }

    public function countAcceptedAnswersForUser($questionnaireId, $userId)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total 
            FROM user_answers 
            WHERE questionnaire_id = :qid AND user_id = :uid AND status = 'accepted' AND is_deleted = 0
        ");
        $stmt->execute([
            'qid' => $questionnaireId,
            'uid' => $userId
        ]);
        return (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
    }

    public function getLastInsertId(): string
    {
        return $this->db->lastInsertId();
    }

    // =========================================================================
    // USER-FACING: Show Page
    // =========================================================================

    /**
     * Verify that a user is assigned to a questionnaire.
     */
    public function getAssignedQuestionnaire(int $questionnaireId, int $userId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT q.* 
            FROM questionnaires q
            INNER JOIN user_questionnaires uq ON q.id = uq.questionnaire_id
            WHERE q.id = :qid 
              AND uq.user_id = :uid 
              AND q.is_deleted = 0 
              AND uq.is_deleted = 0
        ");
        $stmt->execute(['qid' => $questionnaireId, 'uid' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Get flat question list with answer statuses — single query, no N+1.
     */
    public function getQuestionsWithAnswerStatus(int $questionnaireId, ?int $userId): array
    {
        $userCondition = $userId !== null ? "AND ua.user_id = :uid" : "";
        
        $sql = "
            SELECT 
                s.title AS section_title,
                q.id AS question_id,
                q.ref_no,
                q.question_text,
                q.type AS question_type,
                COALESCE(
                    (SELECT ua.status 
                     FROM user_answers ua 
                     WHERE ua.question_id = q.id 
                       $userCondition 
                       AND ua.is_deleted = 0 
                     ORDER BY ua.submitted_at DESC 
                     LIMIT 1),
                    'no answer'
                ) AS answer_status
            FROM questions q
            JOIN sections s ON s.id = q.section_id
            WHERE s.questionnaire_id = :qid 
              AND q.is_deleted = 0 
              AND s.is_deleted = 0
            ORDER BY s.id ASC, q.id ASC
        ";
        
        $stmt = $this->db->prepare($sql);
        $params = ['qid' => $questionnaireId];
        if ($userId !== null) {
            $params['uid'] = $userId;
        }
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get user-facing stats for the show page in a single query batch.
     */
    public function getUserShowStats(int $questionnaireId, ?int $userId): array
    {
        // Total questions
        $stmtTotal = $this->db->prepare("
            SELECT COUNT(*) FROM questions q
            JOIN sections s ON s.id = q.section_id
            WHERE s.questionnaire_id = :qid AND q.is_deleted = 0 AND s.is_deleted = 0
        ");
        $stmtTotal->execute(['qid' => $questionnaireId]);
        $totalQuestions = (int) $stmtTotal->fetchColumn();

        // User accepted answers
        $userCondition = $userId !== null ? "AND ua.user_id = :uid" : "";
        $sql = "
            SELECT COUNT(DISTINCT ua.question_id) FROM user_answers ua
            JOIN questions q ON q.id = ua.question_id
            JOIN sections s ON s.id = q.section_id
            WHERE s.questionnaire_id = :qid 
              $userCondition 
              AND ua.status = 'accepted' 
              AND ua.is_deleted = 0
              AND q.is_deleted = 0
              AND s.is_deleted = 0
        ";
        $stmtAccepted = $this->db->prepare($sql);
        $params = ['qid' => $questionnaireId];
        if ($userId !== null) {
            $params['uid'] = $userId;
        }
        $stmtAccepted->execute($params);
        $acceptedAnswers = (int) $stmtAccepted->fetchColumn();

        $completionRate = $totalQuestions > 0
            ? round(($acceptedAnswers / $totalQuestions) * 100, 2)
            : 0.0;

        return [
            'total_questions'  => $totalQuestions,
            'total_accepted'   => $acceptedAnswers,
            'completion_rate'  => $completionRate,
            'user_progress'    => $completionRate,
        ];
    }

    // =========================================================================
    // USER-FACING: Answer Page
    // =========================================================================

    /**
     * Build section tree for user answer page.
     * Batch-loads answers and files in 2 queries, then distributes in PHP.
     */
    public function getSectionsTreeForUser(?int $parentId, int $questionnaireId, ?int $userId, ?int $viewerUserId = null): array
    {
        // If this is the top-level call, batch-load all answers and files
        static $allAnswers = null;
        static $allFiles = null;
        static $allFilesByAnswerId = null;
        static $viewerAnswers = null;
        static $currentQid = null;
        static $currentViewerUserId = null;
        static $currentDataUserId = null;

        if ($parentId === null || $currentQid !== $questionnaireId || $currentViewerUserId !== $viewerUserId || $currentDataUserId !== $userId) {
            $currentQid = $questionnaireId;
            $currentViewerUserId = $viewerUserId;
            $currentDataUserId = $userId;

            $userCondition = $userId !== null ? "AND ua.user_id = :uid" : "";
            $userJoin = $userId === null ? "LEFT JOIN users u ON ua.user_id = u.id" : "";
            $userSelect = $userId === null ? ", u.name as submitter_name" : "";

            // Batch load ALL answers for this user + questionnaire
            $sqlA = "
                SELECT ua.* $userSelect 
                FROM user_answers ua
                $userJoin
                WHERE ua.questionnaire_id = :qid $userCondition AND ua.is_deleted = 0
                ORDER BY ua.submitted_at DESC
            ";
            $stmtA = $this->db->prepare($sqlA);
            $params = ['qid' => $questionnaireId];
            if ($userId !== null) $params['uid'] = $userId;
            $stmtA->execute($params);
            $rawAnswers = $stmtA->fetchAll(PDO::FETCH_ASSOC);

            // Group by question_id
            $allAnswers = [];
            foreach ($rawAnswers as $a) {
                $allAnswers[(int) $a['question_id']][] = $a;
            }

            $viewerAnswers = [];
            if ($viewerUserId !== null) {
                $stmtViewer = $this->db->prepare("
                    SELECT ua.*
                    FROM user_answers ua
                    WHERE ua.questionnaire_id = :qid
                      AND ua.user_id = :uid
                      AND ua.is_deleted = 0
                    ORDER BY ua.submitted_at DESC, ua.id DESC
                ");
                $stmtViewer->execute(['qid' => $questionnaireId, 'uid' => $viewerUserId]);
                foreach ($stmtViewer->fetchAll(PDO::FETCH_ASSOC) as $viewerAnswer) {
                    $viewerQuestionId = (int) $viewerAnswer['question_id'];
                    if (!isset($viewerAnswers[$viewerQuestionId])) {
                        $viewerAnswers[$viewerQuestionId] = $viewerAnswer;
                    }
                }
            }

            $userConditionF = $userId !== null ? "AND fu.user_id = :uid" : "";
            $userJoinF = $userId === null ? "LEFT JOIN users u ON fu.user_id = u.id" : "";
            $userSelectF = $userId === null ? ", u.name as submitter_name" : "";

            // Batch load ALL files for this user + questionnaire
            $sqlF = "
                SELECT fu.* $userSelectF 
                FROM file_uploads fu
                $userJoinF
                WHERE fu.questionnaire_id = :qid $userConditionF AND fu.is_deleted = 0
                ORDER BY fu.submitted_at DESC
            ";
            $stmtF = $this->db->prepare($sqlF);
            $stmtF->execute($params);
            $rawFiles = $stmtF->fetchAll(PDO::FETCH_ASSOC);

            // Group by question_id
            $allFiles = [];
            $allFilesByAnswerId = [];
            foreach ($rawFiles as $f) {
                $allFiles[(int) $f['question_id']][] = $f;
                if (!empty($f['user_answer_id'])) {
                    $allFilesByAnswerId[(int) $f['user_answer_id']][] = $f;
                }
            }
        }

        // Fetch sections at this level
        $stmtS = $this->db->prepare("
            SELECT * FROM sections 
            WHERE parent_section_id <=> :parent_id 
              AND questionnaire_id = :qid 
              AND is_deleted = 0
            ORDER BY id ASC
        ");
        $stmtS->execute(['parent_id' => $parentId, 'qid' => $questionnaireId]);
        $sections = $stmtS->fetchAll(PDO::FETCH_ASSOC);

        foreach ($sections as &$section) {
            $section['subsections'] = $this->getSectionsTreeForUser(
                (int) $section['id'], $questionnaireId, $userId, $viewerUserId
            );

            // Fetch questions in this section
            $stmtQ = $this->db->prepare("
                SELECT * FROM questions 
                WHERE section_id = :sid AND is_deleted = 0
                ORDER BY id ASC
            ");
            $stmtQ->execute(['sid' => $section['id']]);
            $questions = $stmtQ->fetchAll(PDO::FETCH_ASSOC);

            foreach ($questions as &$question) {
                $qid = (int) $question['id'];
                $answers = $allAnswers[$qid] ?? [];
                $files = $allFiles[$qid] ?? [];

                foreach ($answers as &$answer) {
                    $answer['uploaded_files'] = $allFilesByAnswerId[(int) $answer['id']] ?? [];
                }
                unset($answer);

                $question['answers'] = $answers;
                $question['uploaded_files'] = $files;
                $question['viewer_latest_answer'] = $viewerAnswers[$qid] ?? null;

                // Draft-specific data
                $question['draft_uploaded_files'] = array_filter($files, fn($f) => $f['status'] === 'draft');
                $question['for_review_uploaded_files'] = array_filter($files, fn($f) => $f['status'] === 'for review');

                // Latest answer status
                if (empty($answers)) {
                    $question['latest_answer_status'] = 'no answer';
                    $question['draft_answer'] = null;
                    $question['draft_explanation'] = null;
                } else {
                    $latest = $answers[0];
                    $question['latest_answer_status'] = $latest['status'];

                    if ($latest['status'] === 'draft') {
                        $question['draft_answer'] = $latest['answer'] ?? null;
                        $question['draft_explanation'] = $latest['explanation'] ?? null;
                    } elseif ($latest['status'] === 'for review') {
                        $question['for_review_answer'] = $latest['answer'] ?? null;
                        $question['for_review_explanation'] = $latest['explanation'] ?? null;
                    }
                }
            }

            $section['questions'] = $questions;
        }

        return $sections;
    }

    /**
     * Save user answer (draft or for-review).
     * Legacy submit requests are treated as for-review so users cannot bypass DH verification.
     * Returns associative array with success/error status.
     */
    public function saveUserAnswer(
        int $userId,
        int $questionId,
        int $questionnaireId,
        string $answer,
        ?string $explanation,
        string $action
    ): array {
        try {
            $this->db->beginTransaction();

            $userAnswerId = null;

            match ($action) {
                'draft' => $userAnswerId = $this->handleDraftAnswer(
                    $userId, $questionId, $questionnaireId, $answer, $explanation
                ),
                'submit' => $userAnswerId = $this->handleSubmitAnswer(
                    $userId, $questionId, $questionnaireId, $answer, $explanation
                ),
                'for-review' => $userAnswerId = $this->handleForReviewAnswer(
                    $userId, $questionId, $questionnaireId, $answer, $explanation
                ),
                default => throw new \InvalidArgumentException("Invalid action: {$action}"),
            };

            $this->db->commit();

            return [
                'success' => true,
                'user_answer_id' => $userAnswerId,
                'error' => null,
            ];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'user_answer_id' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function handleDraftAnswer(
        int $userId, int $questionId, int $questionnaireId,
        string $answer, ?string $explanation
    ): int {
        // Check for restricted statuses
        $stmt = $this->db->prepare("
            SELECT id FROM user_answers 
            WHERE user_id = :uid AND question_id = :qid AND questionnaire_id = :qqid 
              AND status IN ('pending', 'for review', 'accepted') AND is_deleted = 0
        ");
        $stmt->execute(['uid' => $userId, 'qid' => $questionId, 'qqid' => $questionnaireId]);
        if ($stmt->fetch()) {
            throw new \RuntimeException('Cannot save draft: a submitted answer already exists.');
        }

        // Check for existing draft
        $stmtDraft = $this->db->prepare("
            SELECT id FROM user_answers 
            WHERE user_id = :uid AND question_id = :qid AND questionnaire_id = :qqid 
              AND status = 'draft' AND is_deleted = 0
        ");
        $stmtDraft->execute(['uid' => $userId, 'qid' => $questionId, 'qqid' => $questionnaireId]);
        $existing = $stmtDraft->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $stmtUp = $this->db->prepare("
                UPDATE user_answers SET answer = :answer, explanation = :explanation, submitted_at = NOW() WHERE id = :id
            ");
            $stmtUp->execute(['answer' => $answer, 'explanation' => $explanation, 'id' => $existing['id']]);
            return (int) $existing['id'];
        }

        $stmtIns = $this->db->prepare("
            INSERT INTO user_answers (user_id, question_id, questionnaire_id, answer, status, submitted_at, explanation)
            VALUES (:uid, :qid, :qqid, :answer, 'draft', NOW(), :explanation)
        ");
        $stmtIns->execute([
            'uid' => $userId, 'qid' => $questionId, 'qqid' => $questionnaireId,
            'answer' => $answer, 'explanation' => $explanation,
        ]);
        return (int) $this->db->lastInsertId();
    }

    private function handleSubmitAnswer(
        int $userId, int $questionId, int $questionnaireId,
        string $answer, ?string $explanation
    ): int {
        return $this->handleForReviewAnswer($userId, $questionId, $questionnaireId, $answer, $explanation);
    }

    private function handleForReviewAnswer(
        int $userId, int $questionId, int $questionnaireId,
        string $answer, ?string $explanation
    ): int {
        $stmtDraft = $this->db->prepare("
            SELECT id FROM user_answers 
            WHERE user_id = :uid AND question_id = :qid AND questionnaire_id = :qqid 
              AND status = 'draft' AND is_deleted = 0
            ORDER BY submitted_at DESC, id DESC
            LIMIT 1
        ");
        $stmtDraft->execute(['uid' => $userId, 'qid' => $questionId, 'qqid' => $questionnaireId]);
        $draftAnswerId = $stmtDraft->fetchColumn();

        // Delete existing draft
        $stmtDel = $this->db->prepare("
            UPDATE user_answers SET is_deleted = 1 
            WHERE user_id = :uid AND question_id = :qid AND questionnaire_id = :qqid 
              AND status = 'draft' AND is_deleted = 0
        ");
        $stmtDel->execute(['uid' => $userId, 'qid' => $questionId, 'qqid' => $questionnaireId]);

        $stmt = $this->db->prepare("
            INSERT INTO user_answers (user_id, question_id, questionnaire_id, answer, status, submitted_at, explanation)
            VALUES (:uid, :qid, :qqid, :answer, 'for review', NOW(), :explanation)
        ");
        $stmt->execute([
            'uid' => $userId, 'qid' => $questionId, 'qqid' => $questionnaireId,
            'answer' => $answer, 'explanation' => $explanation,
        ]);
        $submittedAnswerId = (int) $this->db->lastInsertId();

        if ($draftAnswerId) {
            $renamedFiles = [];

            try {
                $this->transitionDraftFilesToSubmitted(
                    $userId,
                    $questionId,
                    $questionnaireId,
                    (int)$draftAnswerId,
                    $submittedAnswerId,
                    $renamedFiles
                );
            } catch (\Exception $e) {
                $this->rollbackRenamedAnswerFiles($renamedFiles);
                throw $e;
            }
        }

        return $submittedAnswerId;
    }

    /**
     * Process Department Head verification decisions for active for-review answers.
     */
    public function processDhVerification(
        int $questionnaireId,
        int $userId,
        string $dhVerificationFilePath,
        array $evaluations
    ): bool {
        if (empty($evaluations)) {
            return false;
        }

        try {
            $this->db->beginTransaction();

            $stmtFile = $this->db->prepare("
                INSERT INTO dh_verification_file_uploads (user_id, questionnaire_id, file_path, submitted_at)
                VALUES (:uid, :qqid, :file_path, NOW())
            ");
            $stmtFile->execute([
                'uid' => $userId,
                'qqid' => $questionnaireId,
                'file_path' => $dhVerificationFilePath,
            ]);

            $stmtAnswer = $this->db->prepare("
                SELECT id
                FROM user_answers
                WHERE user_id = :uid
                  AND question_id = :qid
                  AND questionnaire_id = :qqid
                  AND status = 'for review'
                  AND is_deleted = 0
                ORDER BY submitted_at DESC, id DESC
                LIMIT 1
            ");

            $stmtUpdateAnswer = $this->db->prepare("
                UPDATE user_answers
                SET status = :status, checked_at = NOW()
                WHERE id = :id
                  AND status = 'for review'
                  AND is_deleted = 0
            ");

            $renamedFiles = [];

            foreach ($evaluations as $questionId => $status) {
                $questionId = (int)$questionId;
                if (!in_array($status, ['pending', 'returned'], true)) {
                    throw new \InvalidArgumentException('Invalid Department Head evaluation status.');
                }

                $stmtAnswer->execute([
                    'uid' => $userId,
                    'qid' => $questionId,
                    'qqid' => $questionnaireId,
                ]);
                $answer = $stmtAnswer->fetch(PDO::FETCH_ASSOC);

                if (!$answer) {
                    throw new \RuntimeException('Only for-review answers can be processed by Department Head verification.');
                }

                $userAnswerId = (int)$answer['id'];
                $stmtUpdateAnswer->execute([
                    'status' => $status,
                    'id' => $userAnswerId,
                ]);

                if ($stmtUpdateAnswer->rowCount() !== 1) {
                    throw new \RuntimeException('Failed to update Department Head evaluation status.');
                }

                $this->transitionForReviewFilesAfterDhVerification(
                    $userId,
                    $questionId,
                    $questionnaireId,
                    $userAnswerId,
                    $status,
                    $renamedFiles
                );
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->rollbackRenamedAnswerFiles($renamedFiles ?? []);
            return false;
        }
    }

    /**
     * Save a file upload record.
     */
    private function transitionDraftFilesToSubmitted(
        int $userId,
        int $questionId,
        int $questionnaireId,
        int $draftAnswerId,
        int $submittedAnswerId,
        array &$renamedFiles
    ): void {
        $stmt = $this->db->prepare("
            SELECT *
            FROM file_uploads
            WHERE user_id = :uid
              AND question_id = :qid
              AND questionnaire_id = :qqid
              AND status = 'draft'
              AND user_answer_id = :draft_id
              AND is_deleted = 0
            ORDER BY id ASC
        ");
        $stmt->execute([
            'uid' => $userId,
            'qid' => $questionId,
            'qqid' => $questionnaireId,
            'draft_id' => $draftAnswerId,
        ]);

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $file) {
            $this->transitionFileUploadRow(
                $file,
                'for review',
                ['user_answer_id' => $submittedAnswerId],
                $renamedFiles
            );
        }
    }

    private function transitionForReviewFilesAfterDhVerification(
        int $userId,
        int $questionId,
        int $questionnaireId,
        int $userAnswerId,
        string $status,
        array &$renamedFiles
    ): void {
        $stmt = $this->db->prepare("
            SELECT *
            FROM file_uploads
            WHERE user_id = :uid
              AND question_id = :qid
              AND questionnaire_id = :qqid
              AND status = 'for review'
              AND is_deleted = 0
              AND (user_answer_id = :uaid OR user_answer_id IS NULL)
            ORDER BY id ASC
        ");
        $stmt->execute([
            'uid' => $userId,
            'qid' => $questionId,
            'qqid' => $questionnaireId,
            'uaid' => $userAnswerId,
        ]);

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $file) {
            $this->transitionFileUploadRow($file, $status, [], $renamedFiles);
        }
    }

    private function transitionFileUploadsForAnswer(
        int $userAnswerId,
        string $status,
        array $extraUpdates,
        array &$renamedFiles
    ): void {
        $stmt = $this->db->prepare("
            SELECT *
            FROM file_uploads
            WHERE user_answer_id = :uaid
              AND is_deleted = 0
            ORDER BY id ASC
        ");
        $stmt->execute(['uaid' => $userAnswerId]);

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $file) {
            $this->transitionFileUploadRow($file, $status, $extraUpdates, $renamedFiles);
        }
    }

    private function transitionFileUploadRow(
        array $file,
        string $status,
        array $extraUpdates,
        array &$renamedFiles
    ): void {
        $filePath = (string)($file['file_path'] ?? '');
        $newFilePath = $filePath;
        $fullPath = $this->resolveAnswerFilePathForTransition($filePath);

        if ($fullPath !== null) {
            [$targetPath, $targetName] = $this->buildTransitionTargetPath($fullPath, $status);

            if ($targetPath !== $fullPath) {
                if (!@rename($fullPath, $targetPath)) {
                    throw new \RuntimeException('Failed to rename uploaded file.');
                }

                $renamedFiles[] = [
                    'from' => $fullPath,
                    'to' => $targetPath,
                ];
            }

            $newFilePath = $targetName;
        }

        $setSql = [
            'status = :status',
            'file_path = :file_path',
        ];
        $params = [
            'status' => $status,
            'file_path' => $newFilePath,
            'id' => (int)$file['id'],
        ];

        if (array_key_exists('user_answer_id', $extraUpdates)) {
            $setSql[] = 'user_answer_id = :user_answer_id';
            $params['user_answer_id'] = $extraUpdates['user_answer_id'];
        }

        if (array_key_exists('remark', $extraUpdates)) {
            $setSql[] = 'remark = :remark';
            $params['remark'] = $extraUpdates['remark'];
        }

        if (array_key_exists('checked_by', $extraUpdates)) {
            $setSql[] = 'checked_by = :checked_by';
            $params['checked_by'] = $extraUpdates['checked_by'];
        }

        if (!empty($extraUpdates['checked_at_now'])) {
            $setSql[] = 'checked_at = NOW()';
        }

        $stmt = $this->db->prepare("
            UPDATE file_uploads
            SET " . implode(', ', $setSql) . "
            WHERE id = :id
              AND is_deleted = 0
        ");
        $stmt->execute($params);
    }

    private function resolveAnswerFilePathForTransition(string $filePath): ?string
    {
        $storedName = basename(str_replace('\\', '/', $filePath));
        if ($storedName === '') {
            return null;
        }

        $candidatePaths = [
            MNT_PATH . '/answer-files/' . $storedName,
            '/mnt/iad/upload/answer-files/' . $storedName,
            STORAGE_PATH . '/answer-files/' . $storedName,
            __DIR__ . '/../../public/assets/answer-files/' . $storedName,
        ];

        foreach ($candidatePaths as $candidatePath) {
            if (is_file($candidatePath)) {
                return $candidatePath;
            }
        }

        return null;
    }

    private function buildTransitionTargetPath(string $currentPath, string $status): array
    {
        $targetName = $this->buildStatusPrefixedFileName(basename($currentPath), $status);
        $directory = dirname($currentPath);
        $targetPath = $directory . DIRECTORY_SEPARATOR . $targetName;

        if ($targetPath === $currentPath || !file_exists($targetPath)) {
            return [$targetPath, $targetName];
        }

        $nameWithoutExt = pathinfo($targetName, PATHINFO_FILENAME);
        $extension = pathinfo($targetName, PATHINFO_EXTENSION);

        for ($i = 1; $i <= 50; $i++) {
            $suffix = '_' . str_pad((string)$i, 2, '0', STR_PAD_LEFT);
            $candidateName = $nameWithoutExt . $suffix . ($extension !== '' ? '.' . $extension : '');
            $candidatePath = $directory . DIRECTORY_SEPARATOR . $candidateName;

            if (!file_exists($candidatePath)) {
                return [$candidatePath, $candidateName];
            }
        }

        throw new \RuntimeException('Could not generate a unique uploaded file name.');
    }

    private function buildStatusPrefixedFileName(string $fileName, string $status): string
    {
        $prefix = $this->filePrefixForStatus($status);
        $knownPrefixes = [
            '(DRAFT)_',
            '(SUBMITTED)_',
            '(FOR-REVIEW)_',
            '(PENDING)_',
            '(ACCEPTED)_',
            '(REJECTED)_',
            '(RETURNED)_',
        ];

        foreach ($knownPrefixes as $knownPrefix) {
            if (str_starts_with($fileName, $knownPrefix)) {
                return $prefix . substr($fileName, strlen($knownPrefix));
            }
        }

        return $prefix . $fileName;
    }

    private function filePrefixForStatus(string $status): string
    {
        return match ($status) {
            'draft' => '(DRAFT)_',
            'for review' => '(FOR-REVIEW)_',
            'pending' => '(PENDING)_',
            'accepted' => '(ACCEPTED)_',
            'rejected' => '(REJECTED)_',
            'returned' => '(RETURNED)_',
            default => throw new \InvalidArgumentException('Unsupported file status prefix.'),
        };
    }

    private function rollbackRenamedAnswerFiles(array $renamedFiles): void
    {
        foreach (array_reverse($renamedFiles) as $renamedFile) {
            $from = $renamedFile['from'] ?? '';
            $to = $renamedFile['to'] ?? '';

            if (is_string($from) && is_string($to) && is_file($to) && !file_exists($from)) {
                @rename($to, $from);
            }
        }
    }

    public function softDeleteEditableFileUploads(
        int $userId,
        int $questionId,
        int $questionnaireId,
        int $userAnswerId,
        array $fileIds
    ): int {
        $fileIds = array_values(array_unique(array_filter(array_map('intval', $fileIds), fn($id) => $id > 0)));
        if (empty($fileIds)) {
            return 0;
        }

        $placeholders = [];
        $params = [
            'uid' => $userId,
            'qid' => $questionId,
            'qqid' => $questionnaireId,
            'uaid' => $userAnswerId,
        ];

        foreach ($fileIds as $index => $fileId) {
            $key = 'file_id_' . $index;
            $placeholders[] = ':' . $key;
            $params[$key] = $fileId;
        }

        $idList = implode(', ', $placeholders);
        $stmtCheck = $this->db->prepare("
            SELECT COUNT(*) AS total
            FROM file_uploads
            WHERE user_id = :uid
              AND question_id = :qid
              AND questionnaire_id = :qqid
              AND user_answer_id = :uaid
              AND status = 'draft'
              AND is_deleted = 0
              AND id IN ($idList)
        ");
        $stmtCheck->execute($params);
        $matchedCount = (int)($stmtCheck->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

        if ($matchedCount !== count($fileIds)) {
            return -1;
        }

        $stmt = $this->db->prepare("
            UPDATE file_uploads
            SET is_deleted = 1
            WHERE user_id = :uid
              AND question_id = :qid
              AND questionnaire_id = :qqid
              AND user_answer_id = :uaid
              AND status = 'draft'
              AND is_deleted = 0
              AND id IN ($idList)
        ");
        $stmt->execute($params);

        return $stmt->rowCount();
    }
    public function saveFileUpload(
        int $userId, int $questionId, int $questionnaireId,
        string $filePath, string $status, ?int $userAnswerId
    ): int {
        $stmt = $this->db->prepare("
            INSERT INTO file_uploads (user_id, question_id, questionnaire_id, file_path, status, user_answer_id, submitted_at)
            VALUES (:uid, :qid, :qqid, :file_path, :status, :uaid, NOW())
        ");
        $stmt->execute([
            'uid' => $userId, 'qid' => $questionId, 'qqid' => $questionnaireId,
            'file_path' => $filePath, 'status' => $status, 'uaid' => $userAnswerId,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Get sub-questionnaires assigned to a user for a given general questionnaire.
     */
    public function getSubQuestionnairesForUser(int $generalId, int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT q.* 
            FROM questionnaires q
            INNER JOIN user_questionnaires uq ON q.id = uq.questionnaire_id
            WHERE q.parent_questionnaire_id = :general_id
              AND uq.user_id = :user_id
              AND q.is_deleted = 0
              AND uq.is_deleted = 0
            ORDER BY q.title ASC
        ");
        $stmt->execute(['general_id' => $generalId, 'user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get the parent questionnaire of a sub-questionnaire.
     */
    public function getParentQuestionnaire(int $parentId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM questionnaires 
            WHERE id = :parent_id 
              AND is_deleted = 0
        ");
        $stmt->execute(['parent_id' => $parentId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
    public function getQuestionStatusCountsForExport(int $questionnaireId): array
    {
        $questionsStmt = $this->db->prepare("SELECT id FROM questions WHERE questionnaire_id = :qid AND is_deleted = 0");
        $questionsStmt->execute(['qid' => $questionnaireId]);
        $questionIds = array_map('intval', $questionsStmt->fetchAll(PDO::FETCH_COLUMN));

        $counts = [];
        foreach ($questionIds as $questionId) {
            $counts[$questionId] = [
                'no_answer' => 0,
                'draft' => 0,
                'for_review' => 0,
                'returned' => 0,
                'pending' => 0,
                'rejected' => 0,
                'accepted' => 0,
            ];
        }

        if (empty($questionIds)) {
            return $counts;
        }

        $assignedStmt = $this->db->prepare("SELECT COUNT(DISTINCT user_id) FROM user_questionnaires WHERE questionnaire_id = :qid AND is_deleted = 0");
        $assignedStmt->execute(['qid' => $questionnaireId]);
        $assignedCount = (int)$assignedStmt->fetchColumn();

        $answerStmt = $this->db->prepare("
            SELECT latest.question_id, latest.status, COUNT(*) AS total
            FROM (
                SELECT ua.question_id, ua.user_id, ua.status
                FROM user_answers ua
                INNER JOIN (
                    SELECT ua2.question_id, ua2.user_id, MAX(ua2.id) AS latest_id
                    FROM user_answers ua2
                    INNER JOIN user_questionnaires uq
                        ON uq.user_id = ua2.user_id
                        AND uq.questionnaire_id = ua2.questionnaire_id
                        AND uq.is_deleted = 0
                    WHERE ua2.questionnaire_id = :qid_latest
                      AND ua2.is_deleted = 0
                    GROUP BY ua2.question_id, ua2.user_id
                ) ids ON ids.latest_id = ua.id
                WHERE ua.questionnaire_id = :qid_rows
                  AND ua.is_deleted = 0
            ) latest
            GROUP BY latest.question_id, latest.status
        ");
        $answerStmt->execute([
            'qid_latest' => $questionnaireId,
            'qid_rows' => $questionnaireId,
        ]);

        foreach ($answerStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $questionId = (int)$row['question_id'];
            if (!isset($counts[$questionId])) {
                continue;
            }
            $status = strtolower(trim((string)$row['status']));
            $key = $status === 'for review' ? 'for_review' : str_replace(' ', '_', $status);
            if (array_key_exists($key, $counts[$questionId])) {
                $counts[$questionId][$key] = (int)$row['total'];
            }
        }

        foreach ($counts as $questionId => $statusCounts) {
            $answered = array_sum($statusCounts);
            $counts[$questionId]['no_answer'] = max(0, $assignedCount - $answered);
        }

        return $counts;
    }
}

