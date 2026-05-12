# Financ Privus v2

Sistema Financeiro Empresarial — Laravel 13 + Livewire 3 + Tailwind CSS v4

## Stack

- **PHP 8.4** + **Laravel 13**
- **Livewire 3** + **Volt** (componentes reativos)
- **Tailwind CSS v4** via Vite (build local, sem CDN)
- **Alpine.js** (interatividade leve)
- **MySQL 8.4** + **Redis 7**
- **Spatie**: Permission, ActivityLog, Backup
- **Laravel Sanctum** (API tokens)
- **Laravel Telescope** (debug em desenvolvimento)

---

## Deploy no Coolify (automático)

O `docker-compose.yml` está configurado para subir **tudo junto** (app + MySQL + Redis) em um único deploy.

### Passo a passo

1. No Coolify: **New Resource → Docker Compose**
2. Informe a URL do repositório GitHub
3. Branch: `main`
4. Coolify detecta o `docker-compose.yml` e exibe todas as variáveis automaticamente
5. Preencha as variáveis obrigatórias (veja abaixo)
6. Clique em **Deploy**

### Variáveis obrigatórias no Coolify

| Variável | Descrição |
|---|---|
| `APP_KEY` | Gere com `php artisan key:generate --show` |
| `APP_URL` | URL do seu domínio (ex: `https://financeiro.seusite.com`) |
| `DB_PASSWORD` | Senha do banco MySQL |
| `DB_ROOT_PASSWORD` | Senha root do MySQL |

> As demais variáveis têm valores padrão e podem ser configuradas depois.

### Variáveis opcionais (integrações)

| Variável | Serviço |
|---|---|
| `OPENAI_API_KEY` | Dashboard com insights de IA |
| `EVOLUTION_API_URL` / `EVOLUTION_API_KEY` | WhatsApp |
| `MERCADOPAGO_ACCESS_TOKEN` | MercadoPago |
| `BANCO_ITAU_CLIENT_ID` / `SECRET` | Itaú Open Finance |
| `BANCO_BRADESCO_TOKEN` | Bradesco |
| `SICOOB_CLIENT_ID` / `SECRET` | Sicoob |
| `WOOCOMMERCE_URL` / `KEY` / `SECRET` | WooCommerce |

---

## Desenvolvimento local

```bash
# 1. Clonar
git clone https://github.com/julioandolfo/financ-privus-v2.git
cd financ-privus-v2

# 2. Dependências
composer install
npm install

# 3. Configurar ambiente
cp .env.example .env
php artisan key:generate

# 4. Banco de dados (ajustar .env com suas credenciais)
php artisan migrate

# 5. Iniciar servidores
php artisan serve &
npm run dev
```

### Com Docker local

```bash
cp .env.example .env
# Edite .env: defina APP_KEY, DB_PASSWORD, DB_ROOT_PASSWORD

docker compose up -d
```

Acesse: `http://localhost`

---

## Estrutura

```
app/
├── Http/Controllers/     # Resource controllers
│   └── Auth/             # Login, logout
├── Livewire/             # Componentes Livewire por módulo
├── Models/               # Eloquent Models
├── Jobs/                 # Queue jobs (ex-cron scripts)
└── Services/             # Lógica de negócio

resources/views/
├── components/
│   ├── layouts/          # sidebar, topbar, nav-item
│   └── ui/               # button, input, card, badge, stat-card
├── layouts/              # app.blade.php, guest.blade.php
├── auth/                 # login
└── dashboard/            # index

docker/
├── nginx/default.conf
├── php/php.ini, opcache.ini, php-fpm.conf
├── supervisor/supervisord.conf
└── entrypoint.sh
```

## O que o container roda automaticamente

- **PHP-FPM** (processa o Laravel)
- **Nginx** (serve a aplicação)
- **Queue Worker** (2 processos, retry automático)
- **Scheduler** (equivalente ao cron, roda a cada 60s)
- **Migrations** (executa `php artisan migrate` no startup)
