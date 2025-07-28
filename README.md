# cronWatcher Laravel Package


cronWatcher is a Laravel  package for pinging cron status on cronWatcher.io Server.
## Installation

```bash
composer require zzgtech/cronic
```
## Configuration in .env file

```bash
CRONIC_ACTIVE=true
CRONIC_CLIENT_NAME="*****"
CRONIC_URL="*****"
CRONIC_KEY="****"

```
## Synchronisation to Server

```bash
$ php artisan cronic:sync
```
