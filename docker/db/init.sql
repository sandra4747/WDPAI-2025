-- =========================
-- T A B E L E
-- =========================

CREATE TABLE roles (
    id SERIAL PRIMARY KEY,
    name VARCHAR(20) NOT NULL UNIQUE
);

CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    role_id INTEGER NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE profiles (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL UNIQUE,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    avatar_url TEXT
);

CREATE TABLE categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    icon VARCHAR(50)
);

CREATE TABLE goals (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    category_id INTEGER,
    title VARCHAR(255) NOT NULL,
    target_amount NUMERIC(12,2) NOT NULL,
    current_amount NUMERIC(12,2) DEFAULT 0,
    target_date DATE,
    image_path TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE contributions (
    id SERIAL PRIMARY KEY,
    goal_id INTEGER NOT NULL,
    amount NUMERIC(12,2) NOT NULL,
    contribution_date DATE DEFAULT CURRENT_DATE
);

CREATE TABLE goal_logs (
    id SERIAL PRIMARY KEY,
    goal_id INTEGER,
    old_amount NUMERIC(10,2),
    new_amount NUMERIC(10,2),
    change_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    action_type VARCHAR(50)
);

CREATE TABLE badges (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    icon VARCHAR(50) NOT NULL,
    description VARCHAR(255)
);

CREATE TABLE user_badges (
    user_id INTEGER NOT NULL,
    badge_id INTEGER NOT NULL,
    awarded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, badge_id)
);

-- =========================
-- K L U C Z E  O B C E
-- =========================

ALTER TABLE users
ADD CONSTRAINT fk_users_role
FOREIGN KEY (role_id) REFERENCES roles(id);

ALTER TABLE profiles
ADD CONSTRAINT fk_profiles_user
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

ALTER TABLE goals
ADD CONSTRAINT fk_goals_user
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

ALTER TABLE goals
ADD CONSTRAINT fk_goals_category
FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL;

ALTER TABLE contributions
ADD CONSTRAINT fk_contributions_goal
FOREIGN KEY (goal_id) REFERENCES goals(id) ON DELETE CASCADE;

ALTER TABLE goal_logs
ADD CONSTRAINT fk_goal_logs_goal
FOREIGN KEY (goal_id) REFERENCES goals(id) ON DELETE CASCADE;

ALTER TABLE user_badges
ADD CONSTRAINT fk_user_badges_user
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

ALTER TABLE user_badges
ADD CONSTRAINT fk_user_badges_badge
FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE;

-- =========================
-- F U N K C J E
-- =========================

CREATE OR REPLACE FUNCTION calculate_progress(
    current_val NUMERIC,
    target_val NUMERIC
) RETURNS INTEGER AS $$
BEGIN
    IF target_val <= 0 THEN
        RETURN 0;
    END IF;
    RETURN LEAST(
        GREATEST(CAST((current_val / target_val) * 100 AS INTEGER), 0),
        100
    );
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION calculate_total_user_progress(
    user_id_param INTEGER
) RETURNS INTEGER AS $$
DECLARE
    total_target NUMERIC(12,2);
    total_current NUMERIC(12,2);
BEGIN
    SELECT SUM(target_amount), SUM(current_amount)
    INTO total_target, total_current
    FROM goals
    WHERE user_id = user_id_param;

    IF total_target IS NULL OR total_target = 0 THEN
        RETURN 0;
    END IF;

    RETURN LEAST(
        GREATEST(CAST((total_current / total_target) * 100 AS INTEGER), 0),
        100
    );
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION log_goal_changes()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.current_amount <> OLD.current_amount THEN
        INSERT INTO goal_logs (goal_id, old_amount, new_amount, action_type)
        VALUES (NEW.id, OLD.current_amount, NEW.current_amount, 'KWOTA_ZMIENIONA');
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- =========================
-- T R I G G E R
-- =========================

CREATE TRIGGER audit_goal_update
AFTER UPDATE ON goals
FOR EACH ROW
EXECUTE FUNCTION log_goal_changes();

-- =========================
-- W I D O K I
-- =========================

CREATE OR REPLACE VIEW v_goals_details AS
SELECT
    g.id,
    g.user_id,
    g.category_id,
    g.title,
    c.name AS category_name,
    c.icon AS category_icon, 
    g.target_amount,
    g.current_amount,
    g.target_date,
    g.image_path,            
    calculate_progress(g.current_amount, g.target_amount) AS progress_percentage
FROM goals g
LEFT JOIN categories c ON g.category_id = c.id;

CREATE OR REPLACE VIEW v_user_details AS
SELECT
    u.id,
    u.email,
    r.name AS role,
    p.first_name,
    p.last_name,
    p.avatar_url,
    COUNT(g.id) AS total_goals
FROM users u
JOIN roles r ON u.role_id = r.id
LEFT JOIN profiles p ON u.id = p.user_id
LEFT JOIN goals g ON u.id = g.user_id
GROUP BY u.id, u.email, r.name, p.first_name, p.last_name, p.avatar_url;
