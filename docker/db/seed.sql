-- =====================
-- SEED DATA
-- =====================

-- ---------------------
-- ROLES
-- ---------------------
INSERT INTO roles (id, name) VALUES
(1, 'ROLE_USER'),
(2, 'ROLE_ADMIN');

-- ---------------------
-- USERS 
-- ---------------------
INSERT INTO users (id, role_id, email, password) VALUES
-- Konto Admina (Login: admin@dreambo.pl | Hasło: adminadmin)
(1, 2, 'admin@dreambo.pl','$2y$10$.cxeuGcH1DL/afgQI68wPesFehMV5eppB775vripuhtZfjULqiiF.'),

-- Konto Jana (Login: jan@poczta.pl | Hasło: test1234)
(2, 1, 'jan@poczta.pl', '$2y$10$CAOW351Dd91Pc9a1MwCqz.DcV8NUiKOjHuf0eXPubgcCel4jXcWaW');

-- ---------------------
-- PROFILES (1–1)
-- ---------------------
INSERT INTO profiles (user_id, first_name, last_name, avatar_url) VALUES
(1, 'Admin', 'System', NULL),
(2, 'Jan', 'Kowalski', NULL);

-- ---------------------
-- CATEGORIES
-- ---------------------
INSERT INTO categories (name, icon) VALUES
('Podróże', 'plane'),
('Gadżety', 'laptop'),
('Dom', 'house');

-- ---------------------
-- TAGS
-- ---------------------
INSERT INTO tags (tag_name) VALUES
('Pilne'),
('Marzenie'),
('Praca'),
('Zdrowie');

-- ---------------------
-- GOALS
-- ---------------------
INSERT INTO goals (user_id, category_id, title, target_amount, current_amount, target_date, image_path) VALUES
(2, 3, 'Fundusz awaryjny', 10000.00, 6000.00, '2026-06-30', NULL),
(2, 2, 'Nowy MacBook Pro', 12000.00, 3000.00, '2026-11-30', 'macbook.png');


SELECT setval('roles_id_seq', (SELECT MAX(id) FROM roles));
SELECT setval('users_id_seq', (SELECT MAX(id) FROM users));
SELECT setval('profiles_id_seq', (SELECT MAX(id) FROM profiles));
SELECT setval('categories_id_seq', (SELECT MAX(id) FROM categories));
SELECT setval('goals_id_seq', (SELECT MAX(id) FROM goals));