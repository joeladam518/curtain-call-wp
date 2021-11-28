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
The `docker-compose.yml` file also installs phpMyAdmin. You can view the database at:
```
http://localhost:${PHP_MYADMIN_PORT}
```

## Building the plugin
You can build the plugin locally by running the build script.
Then you can install the generated zip file in seperate WordPress installation.
```shell
$ bash scrtipts/build.sh

# output: curtaincallwp.zip
```

The script takes and optional version argument attach to the file name.
```shell
$ bash scrtipts/build.sh 1.0.0

# output: curtaincallwp-1.0.0.zip
```


