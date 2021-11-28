# CurtainCallWP
A plugin for you to display your productions and their cast and crew.

## Development
**Setup**
1. Install docker if you don't already have it.
2. `cp .env.example .env`
3. In the `.env` file, fill in the `DB_DATABASE`, `DB_USER`, and `DB_PASSWORD` fields.
4. Add `127.0.0.1 wpsite.test` to your `/etc/hosts` file.
5. Run `scripts/setup_local.sh`
6. Visit `http://wpsite.test` in your browser and setup wordpress.
7. Activate the `CurtainCallWP` plugin.

**Note**<br>
The `docker-compose.yml` file also installs phpmyadmin. You can view the database at:
```
http://localhost:${PHP_MYADMIN_PORT}
```


