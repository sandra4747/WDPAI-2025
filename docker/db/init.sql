DROP TABLE IF EXISTS cards CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- Tabela ról (Wymaganie: Zarządzanie użytkownikami)
CREATE TABLE roles (
    id SERIAL PRIMARY KEY,
    name VARCHAR(20) UNIQUE NOT NULL
);

-- Tabela użytkowników (Wymaganie: Logowanie, Relacja 1:N z roles)
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    role_id INTEGER NOT NULL REFERENCES roles(id),
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Profil użytkownika (Wymaganie: Relacja 1:1 z users)
CREATE TABLE profiles (
    id SERIAL PRIMARY KEY,
    user_id INTEGER UNIQUE NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    first_name VARCHAR(100),
    avatar_url TEXT
);

-- Kategorie celów (Relacja 1:N z goals)
CREATE TABLE categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    icon VARCHAR(50)
);

-- Główne cele 
CREATE TABLE goals (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    category_id INTEGER REFERENCES categories(id) ON DELETE SET NULL,
    title VARCHAR(255) NOT NULL,
    target_amount DECIMAL(12, 2) NOT NULL,
    current_amount DECIMAL(12, 2) DEFAULT 0.00,
    target_date DATE,
    image_path TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tagi (Dla relacji Wiele-do-Wielu)
CREATE TABLE tags (
    id SERIAL PRIMARY KEY,
    tag_name VARCHAR(50) UNIQUE NOT NULL
);

-- Tabela łącząca M:N
CREATE TABLE goal_tags (
    goal_id INTEGER REFERENCES goals(id) ON DELETE CASCADE,
    tag_id INTEGER REFERENCES tags(id) ON DELETE CASCADE,
    PRIMARY KEY (goal_id, tag_id)
);

-- Historia wpłat (Relacja 1:N z goals) 
CREATE TABLE contributions (
    id SERIAL PRIMARY KEY,
    goal_id INTEGER NOT NULL REFERENCES goals(id) ON DELETE CASCADE,
    amount DECIMAL(12, 2) NOT NULL,
    contribution_date DATE DEFAULT CURRENT_DATE
);

-- LOGIKA (FUNKCJE I WYZWALACZE)

-- Funkcja obliczająca postęp (używana w widoku) 
CREATE OR REPLACE FUNCTION calculate_progress(current_val DECIMAL, target_val DECIMAL)
RETURNS INTEGER AS $$
BEGIN
    IF target_val <= 0 THEN RETURN 0; END IF;
    RETURN LEAST(GREATEST(CAST((current_val / target_val) * 100 AS INTEGER), 0), 100);
END;
$$ LANGUAGE plpgsql;

-- Wyzwalacz: Automatyczna aktualizacja stanu konta celu po dodaniu wpłaty
CREATE OR REPLACE FUNCTION update_goal_current_amount()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE goals 
    SET current_amount = current_amount + NEW.amount
    WHERE id = NEW.goal_id;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_after_contribution_insert
AFTER INSERT ON contributions
FOR EACH ROW
EXECUTE FUNCTION update_goal_current_amount();

-- WIDOKI (JOIN)

-- Widok szczegółów (Cel + Kategoria + Obliczony Postęp)
CREATE VIEW v_goals_details AS
SELECT 
    g.id,
    g.user_id,
    g.title,
    c.name AS category_name,
    g.target_amount,
    g.current_amount,
    calculate_progress(g.current_amount, g.target_amount) AS progress_percentage
FROM goals g
LEFT JOIN categories c ON g.category_id = c.id;

-- Widok dashboardu (Użytkownik + Rola + Liczba celów)
CREATE VIEW v_user_dashboard AS
SELECT 
    u.id,
    u.email,
    r.name AS role_name,
    p.first_name,
    COUNT(g.id) AS active_goals
FROM users u
JOIN roles r ON u.role_id = r.id
JOIN profiles p ON u.id = p.user_id
LEFT JOIN goals g ON u.id = g.user_id
GROUP BY u.id, r.name, p.first_name;

