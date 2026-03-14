# URL Shortener

![Status](https://img.shields.io/badge/status-estudo-blue)
![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)
![Blade](https://img.shields.io/badge/Blade-Laravel-F7523F?logo=laravel&logoColor=white)
![Alpine.js](https://img.shields.io/badge/Alpine.js-3-8BC0D0?logo=alpinedotjs&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16+-4169E1?logo=postgresql&logoColor=white)
![Vite](https://img.shields.io/badge/Vite-7-646CFF?logo=vite&logoColor=white)
![License](https://img.shields.io/badge/license-MIT-green)

Projeto de estudos para praticar **PHP** e **Laravel** construindo um encurtador de URLs.

## Objetivo

- Entender melhor fluxo de desenvolvimento com Laravel.
- Praticar rotas, Eloquent, filas e organização de projeto.
- Evoluir boas praticas no backend com PHP.

## Stack

- PHP 8.4
- Laravel 12
- Blade
- Alpine.js
- PostgreSQL
- Node.js + npm (assets com Vite)

## Como rodar (local)

```bash
composer setup
composer dev
```

O comando `composer setup` instala dependencias, cria `.env` (se necessario), gera chave da app, roda migracoes e builda os assets.

Antes de rodar, configure o banco no `.env` com `DB_CONNECTION=pgsql` e as credenciais do seu PostgreSQL.

## Testes

```bash
composer test
```

## Observacoes

- Este repositorio e focado em aprendizado e experimentacao.
- A estrutura pode mudar com o avancar dos estudos.
