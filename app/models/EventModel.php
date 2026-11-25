<?php
class EventModel extends Model
{
    public function __construct()
    {
        parent::__construct('default');
    }

    public function getAllEvents(int $limit = 0, int $offset = 0)
    {
        $sql = "SELECT * FROM events WHERE is_deleted = 0 ORDER BY date DESC, time DESC";
        if ($limit > 0) {
            $sql .= " LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVisibleEvents(int $limit = 0, int $offset = 0)
    {
        $sql = "SELECT * FROM events WHERE is_deleted = 0 AND status IN ('active', 'finished') ORDER BY date DESC, time DESC";
        if ($limit > 0) {
            $sql .= " LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEventById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM events WHERE id = :id AND is_deleted = 0");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getPublicEventById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM events WHERE id = :id AND is_deleted = 0 AND status IN ('active', 'finished')");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createEvent($data)
    {
        $stmt = $this->db->prepare("INSERT INTO events (title, venue, date, time, status, description, created_by) 
            VALUES (:title, :venue, :date, :time, :status, :description, :created_by)");
        return $stmt->execute($data);
    }

    public function updateEvent($id, $data)
    {
        $stmt = $this->db->prepare("UPDATE events SET 
            title = :title,
            venue = :venue,
            date = :date,
            time = :time,
            status = :status,
            description = :description
            WHERE id = :id AND is_deleted = 0");
        $data['id'] = $id;
        return $stmt->execute($data);
    }

    public function softDeleteEvent($id)
    {
        $stmt = $this->db->prepare("UPDATE events SET is_deleted = 1 WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getRecentEvents($limit = 3)
    {
        $stmt = $this->db->prepare("SELECT * FROM events WHERE is_deleted = 0 AND status IN ('active', 'finished') AND date <= CURDATE() ORDER BY date DESC, time DESC LIMIT :limit");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUpcomingEvents($limit = 3)
    {
        $stmt = $this->db->prepare("SELECT * FROM events WHERE is_deleted = 0 AND status IN ('active', 'finished') AND date > CURDATE() ORDER BY date ASC, time ASC LIMIT :limit");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLastInsertId()
    {
        return $this->db->lastInsertId();
    }

    public function getEventImages(int $eventId, bool $includeDeleted = false): array
    {
        $sql = "SELECT * FROM event_images WHERE event_id = :event_id";
        if (!$includeDeleted) {
            $sql .= " AND is_deleted = 0";
        }
        $sql .= " ORDER BY sort_order ASC, id ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['event_id' => $eventId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEventImageById(int $imageId): array|false
    {
        $stmt = $this->db->prepare("
            SELECT event_images.*, events.is_deleted AS event_is_deleted, events.status AS event_status
            FROM event_images
            INNER JOIN events ON events.id = event_images.event_id
            WHERE event_images.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $imageId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createEventImage(int $eventId, string $filePath, string $originalName, int $sortOrder): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO event_images (event_id, file_path, original_name, sort_order)
            VALUES (:event_id, :file_path, :original_name, :sort_order)
        ");

        return $stmt->execute([
            'event_id' => $eventId,
            'file_path' => $filePath,
            'original_name' => $originalName,
            'sort_order' => $sortOrder,
        ]);
    }

    public function getEventImagesByIds(int $eventId, array $imageIds): array
    {
        $imageIds = array_values(array_unique(array_filter(array_map('intval', $imageIds))));
        if (empty($imageIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($imageIds), '?'));
        $stmt = $this->db->prepare("
            SELECT *
            FROM event_images
            WHERE event_id = ? AND id IN ($placeholders)
        ");
        $stmt->execute(array_merge([$eventId], $imageIds));

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteEventImages(int $eventId, array $imageIds): bool
    {
        $imageIds = array_values(array_unique(array_filter(array_map('intval', $imageIds))));
        if (empty($imageIds)) {
            return true;
        }

        $placeholders = implode(',', array_fill(0, count($imageIds), '?'));
        $stmt = $this->db->prepare("
            DELETE FROM event_images
            WHERE event_id = ? AND id IN ($placeholders)
        ");

        return $stmt->execute(array_merge([$eventId], $imageIds));
    }

    public function updateEventImageOrder(int $eventId, array $orderedIds): bool
    {
        $orderedIds = array_values(array_unique(array_filter(array_map('intval', $orderedIds))));
        foreach ($orderedIds as $index => $imageId) {
            $stmt = $this->db->prepare("
                UPDATE event_images
                SET sort_order = :sort_order, updated_at = CURRENT_TIMESTAMP
                WHERE id = :id AND event_id = :event_id AND is_deleted = 0
            ");
            if (!$stmt->execute([
                'sort_order' => $index + 1,
                'id' => $imageId,
                'event_id' => $eventId,
            ])) {
                return false;
            }
        }

        return true;
    }

    public function nextImageSortOrder(int $eventId): int
    {
        $stmt = $this->db->prepare("
            SELECT COALESCE(MAX(sort_order), 0) + 1
            FROM event_images
            WHERE event_id = :event_id AND is_deleted = 0
        ");
        $stmt->execute(['event_id' => $eventId]);
        return (int)$stmt->fetchColumn();
    }
}
