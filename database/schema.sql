CREATE DATABASE IF NOT EXISTS eliteshelf;

USE eliteshelf;

CREATE TABLE users (

    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(120) NOT NULL,

    email VARCHAR(190) UNIQUE,

    phone VARCHAR(30) UNIQUE,

    user_type ENUM(
        'parent',
        'student',
        'teacher',
        'admin'
    ) NOT NULL DEFAULT 'student',

    class_level ENUM('S5','S6'),

    password_hash VARCHAR(255) NOT NULL,

    role ENUM('user','admin') DEFAULT 'user',

    profile_pic VARCHAR(255),

    balance BIGINT DEFAULT 0,

    status ENUM(
        'active',
        'suspended'
    ) DEFAULT 'active',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE papers (

    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    title VARCHAR(220) NOT NULL,

    subject VARCHAR(120) NOT NULL,

    stream ENUM('Arts','Science') NOT NULL,

    class_level ENUM('S5','S6') NOT NULL,

    year SMALLINT,

    description TEXT,

    file_name VARCHAR(255) NOT NULL,

    stored_name VARCHAR(255) NOT NULL,

    file_size BIGINT UNSIGNED NOT NULL,

    price INT NOT NULL DEFAULT 0,

    is_free TINYINT(1) DEFAULT 0,

    uploaded_by BIGINT UNSIGNED NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY(uploaded_by)
    REFERENCES users(id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE payment_requests (

    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id BIGINT UNSIGNED NOT NULL,

    amount INT NOT NULL,

    transaction_ref VARCHAR(100) NOT NULL UNIQUE,

    payer_phone VARCHAR(30) NOT NULL,

    proof_path VARCHAR(255),

    status ENUM(
        'pending',
        'approved',
        'rejected'
    ) DEFAULT 'pending',

    admin_note VARCHAR(500),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    reviewed_at DATETIME,

    reviewed_by BIGINT UNSIGNED,

    FOREIGN KEY(user_id)
    REFERENCES users(id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE wallet_transactions (

    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id BIGINT UNSIGNED NOT NULL,

    type ENUM(
        'credit',
        'debit',
        'refund',
        'adjustment'
    ) NOT NULL,

    amount INT NOT NULL,

    reference VARCHAR(120) NOT NULL,

    description VARCHAR(255),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY(user_id)
    REFERENCES users(id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE downloads (

    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id BIGINT UNSIGNED NOT NULL,

    paper_id BIGINT UNSIGNED NOT NULL,

    amount_paid INT NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY(user_id)
    REFERENCES users(id),

    FOREIGN KEY(paper_id)
    REFERENCES papers(id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE notifications (

    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id BIGINT UNSIGNED NOT NULL,

    title VARCHAR(180) NOT NULL,

    body TEXT NOT NULL,

    is_read TINYINT(1) DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY(user_id)
    REFERENCES users(id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE reviews (

    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id BIGINT UNSIGNED NOT NULL,

    rating TINYINT NOT NULL,

    comment TEXT NOT NULL,

    status ENUM('visible','hidden') DEFAULT 'visible',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY(user_id)
    REFERENCES users(id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE questions (

    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id BIGINT UNSIGNED NOT NULL,

    subject VARCHAR(120) NOT NULL,

    question TEXT NOT NULL,

    reply TEXT,

    status ENUM(
        'open',
        'answered',
        'closed'
    ) DEFAULT 'open',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY(user_id)
    REFERENCES users(id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE announcements (

    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    title VARCHAR(180) NOT NULL,

    body TEXT NOT NULL,

    created_by BIGINT UNSIGNED NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY(created_by)
    REFERENCES users(id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE audit_logs (

    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id BIGINT UNSIGNED,

    action VARCHAR(120) NOT NULL,

    detail TEXT,

    ip_address VARCHAR(45),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
