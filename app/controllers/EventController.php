<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/EventModel.php';

class EventController extends Controller
{
    protected EventModel $eventModel;
    
    // Mock photos for our Instagram-like carousel
    private array $mockPhotos = [
        '/assets/images/field1.png',
        '/assets/images/field2.jpg',
        '/assets/images/field3.jpg',
        '/assets/images/field4.png',
        '/assets/images/field1.webp'
    ];

    public function __construct()
    {
        $this->eventModel = new EventModel();
    }

    public function index(): void
    {
        // Initial load only needs a few events
        $events = $this->eventModel->getVisibleEvents(8, 0);
        
        foreach ($events as &$event) {
            $event['photos'] = $this->photosForEvent((int)$event['id'], 3);
        }

        $data = [
            'title' => 'Events',
            'events' => $events
        ];
        $this->view('events/index', $data);
    }

    public function show(string|int $id): void
    {
        $event = $this->eventModel->getPublicEventById((int)$id);
        if (!$event) {
            header("Location: /events");
            exit;
        }

        $event['photos'] = $this->photosForEvent((int)$event['id'], 6);

        $recentEvents = $this->eventModel->getRecentEvents(3);
        $upcomingEvents = $this->eventModel->getUpcomingEvents(3);

        $data = [
            'title' => $event['title'],
            'event' => $event,
            'recentEvents' => $recentEvents,
            'upcomingEvents' => $upcomingEvents
        ];
        $this->view('events/show', $data);
    }

    public function loadMoreEvents(): void
    {
        header('Content-Type: application/json');
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 8;
        $offset = ($page - 1) * $limit;

        $events = $this->eventModel->getVisibleEvents($limit, $offset);
        
        foreach ($events as &$event) {
            $event['photos'] = $this->photosForEvent((int)$event['id'], 3);
            // Format dates for JSON response
            $event['date_formatted'] = date('M d, Y', strtotime((string)$event['date']));
        }

        echo json_encode([
            'success' => true,
            'data' => $events,
            'hasMore' => count($events) === $limit
        ]);
        exit;
    }

    public function loadMorePhotos(): void
    {
        header('Content-Type: application/json');
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $eventId = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;

        if ($eventId > 0) {
            if (!$this->eventModel->getPublicEventById($eventId)) {
                echo json_encode([
                    'success' => false,
                    'data' => [],
                    'hasMore' => false
                ]);
                exit;
            }

            $images = $this->eventModel->getEventImages($eventId);
            $photos = array_map(fn($image) => $this->imageUrl((int)$image['id']), $images);
            $limit = 6;
            $offset = max(0, ($page - 1) * $limit);
            $slice = array_slice($photos, $offset, $limit);

            echo json_encode([
                'success' => true,
                'data' => $slice,
                'hasMore' => $offset + $limit < count($photos)
            ]);
            exit;
        }
        
        // Let's pretend we only have 3 pages of photos
        if ($page > 3) {
            echo json_encode([
                'success' => true,
                'data' => [],
                'hasMore' => false
            ]);
            exit;
        }

        $photos = $this->getRandomMockPhotos(6);

        echo json_encode([
            'success' => true,
            'data' => $photos,
            'hasMore' => $page < 3
        ]);
        exit;
    }

    private function getRandomMockPhotos(int $count = 3): array
    {
        $photos = $this->mockPhotos;
        shuffle($photos);
        // Sometimes return fewer photos to make it realistic
        $numPhotos = min($count, count($photos), rand(2, 5));
        return array_slice($photos, 0, $numPhotos);
    }

    public function image(): void
    {
        $imageId = (int)($_GET['id'] ?? 0);
        $image = $this->eventModel->getEventImageById($imageId);
        if (
            !$image ||
            (int)$image['is_deleted'] === 1 ||
            (int)$image['event_is_deleted'] === 1 ||
            !in_array((string)$image['event_status'], ['active', 'finished'], true)
        ) {
            http_response_code(404);
            echo 'Image not found.';
            exit;
        }

        $relativePath = str_replace(['../', './', '\\'], ['', '', '/'], (string)($image['file_path'] ?? ''));
        if (!str_starts_with($relativePath, 'event-images/')) {
            http_response_code(404);
            echo 'Image not found.';
            exit;
        }

        $filename = basename($relativePath);
        $fullPath = rtrim(MNT_PATH, '/\\') . DIRECTORY_SEPARATOR . 'event-images' . DIRECTORY_SEPARATOR . $filename;
        if (!is_file($fullPath)) {
            http_response_code(404);
            echo 'Image file not found.';
            exit;
        }

        $mime = mime_content_type($fullPath) ?: 'application/octet-stream';
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            http_response_code(415);
            echo 'Unsupported image type.';
            exit;
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($fullPath));
        header('Content-Disposition: inline; filename="' . basename((string)($image['original_name'] ?? $filename)) . '"');
        readfile($fullPath);
        exit;
    }

    private function photosForEvent(int $eventId, int $fallbackCount): array
    {
        $images = $this->eventModel->getEventImages($eventId);
        if (!empty($images)) {
            return array_map(fn($image) => $this->imageUrl((int)$image['id']), $images);
        }

        return $this->getRandomMockPhotos($fallbackCount);
    }

    private function imageUrl(int $imageId): string
    {
        return '/events/image?id=' . $imageId;
    }
}
