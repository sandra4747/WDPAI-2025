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

