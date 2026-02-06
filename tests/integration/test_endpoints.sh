#!/bin/bash

BASE_URL="http://localhost:8080"
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m'

echo -n "Test 1: GET /login ... "
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/login")
if [ "$HTTP_CODE" == "200" ]; then echo -e "${GREEN}OK${NC}"; else echo -e "${RED}FAIL ($HTTP_CODE)${NC}"; exit 1; fi

echo -n "Test 2: GET /nie-istnieje ... "
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/nie-istnieje")
if [ "$HTTP_CODE" == "404" ]; then echo -e "${GREEN}OK${NC}"; else echo -e "${RED}FAIL ($HTTP_CODE)${NC}"; exit 1; fi

echo -n "Test 3: POST /login (Błędne dane) ... "
RESPONSE=$(curl -s -d "email=test@test.pl&password=zle" -X POST "$BASE_URL/login")
if echo "$RESPONSE" | grep -q "Nieprawidłowy"; then 
    echo -e "${GREEN}OK (Kontroler odrzucił błędne dane)${NC}"
else 
    echo -e "${RED}FAIL (Kontroler nie zareagował)${NC}"; exit 1
fi

echo "Test 4: Symulacja awarii (Błąd 500)..."

DB_CONTAINER=$(docker ps --format "{{.Names}}" | grep -E "db|postgres" | head -n 1)

if [ -z "$DB_CONTAINER" ]; then
    echo -e "${RED}Nie znaleziono kontenera bazy danych! Pomiń ten test.${NC}"
else
    echo "Zatrzymuję bazę danych ($DB_CONTAINER) na chwilę..."
    docker stop "$DB_CONTAINER" > /dev/null

    echo -n "Sprawdzanie reakcji na awarię bazy... "
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" -d "email=crash@test.pl&password=crash" -X POST "$BASE_URL/login")
    docker start "$DB_CONTAINER" > /dev/null
    
    sleep 3

    if [ "$HTTP_CODE" == "500" ]; then
        echo -e "${GREEN}OK (Otrzymano kod 500 - aplikacja zgłosiła awarię)${NC}"
    else
        echo -e "${RED}FAIL (Otrzymano kod $HTTP_CODE zamiast 500)${NC}"
    fi
fi

