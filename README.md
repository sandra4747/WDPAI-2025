# ☁️ DreamBo - Twoje Prywatne Centrum Dowodzenia Marzeniami

> **Temat projektu:** DreamBo – Interaktywna lista życzeń z modułem wizualizacji postępów i systemem motywacyjnym.
>
> **Przedmiot:** Wstęp Do Tworzenia Aplikacji Internetowych (WDPAI)

---

## Spis treści
1. [O projekcie](#o-projekcie)
2. [Kluczowe Funkcjonalności](#kluczowe-funkcjonalności)
3. [Architektura Techniczna](#architektura-techniczna)
4. [Baza Danych](#baza-danych)
5. [Interfejs Użytkownika](#interfejs-użytkownika)
6. [Instrukcja Uruchomienia](#instrukcja-uruchomienia)
7. [Scenariusze Testowe](#scenariusze-testowe)

---

## O projekcie

**DreamBo** to aplikacja webowa typu *self-hosted*, która rozwiązuje problem "cmentarzysk marzeń" - pasywnych list życzeń, które nigdy nie są realizowane.

W przeciwieństwie do zwykłego notatnika, DreamBo umożliwia określenie konkretnych kwot i terminów, zmieniając "chciałbym" w "realizuję". Aplikacja łączy funkcjonalność wishlisty z narzędziem do oszczędzania, wizualizując, jak blisko spełnienia marzenia jesteś.

## Kluczowe Funkcjonalności

### Uwierzytelnianie i Bezpieczeństwo
* **Bezpieczna Rejestracja i Logowanie:** System oparty na sesjach PHP z hashowaniem haseł (`bcrypt`).
* **Kontrola Dostępu:** Role użytkowników (User/Admin) sterujące dostępem do zasobów.

### Zarządzanie Celami (Dream Management)
* **Tworzenie Marzeń:** Definiowanie celów z określeniem kwoty docelowej, kategorii (np. Podróże, Gadżety) i daty realizacji.
* **Symulacja Wpłat:** Możliwość rejestrowania wpłat na wirtualne konto celu.
* **Wizualizacja Postępów:** Dynamiczne paski postępu (Progress Bars) obliczane w czasie rzeczywistym na podstawie danych z bazy.
* **Edycja i Historia:** Możliwość zmiany parametrów celu wraz z pełnym audytem zmian kwot.

### Odkrywanie i Śledzenie
* **Wyszukiwarka Live:** Błyskawiczne filtrowanie celów po nazwie z wykorzystaniem **Fetch API** (bez przeładowania strony).
* **Dashboard:** Centrum dowodzenia z podsumowaniem całkowitego postępu konta i najbliższych celów.

### System Motywacyjny
* **System Odznak (Badges):** Automatyczne przyznawanie osiągnięć (np. "Początkujący", "Bogacz") po spełnieniu warunków finansowych.

### Zarządzanie Profilem
* **Personalizacja:** Edycja danych osobowych i wgrywanie awatara (obsługa plików).
* **Widok Profilu:** Prezentacja zdobytych odznak i danych użytkownika.

### Panel Administratora (Admin Dashboard)
* **Zarządzanie Użytkownikami:** Dedykowany widok dostępny tylko dla roli `ROLE_ADMIN`, prezentujący listę wszystkich zarejestrowanych użytkowników.
* **Moderacja i Usuwanie:** Możliwość trwałego usunięcia konta użytkownika. Dzięki zastosowaniu mechanizmu **CASCADE** w bazie danych, usunięcie użytkownika automatycznie i bezpiecznie czyści wszystkie powiązane z nim cele, historie wpłat oraz zdobyte odznaki.

---

## Architektura Techniczna

Aplikacja została zbudowana w oparciu o wzorzec **MVC (Model-View-Controller)**, zapewniający czystość kodu i łatwość utrzymania.

### Przepływ Systemu
1.  **Routing:** Aplikacja wykorzystuje wzorzec Front Controller. Wszystkie żądania trafiają do pliku index.php (punkt wejścia), który inicjuje sesję i obsługę błędów, a następnie przekazuje sterowanie do klasy Routing, która uruchamia odpowiedni Kontroler.
2.  **Controller:** Przetwarza dane wejściowe, weryfikuje uprawnienia i komunikuje się z Repozytorium.
3.  **Repository:** Warstwa abstrakcji bazy danych - wykonuje bezpieczne zapytania SQL (Prepared Statements).
4.  **Database:** Konteneryzowany silnik PostgreSQL przetwarza dane, uruchamia Triggery i zwraca wyniki przez Widoki.
5.  **View:** Wyrenderowany kod HTML (wraz z dołączonymi stylami CSS i skryptami JavaScript) jest wysyłany do przeglądarki, gdzie następuje finalna prezentacja interfejsu.
   
---

## Baza Danych

Baza danych PostgreSQL została zaprojektowana zgodnie z **3. Postacią Normalną (3NF)**, eliminując redundancję danych.

### Diagram ERD
![Diagram ERD](./docs/ERD.png)

### Zaimplementowane wymagane elementy SQL:
* **Relacje:**
    * **1:1** (`users` ↔ `profiles`) – profil użytkownika.
    * **1:N** (`users` → `goals`, `categories` → `goals`) – lista marzeń.
    * **N:M** (`users` ↔ `badges`) – system odznak (tabela łącząca `user_badges`).
      
* **Widoki (Views):**
    * `v_goals_details` – dynamiczny widok łączący marzenia z kategoriami i wyliczający % realizacji.
      
      ```sql
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

    * `v_user_details` – agregacja statystyk użytkownika (liczba marzeń, rola).
 
      ```sql
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
      
* **Wyzwalacz (Trigger):**
    * `audit_goal_update` – automat logujący każdą zmianę kwoty marzenia do tabeli `goal_logs`.
      ```sql
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



      CREATE TRIGGER audit_goal_update
      AFTER UPDATE ON goals
      FOR EACH ROW
      EXECUTE FUNCTION log_goal_changes();
      
* **Funkcje PL/pgSQL:**
    * `calculate_progress()` – funkcja obliczająca postęp (używana w widokach i triggerach).
      ```sql
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
    
    * `calculate_total_user_progress()` – funkcja sumująca postęp całego konta.

      ```sql
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
      
* **Transakcje:**
    * Transakcje w klasie UserRepository zabezpieczają procesy rejestracji i edycji profilu, gwarantując atomowy zapis danych do powiązanych tabel users i profiles. Dzięki temu błąd w dowolnym kroku automatycznie wycofuje całą operację (ROLLBACK), zapewniając pełną spójność danych i eliminując ryzyko powstania niekompletnych rekordów.
 
      ```php
      // Fragment kodu z UserRepository.php
      public function createUser(UserRegistrationDTO $userDto): void {
          $db = $this->database->connect();
          try {
              $db->beginTransaction(); // Start transakcji
        
            // Krok 1: Insert do tabeli users
            $stmt = $db->prepare('INSERT INTO users ... RETURNING id');
            // ... execute ...
    
            // Krok 2: Insert do tabeli profiles
            $stmt = $db->prepare('INSERT INTO profiles ...');
            // ... execute ...
    
            $db->commit(); // Zatwierdzenie zmian
            } catch (PDOException $e) {
                if ($db->inTransaction()) {
                    $db->rollBack(); // Wycofanie w razie błędu
                }
                throw $e;
            }
      }

---

## Interfejs Użytkownika

![Dashboard](./docs/screenshots/dashboard.png)
![Gallery](./docs/screenshots/gallery.png)
![AddGoal](./docs/screenshots/addGoal.png)
![Profile](./docs/screenshots/profile.png)
![Admin](./docs/screenshots/admin.png)






---

## Instrukcja Uruchomienia

Wymagane środowisko: **Docker** oraz **Docker Compose**.

1.  **Sklonuj repozytorium:**
    ```bash
    git clone [https://github.com/sandra4747/WDPAI-2025.git](https://github.com/sandra4747/WDPAI-2025.git)
    cd WDPAI-2025
    ```

2.  **Uruchom aplikację:**
    ```bash
    docker-compose up -d --build
    ```
    *Baza danych zostanie automatycznie zainicjalizowana strukturą i danymi testowymi (seed) z pliku `docker/db/init.sql`.*

3.  **Dostęp:**
    * **Aplikacja DreamBo:** `http://localhost:8080`
        * *Konto Admina:* `admin@dreambo.com`, `adminadmin`
        * *Konto Użytkownika:* `jan@poczta.pl`, `test1234`
    * **Baza danych (PgAdmin):** `http://localhost:5050`
        * *Email:* `admin@example.com`
        * *Hasło:* `admin`

---

## Scenariusze Testowe

### 1. Automatyczne Testy Integracyjne 
Projekt zawiera skrypt bash weryfikujący endpointy i odporność na awarie.

    ```bash
    # Uruchomienie testów
    ./tests/integration/test_endpoints.sh

### 2. Testy Jednostkowe (Unit Tests)
Projekt wykorzystuje bibliotekę **PHPUnit** do weryfikacji logiki biznesowej. Testy są uruchamiane bezpośrednio w kontenerze aplikacji, co eliminuje konieczność lokalnej konfiguracji PHP.

**Uruchomienie testów:**

    ```bash
    # Uruchomienie PHPUnit wewnątrz kontenera Docker
    docker compose exec php ./vendor/bin/phpunit tests/unit
    
