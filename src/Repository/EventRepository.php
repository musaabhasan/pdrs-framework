<?php

declare(strict_types=1);

namespace Pdrs\Repository;

use PDO;
use Pdrs\Support\Json;

final class EventRepository
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function findActiveBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM events WHERE slug = :slug AND status = "published" LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $event = $stmt->fetch();

        return $event ? $this->hydrate($event) : null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM events WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $event = $stmt->fetch();

        return $event ? $this->hydrate($event) : null;
    }

    private function hydrate(array $event): array
    {
        foreach (['program_modes', 'custom_fields', 'allowed_domains', 'moodle_course_ids', 'moodle_cohort_ids'] as $field) {
            $event[$field] = Json::decode($event[$field] ?? '[]', []);
        }

        return $event;
    }
}
