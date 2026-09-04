

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role         ENUM('student','admin','vc') NOT NULL,
    full_name    VARCHAR(120) NOT NULL,
    email        VARCHAR(190) NOT NULL,
    password     VARCHAR(255) NOT NULL,           
    student_id   VARCHAR(40)  NULL,               
    employee_id  VARCHAR(40)  NULL,             
    department   VARCHAR(60)  NULL,
    status       ENUM('active','suspended') NOT NULL DEFAULT 'active',
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_email (email),
    UNIQUE KEY uniq_student_id (student_id),
    UNIQUE KEY uniq_employee_id (employee_id),
    KEY idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS documents;
CREATE TABLE documents (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id       INT UNSIGNED NOT NULL,
    doc_type      ENUM('id_card','fee_receipt','library_card','transcripts','other') NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    stored_name   VARCHAR(64)  NOT NULL,          
    mime_type     VARCHAR(100) NOT NULL,
    size_bytes    INT UNSIGNED NOT NULL,
    uploaded_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_doc_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    KEY idx_user (user_id),
    KEY idx_doctype (doc_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS login_audit;
CREATE TABLE login_audit (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id      INT UNSIGNED NULL,
    email        VARCHAR(190) NOT NULL,
    ip           VARCHAR(45)  NOT NULL,
    success      TINYINT(1)   NOT NULL,
    attempted_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_email (email),
    KEY idx_time (attempted_at),
    KEY idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;


INSERT INTO users (role, full_name, email, password, employee_id, department, status)
VALUES (
    'vc',
    'Vice Chancellor',
    'vc@uiu.ac.bd',
    '$2y$10$mNEAEkMe9TLI2V8sCFT3YuOpV8WInOmvbkO/mIm4OvUsBKoGfOxtS',
    'EMP-VC-001',
    'Office of the Vice Chancellor',
    'active'
);


INSERT INTO users (role, full_name, email, password, employee_id, department, status)
VALUES (
    'admin',
    'Department Admin',
    'admin@uiu.ac.bd',
    '$2y$10$mNEAEkMe9TLI2V8sCFT3YuOpV8WInOmvbkO/mIm4OvUsBKoGfOxtS',
    'EMP-ADM-001',
    'Administration',
    'active'
);
