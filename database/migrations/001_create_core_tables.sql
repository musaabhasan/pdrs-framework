CREATE TABLE IF NOT EXISTS events (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(140) NOT NULL UNIQUE,
  title VARCHAR(255) NOT NULL,
  summary TEXT NULL,
  start_at DATETIME NOT NULL,
  end_at DATETIME NOT NULL,
  timezone VARCHAR(80) NOT NULL DEFAULT 'Asia/Dubai',
  location VARCHAR(255) NULL,
  custom_fields JSON NOT NULL,
  allowed_domains JSON NOT NULL,
  instant_approval TINYINT(1) NOT NULL DEFAULT 1,
  requires_payment TINYINT(1) NOT NULL DEFAULT 0,
  moodle_course_ids JSON NOT NULL,
  moodle_cohort_ids JSON NOT NULL,
  thank_you_message TEXT NULL,
  status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_events_status_start (status, start_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS verification_challenges (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  uuid CHAR(36) NOT NULL UNIQUE,
  event_id BIGINT UNSIGNED NOT NULL,
  email_hash CHAR(64) NOT NULL,
  email_encrypted TEXT NOT NULL,
  code_hash VARCHAR(255) NOT NULL,
  signed_token_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  verified_at DATETIME NULL,
  attempts INT UNSIGNED NOT NULL DEFAULT 0,
  ip_hash CHAR(64) NULL,
  user_agent_hash CHAR(64) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_verification_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
  INDEX idx_verification_email_event (event_id, email_hash),
  INDEX idx_verification_token (signed_token_hash),
  INDEX idx_verification_expiry (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS registrations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  uuid CHAR(36) NOT NULL UNIQUE,
  event_id BIGINT UNSIGNED NOT NULL,
  verification_id BIGINT UNSIGNED NOT NULL,
  email_hash CHAR(64) NOT NULL,
  email_encrypted TEXT NOT NULL,
  first_name_encrypted TEXT NOT NULL,
  last_name_encrypted TEXT NOT NULL,
  city_encrypted TEXT NULL,
  metadata_encrypted MEDIUMTEXT NULL,
  moodle_user_id BIGINT UNSIGNED NULL,
  approval_status ENUM('pending','approved','provisioned','failed') NOT NULL DEFAULT 'pending',
  approval_reason VARCHAR(500) NULL,
  provisioned_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_registration_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE RESTRICT,
  CONSTRAINT fk_registration_verification FOREIGN KEY (verification_id) REFERENCES verification_challenges(id) ON DELETE RESTRICT,
  UNIQUE KEY uniq_registration_event_email (event_id, email_hash),
  INDEX idx_registration_status (approval_status),
  INDEX idx_registration_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  actor_type VARCHAR(60) NOT NULL,
  actor_id VARCHAR(120) NULL,
  action VARCHAR(120) NOT NULL,
  entity_type VARCHAR(80) NULL,
  entity_id BIGINT UNSIGNED NULL,
  ip_hash CHAR(64) NULL,
  user_agent_hash CHAR(64) NULL,
  payload_json JSON NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_audit_action_created (action, created_at),
  INDEX idx_audit_entity (entity_type, entity_id),
  INDEX idx_audit_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rate_limits (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  rate_key CHAR(64) NOT NULL,
  action VARCHAR(80) NOT NULL,
  attempts INT UNSIGNED NOT NULL DEFAULT 0,
  window_start DATETIME NOT NULL,
  UNIQUE KEY uniq_rate_key_action (rate_key, action),
  INDEX idx_rate_window (window_start)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
