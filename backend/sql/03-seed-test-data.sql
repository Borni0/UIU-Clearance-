

SET NAMES utf8mb4;


DELETE FROM activity_log
 WHERE actor_id IN (SELECT id FROM users WHERE email LIKE '%@uiu.ac.bd');
DELETE FROM notifications WHERE user_id IN (SELECT id FROM users WHERE email LIKE '%@uiu.ac.bd');
DELETE FROM certificates  WHERE student_id IN (SELECT id FROM users WHERE email LIKE '%@uiu.ac.bd');
DELETE FROM clearance_remarks WHERE author_id IN (SELECT id FROM users WHERE email LIKE '%@uiu.ac.bd');
DELETE FROM clearance_requests WHERE student_id IN (SELECT id FROM users WHERE email LIKE '%@uiu.ac.bd');
DELETE FROM emergency_requests WHERE student_id IN (SELECT id FROM users WHERE email LIKE '%@uiu.ac.bd');
DELETE FROM users WHERE email LIKE '%@uiu.ac.bd' AND email NOT IN ('vc@uiu.ac.bd','admin@uiu.ac.bd');

SET @PW = '$2y$10$mNEAEkMe9TLI2V8sCFT3YuOpV8WInOmvbkO/mIm4OvUsBKoGfOxtS';


INSERT INTO users (role, full_name, email, password, employee_id, department, status)
VALUES
('vc', 'Dr. Ayesha Siddiqui', 'vc2@uiu.ac.bd', @PW, 'EMP-VC-002', 'Office of the Vice Chancellor', 'active')
ON DUPLICATE KEY UPDATE full_name=VALUES(full_name), password=VALUES(password), status='active';


INSERT INTO users (role, full_name, email, password, employee_id, department, status) VALUES
('admin', 'Dr. Kamal Hossain',  'admin.kamal@uiu.ac.bd',  @PW, 'EMP-ADM-101', 'Education',  'active'),
('admin', 'Ms. Nusrat Jahan',   'admin.nusrat@uiu.ac.bd', @PW, 'EMP-ADM-102', 'Library',    'active'),
('admin', 'Mr. Rezaul Karim',   'admin.rezaul@uiu.ac.bd', @PW, 'EMP-ADM-103', 'Transport',  'active')
ON DUPLICATE KEY UPDATE full_name=VALUES(full_name), department=VALUES(department), password=VALUES(password), status='active';


INSERT INTO users (role, full_name, email, password, student_id, department, status) VALUES
('student', 'Rahim Ahmed',    'student.rahim@uiu.ac.bd',   @PW, '011221001', 'CSE',       'active'),
('student', 'Karim Hossain',  'student.karim@uiu.ac.bd',   @PW, '011221002', 'EEE',       'active'),
('student', 'Sadia Islam',    'student.sadia@uiu.ac.bd',   @PW, '011221003', 'BBA',       'active')
ON DUPLICATE KEY UPDATE full_name=VALUES(full_name), department=VALUES(department), password=VALUES(password), status='active';


INSERT IGNORE INTO clearance_requests (student_id, department, status, submitted_at, decided_at, decided_by) VALUES

((SELECT id FROM users WHERE email='student.rahim@uiu.ac.bd'),  'education', 'approved',  NOW() - INTERVAL 10 DAY, NOW() - INTERVAL 7 DAY, (SELECT id FROM users WHERE email='admin.kamal@uiu.ac.bd')),
((SELECT id FROM users WHERE email='student.rahim@uiu.ac.bd'),  'library',   'approved',  NOW() - INTERVAL 10 DAY, NOW() - INTERVAL 6 DAY, (SELECT id FROM users WHERE email='admin.nusrat@uiu.ac.bd')),
((SELECT id FROM users WHERE email='student.rahim@uiu.ac.bd'),  'transport', 'approved',  NOW() - INTERVAL 9 DAY,  NOW() - INTERVAL 5 DAY, (SELECT id FROM users WHERE email='admin.rezaul@uiu.ac.bd')),
((SELECT id FROM users WHERE email='student.rahim@uiu.ac.bd'),  'medical',   'pending',   NOW() - INTERVAL 2 DAY,  NULL, NULL),
((SELECT id FROM users WHERE email='student.rahim@uiu.ac.bd'),  'hostel',    'hold',      NOW() - INTERVAL 5 DAY,  NOW() - INTERVAL 3 DAY, (SELECT id FROM users WHERE email='admin@uiu.ac.bd')),

((SELECT id FROM users WHERE email='student.karim@uiu.ac.bd'),  'education', 'approved',  NOW() - INTERVAL 8 DAY,  NOW() - INTERVAL 4 DAY, (SELECT id FROM users WHERE email='admin.kamal@uiu.ac.bd')),
((SELECT id FROM users WHERE email='student.karim@uiu.ac.bd'),  'library',   'pending',   NOW() - INTERVAL 1 DAY,  NULL, NULL),
((SELECT id FROM users WHERE email='student.karim@uiu.ac.bd'),  'transport', 'pending',   NOW() - INTERVAL 1 DAY,  NULL, NULL),
((SELECT id FROM users WHERE email='student.karim@uiu.ac.bd'),  'medical',   'rejected',  NOW() - INTERVAL 6 DAY,  NOW() - INTERVAL 4 DAY, (SELECT id FROM users WHERE email='admin@uiu.ac.bd')),
((SELECT id FROM users WHERE email='student.karim@uiu.ac.bd'),  'hostel',    'hold',      NOW() - INTERVAL 4 DAY,  NOW() - INTERVAL 2 DAY, (SELECT id FROM users WHERE email='admin@uiu.ac.bd')),

