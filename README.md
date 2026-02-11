# Challenge
Technical challenge using PHP, Docker, and other technologies.

# 📦 PicPay Simplificado — PHP 8.2 (Sem Framework)

Implementação de backend RESTful em **PHP puro**, seguindo princípios de:

- Clean Architecture
- Separação por camadas (Domain / Application / Infrastructure / HTTP)
- PSR-7 / PSR-15
- Transações atômicas
- Testes unitários
- Docker
- Logs estruturados

---

# 🧱 Arquitetura

app/
├── Domain/
│ ├── Contract/
│ ├── Exception/
│
├── Application/
│ └── V1/
│ └── UseCase/
│
├── Infrastructure/
│ ├── Persistence/
│ ├── External/
│ ├── Logging/
│ └── Container/
│
└── Http/
├── Controller/
├── Middleware/
└── Router.php


## Camadas

| Camada | Responsabilidade |
|---------|------------------|
| Domain | Regras e contratos |
| Application | Casos de uso |
| Infrastructure | Banco, HTTP externo, Logger |
| Http | Controllers, Middlewares |

---

# 🚀 Como Rodar o Projeto

## 🔹 1. Subir containers

Na raiz do projeto:

```bash
docker compose --profile infra up -d --build

```

Containers:
-`app`
-`db`(MySQL8)

## 🔹 2. Entrar no container da aplicação

```bash
docker compose --profile infra exec app bash
```

## 🔹 3. Subir servidor HTTP

Dentro do container:

```bash
php -S 0.0.0.0:8080 -t public
```

A aplicação ficará disponível em:

[`localhost:8080`](http://localhost:8080)


# 📡 Endpoints

## 🔹 Health Check

```http
GET /health
```

Resposta:

```json
{
  "status": "ok"
}
```


## 🔹 Criar Usuário

```http
POST /users
Content-Type: application/json
```

Body:
```json
{
  "fullName": "Fulano de Tal",
  "cpfCnpj": "12345678900",
  "email": "fulano@email.com",
  "password": "123456",
  "isMerchant": false
}
```

## 🔹 Transferência

```http
POST /transfer
Content-Type: application/json
```

Body:

```json
{
  "value": "10.00",
  "payer": 4,
  "payee": 15
}
```

# 🧠 Regras de Negócio Implementadas

✔ Usuário comum pode enviar e receber
✔ Lojista apenas recebe
✔ Não pode transferir para si mesmo
✔ Deve ter saldo suficiente
✔ Consulta serviço autorizador externo
✔ Operação é transacional (atômica)
✔ Notificação é best-effort (não desfaz transferência se falhar)

# 📊 Logs Estruturados

## Eventos gerados:

| Evento | Quando |
| :--- | :--- |
| `transfer.authorized` | Autorização externa OK |
| `transfer.created` | Transferência persistida |
| `transfer.failed` | Erro de regra de negócio |
| `notify.failed` | Falha ao notificar |

Exemplo de log:
```json
{
  "ts":"2026-02-11T22:07:17+00:00",
  "level":"ERROR",
  "event":"transfer.failed",
  "context":{
    "reason":"Saldo insuficiente.",
    "payer":4,
    "payee":15,
    "valor":"10.00"
  }
}
```

# 🧪 Testes Unitários

Localização:

```php
tests/Unit/Application/V1/CreateTransferTest.php
```
## Cobertura Atual

✔ Não permite transferir para si mesmo
✔ Saldo insuficiente

## Rodar testes

Dentro do container:
```bash
composer dump-autoload
vendor/bin/phpunit
```
Saída esperada:
```php
OK (2 tests, 4 assertions)
```
# 🛠 Banco de Dados

## Consultar carteiras
```bash
docker compose --profile infra exec db \
mysql -uapp -papp -Dapp \
-e "SELECT user_id, balance_cents FROM wallets;"
```

# 🔐 Status Codes

| Caso | Código |
| :--- | :--- |
| JSON inválido | `400` |
| Content-Type inválido | `415` |
| Campos ausentes | `422` |
| Regra de negócio | `422` |
| Usuário não encontrado | `404` |

(Atualmente DomainException retorna 400 — melhoria futura possível com ExceptionMiddleware.)

# 🧠 Decisões Técnicas

- Uso de bcmath para evitar erro de ponto flutuante
- Transações via TransactionManager
- Inversão de dependência via Container manual
- Sem framework propositalmente (requisito do desafio)
- Logger estruturado para observabilidade

# 📈 Possíveis Melhorias Futuras

- ExceptionMiddleware para mapear status codes corretamente
- Testes de integração com banco real
- Cobertura de código com Xdebug
- Autenticação JWT
- Histórico de transferências com paginação
- CI/CD completo com análise estática obrigatória
# 🏁 Conclusão

Projeto estruturado com:

✔ Arquitetura limpa
✔ Separação de responsabilidades
✔ Testes unitários
✔ Dockerizado
✔ Logs estruturados
✔ Preparado para evolução (V2 prevista no Router)


