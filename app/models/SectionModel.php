<?php
class SectionModel extends Model
{
    public function __construct()
    {
        parent::__construct('default');
    }

    public function getByQuestionnaireId($questionnaireId)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM sections 
            WHERE questionnaire_id = :qid AND is_deleted = 0 
            ORDER BY id ASC
        ");
        $stmt->execute(['qid' => $questionnaireId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM sections WHERE id = :id AND is_deleted = 0");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO sections (questionnaire_id, parent_section_id, title) 
            VALUES (:questionnaire_id, :parent_section_id, :title)
        ");
        return $stmt->execute([
            'questionnaire_id' => $data['questionnaire_id'],
            'parent_section_id' => $data['parent_section_id'] ?? null,
            'title' => $data['title']
        ]);
    }

    public function update($id, $data)
    {
        $stmt = $this->db->prepare("
            UPDATE sections 
            SET title = :title, parent_section_id = :parent_section_id 
            WHERE id = :id AND is_deleted = 0
        ");
        return $stmt->execute([
            'title' => $data['title'],
            'parent_section_id' => $data['parent_section_id'] ?? null,
            'id' => $id
        ]);
    }

    public function isDescendantOf($candidateSectionId, $sectionId)
    {
        $candidateSectionId = (int)$candidateSectionId;
        $sectionId = (int)$sectionId;
        $visited = [];

        while ($candidateSectionId > 0) {
            if ($candidateSectionId === $sectionId) {
                return true;
            }

            if (isset($visited[$candidateSectionId])) {
                return true;
            }
            $visited[$candidateSectionId] = true;

            $candidate = $this->getById($candidateSectionId);
            if (!$candidate || empty($candidate['parent_section_id'])) {
                return false;
            }

            $candidateSectionId = (int)$candidate['parent_section_id'];
        }

        return false;
    }

    public function countActiveChildren($id)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM sections 
            WHERE parent_section_id = :id AND is_deleted = 0
        ");
        $stmt->execute(['id' => $id]);
        return (int)$stmt->fetchColumn();
    }

    public function countActiveQuestions($id)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM questions 
            WHERE section_id = :id AND is_deleted = 0
        ");
        $stmt->execute(['id' => $id]);
        return (int)$stmt->fetchColumn();
    }

    public function softDelete($id)
    {
        $stmt = $this->db->prepare("UPDATE sections SET is_deleted = 1 WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
