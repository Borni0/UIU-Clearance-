

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS clearance_remarks;
DROP TABLE IF EXISTS clearance_requests;

CREATE TABLE clearance_requests (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id    INT UNSIGNED NOT NULL,
    department    ENUM('education','library','transport','medical','hostel') NOT NULL,
    status        ENUM('pending','approved','hold','rejected','blocked') NOT NULL DEFAULT 'pending',
    reason        VARCHAR(500) NULL,
    submitted_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    decided_at    DATETIME NULL,
    decided_by    INT UNSIGNED NULL,
    CONSTRAINT fk_cr_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_cr_decider FOREIGN KEY (decided_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uniq_student_dept (student_id, department),
    KEY idx_status (status),
    KEY idx_dept (department)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE clearance_remarks (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_id    INT UNSIGNED NOT NULL,
    author_id     INT UNSIGNED NOT NULL,
    body          TEXT NOT NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_rm_req FOREIGN KEY (request_id) REFERENCES clearance_requests(id) ON DELETE CASCADE,
    CONSTRAINT fk_rm_author FOREIGN KEY (author_id)  REFERENCES users(id) ON DELETE CASCADE,
    KEY idx_request (request_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS emergency_requests;

CREATE TABLE emergency_requests (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id    INT UNSIGNED NOT NULL,
    title         VARCHAR(150) NOT NULL,
    reason        TEXT NOT NULL,
    required_by   DATE NOT NULL,
    document_id   INT UNSIGNED NULL,
    status        ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    admin_remark  TEXT NULL,
    decided_by    INT UNSIGNED NULL,
    decided_at    DATETIME NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_em_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_em_doc FOREIGN KEY (document_id)  REFERENCES documents(id) ON DELETE SET NULL,
    CONSTRAINT fk_em_decider FOREIGN KEY (decided_by) REFERENCES users(id) ON DELETE SET NULL,
    KEY idx_em_student (student_id),
    KEY idx_em_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS notifications;

CREATE TABLE notifications (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id       INT UNSIGNED NOT NULL,
    kind          ENUM('request_submitted','status_update','admin_remark','approval','rejection','emergency') NOT NULL,
    title         VARCHAR(150) NOT NULL,
    body          VARCHAR(500) NULL,
    link          VARCHAR(255) NULL,
    is_read       TINYINT(1) NOT NULL DEFAULT 0,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_nt_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    KEY idx_user_unread (user_id, is_read),
    KEY idx_user_time (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS certificates;

CREATE TABLE certificates (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id    INT UNSIGNED NOT NULL,
    issued_by     INT UNSIGNED NULL,
    serial        VARCHAR(40) NOT NULL,
    note          VARCHAR(500) NULL,
    issued_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cert_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_cert_issuer  FOREIGN KEY (issued_by)  REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uniq_serial (serial),
    KEY idx_cert_student (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS activity_log;

CREATE TABLE activity_log (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    actor_id      INT UNSIGNED NULL,
    action        VARCHAR(80) NOT NULL,
    target_type   VARCHAR(40) NULL,
    target_id     INT UNSIGNED NULL,
    detail        VARCHAR(500) NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_act_actor FOREIGN KEY (actor_id) REFERENCES users(id) ON DELETE SET NULL,
    KEY idx_actor (actor_id),
    KEY idx_time (created_at),
    KEY idx_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
