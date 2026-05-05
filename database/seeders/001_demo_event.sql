INSERT INTO events (
  slug,
  title,
  summary,
  start_at,
  end_at,
  timezone,
  location,
  custom_fields,
  allowed_domains,
  instant_approval,
  requires_payment,
  moodle_course_ids,
  moodle_cohort_ids,
  thank_you_message,
  status
) VALUES (
  'secure-ai-governance',
  'Secure AI Governance for Education Leaders',
  'A professional development program on secure AI adoption, governance evidence, risk decisions, and learning-sector implementation models.',
  '2026-06-10 09:00:00',
  '2026-06-10 15:00:00',
  'UTC',
  'Online / Hybrid',
  JSON_ARRAY(
    JSON_OBJECT('name', 'organization', 'label', 'Organization', 'type', 'text', 'required', true, 'moodle_shortname', 'organization'),
    JSON_OBJECT('name', 'job_title', 'label', 'Job title', 'type', 'text', 'required', true, 'moodle_shortname', 'jobtitle'),
    JSON_OBJECT('name', 'registration_type', 'label', 'Registration type', 'type', 'select', 'options', JSON_ARRAY('Faculty', 'School Leader', 'Government Partner', 'Other'), 'required', true, 'moodle_shortname', 'registrationtype')
  ),
  JSON_ARRAY('example.ac.ae', 'eca.ac.ae'),
  1,
  0,
  JSON_ARRAY(101, 102),
  JSON_ARRAY(12),
  'Thank you for registering. Program details and platform access will be sent after verification and provisioning.',
  'published'
) ON DUPLICATE KEY UPDATE
  title = VALUES(title),
  summary = VALUES(summary),
  custom_fields = VALUES(custom_fields),
  allowed_domains = VALUES(allowed_domains),
  moodle_course_ids = VALUES(moodle_course_ids),
  moodle_cohort_ids = VALUES(moodle_cohort_ids),
  status = VALUES(status);