((SELECT id FROM users WHERE email='student.sadia@uiu.ac.bd'),  'education', 'approved',  NOW() - INTERVAL 12 DAY, NOW() - INTERVAL 9 DAY, (SELECT id FROM users WHERE email='admin.kamal@uiu.ac.bd')),
((SELECT id FROM users WHERE email='student.sadia@uiu.ac.bd'),  'library',   'approved',  NOW() - INTERVAL 11 DAY, NOW() - INTERVAL 8 DAY, (SELECT id FROM users WHERE email='admin.nusrat@uiu.ac.bd')),
((SELECT id FROM users WHERE email='student.sadia@uiu.ac.bd'),  'transport', 'pending',   NOW() - INTERVAL 3 DAY,  NULL, NULL),
((SELECT id FROM users WHERE email='student.sadia@uiu.ac.bd'),  'medical',   'approved',  NOW() - INTERVAL 9 DAY,  NOW() - INTERVAL 6 DAY, (SELECT id FROM users WHERE email='admin@uiu.ac.bd')),
((SELECT id FROM users WHERE email='student.sadia@uiu.ac.bd'),  'hostel',    'pending',   NOW() - INTERVAL 1 DAY,  NULL, NULL);


INSERT IGNORE INTO clearance_remarks (request_id, author_id, body) VALUES
((SELECT cr.id FROM clearance_requests cr JOIN users u ON u.id=cr.student_id WHERE u.email='student.karim@uiu.ac.bd' AND cr.department='medical'),
 (SELECT id FROM users WHERE email='admin@uiu.ac.bd'),
 'Outstanding library fine from semester 3. Please clear it before reapplying.'),
((SELECT cr.id FROM clearance_requests cr JOIN users u ON u.id=cr.student_id WHERE u.email='student.rahim@uiu.ac.bd' AND cr.department='hostel'),
 (SELECT id FROM users WHERE email='admin@uiu.ac.bd'),
 'Room inspection pending — hosteller will be notified by email.');


INSERT IGNORE INTO emergency_requests (student_id, title, reason, required_by, status, created_at) VALUES
((SELECT id FROM users WHERE email='student.rahim@uiu.ac.bd'),
 'Graduation certificate needed for job application',
 'I have a final-round interview on short notice and the employer requires the original clearance certificate.',
 DATE_ADD(CURDATE(), INTERVAL 7 DAY),
 'pending',
 NOW() - INTERVAL 1 DAY);


INSERT IGNORE INTO notifications (user_id, kind, title, body, link) VALUES
((SELECT id FROM users WHERE email='student.rahim@uiu.ac.bd'), 'approval',  'Library clearance approved', 'Your library clearance was approved by Ms. Nusrat Jahan.', '/student/track-status.php'),
((SELECT id FROM users WHERE email='student.rahim@uiu.ac.bd'), 'admin_remark','Hostel clearance on hold',  'Reason: pending room inspection.', '/student/track-status.php'),
((SELECT id FROM users WHERE email='student.karim@uiu.ac.bd'), 'rejection', 'Medical clearance rejected', 'Outstanding fine on record. See remarks.', '/student/track-status.php'),
((SELECT id FROM users WHERE email='student.sadia@uiu.ac.bd'), 'status_update','Transport clearance submitted', 'Your transport clearance is now pending review.', '/student/track-status.php'),
((SELECT id FROM users WHERE email='admin.kamal@uiu.ac.bd'), 'request_submitted', 'New clearance request', 'Karim Hossain submitted Education clearance.', '/admin/admin-education.php'),
((SELECT id FROM users WHERE email='admin.nusrat@uiu.ac.bd'), 'request_submitted', 'New clearance request', 'Sadia Islam submitted Library clearance.', '/admin/admin-library.php'),
((SELECT id FROM users WHERE email='admin.rezaul@uiu.ac.bd'), 'request_submitted', 'New clearance request', 'Karim Hossain submitted Transport clearance.', '/admin/admin-transport.php'),
((SELECT id FROM users WHERE email='vc@uiu.ac.bd'), 'emergency', 'New emergency request', 'Rahim Ahmed submitted an emergency clearance request.', '/vc/dashboard.php');


INSERT IGNORE INTO activity_log (actor_id, action, target_type, target_id, detail) VALUES
((SELECT id FROM users WHERE email='admin.kamal@uiu.ac.bd'),  'clearance_approved',  'clearance_request', 1, 'Education clearance approved for Rahim Ahmed'),
((SELECT id FROM users WHERE email='admin.nusrat@uiu.ac.bd'), 'clearance_approved',  'clearance_request', 2, 'Library clearance approved for Rahim Ahmed'),
((SELECT id FROM users WHERE email='admin@uiu.ac.bd'),        'clearance_rejected',  'clearance_request', 9, 'Medical clearance rejected for Karim Hossain');
