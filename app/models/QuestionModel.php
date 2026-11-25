<?php
class QuestionModel extends Model
{
    public function __construct()
    {
        parent::__construct('default');
    }

    public function getBySectionId($sectionId)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM questions 
            WHERE section_id = :sid AND is_deleted = 0 
            ORDER BY id ASC
        ");
        $stmt->execute(['sid' => $sectionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByQuestionnaireId($questionnaireId)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM questions 
            WHERE questionnaire_id = :qid AND is_deleted = 0 
            ORDER BY section_id ASC, id ASC
        ");
        $stmt->execute(['qid' => $questionnaireId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM questions WHERE id = :id AND is_deleted = 0");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO questions (
                questionnaire_id, section_id, question_text, type, criteria, remarks, ref_no, area,
                criteria_type, criteria_link, criteria_file_path, created_by
            ) 
            VALUES (
                :questionnaire_id, :section_id, :question_text, :type, :criteria, :remarks, :ref_no, :area,
                :criteria_type, :criteria_link, :criteria_file_path, :created_by
            )
        ");
        return $stmt->execute([
            'questionnaire_id' => $data['questionnaire_id'],
            'section_id' => $data['section_id'] ?? null,
            'question_text' => $data['question_text'],
            'type' => $data['type'],
            'criteria' => $data['criteria'] ?? null,
            'remarks' => $data['remarks'] ?? null,
            'ref_no' => $data['ref_no'] ?? null,
            'area' => $data['area'] ?? null,
            'criteria_type' => $data['criteria_type'] ?? 'none',
            'criteria_link' => $data['criteria_link'] ?? null,
            'criteria_file_path' => $data['criteria_file_path'] ?? null,
            'created_by' => $data['created_by']
        ]);
    }

    public function update($id, $data)
    {
        $stmt = $this->db->prepare("
            UPDATE questions SET 
                section_id = :section_id,
                question_text = :question_text,
                type = :type,
                criteria = :criteria,
                remarks = :remarks,
                ref_no = :ref_no,
                area = :area,
                criteria_type = :criteria_type,
                criteria_link = :criteria_link,
                criteria_file_path = :criteria_file_path,
                edited_at = NOW(),
                edited_by = :edited_by
            WHERE id = :id AND is_deleted = 0
        ");
        return $stmt->execute([
            'section_id' => $data['section_id'] ?? null,
            'question_text' => $data['question_text'],
            'type' => $data['type'],
            'criteria' => $data['criteria'] ?? null,
            'remarks' => $data['remarks'] ?? null,
            'ref_no' => $data['ref_no'] ?? null,
            'area' => $data['area'] ?? null,
            'criteria_type' => $data['criteria_type'] ?? 'none',
            'criteria_link' => $data['criteria_link'] ?? null,
            'criteria_file_path' => $data['criteria_file_path'] ?? null,
            'edited_by' => $data['edited_by'],
            'id' => $id
        ]);
    }

    public function softDelete($id)
    {
        $stmt = $this->db->prepare("UPDATE questions SET is_deleted = 1 WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function countActiveAnswers($id)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM user_answers 
            WHERE question_id = :id AND is_deleted = 0
        ");
        $stmt->execute(['id' => $id]);
        return (int)$stmt->fetchColumn();
    }
}
