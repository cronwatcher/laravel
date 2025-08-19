# cronWatcher Laravel Package
[![Homepage](https://img.shields.io/badge/Homepage-cronwatcher.io-blue)](https://cronwatcher.io)

Laravel package for integrating with [CronWatcher.io](https://cronwatcher.io) to monitor and sync scheduled tasks.
## Installation

```bash
composer require cronwatcher/laravel
```
## Configuration in .env file

```bash
CRONWATCHER_KEY="****"

```
## Synchronisation to Server

```bash
$ php artisan cronwatcher:update
```
## 📌 Supported Versions

`cronwatcher/laravel` is released in separate major versions to match the corresponding Laravel release.  
Composer will automatically resolve and install the correct package version for your Laravel project.

| Laravel Version | Package Version | Git Branch   |
|-----------------|-----------------|--------------|
| **9.x**         | `^1.0`          | `laravel-9`  |
| **10.x**        | `^2.0`          | `laravel-10` |
| **11.x**        | `^3.0`          | `laravel-11` |
| **12.x**        | `^4.0`          | `laravel-12` |
