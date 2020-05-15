# Behat Docker Image

A self-contained Docker image to run Behat with no external dependencies.

This image is part of the [Docksal](http://docksal.io) project.

Features:

- PHP7, Composer
- Behat 3.x
- DrupalExtension 3.x


## Usage

Use this image as if you were using a binary.  
Working directory is expected to be mounted at `/src` in the container.

```
$ docker run --rm -v $(pwd):/src docksal/behat --version
behat version 3.1.0
```

You can also add a shell alias (in `.bashrc`, `.zshrc`, etc.) for convenience.

```
alias behat='docker run --rm -v $(pwd):/src docksal/behat --colors "$@"'
```

Restart your shell or open a new one, then

```
$ behat --version
behat version 3.1.0
```


## Sample setup

Sample setup and tests can be found in the [example](example) folder.
 
Features:

- Sample tests
- Headless Selenium Chrome/Firefox support
- HTML report

### Using sample setup

```
git clone https://github.com/docksal/behat.git docksal-behat
cd docksal-behat/example
behat features/blackbox.feature
```

Note: if you did not add the shell alias, replace `behat` with `docker run --rm -v $(pwd):/src docksal/behat --colors`.


### Behat with Selenium

To run Behat tests that require a real browser (e.g. for JavaScript support) a headless Selenium Chrome/Firefox can be used.

There is a Docker Compose configuration in the example folder, that will get you up and running with a Selenium Chrome.

```
cd example
docker-compose up -d
./run-behat features/blackbox-javascript.feature
```

In this case, you get two containers - one running a built-in PHP server for access to HTML reports and one running Selenium. 
Behat runs within the first container and talks to the Selenium container to run tests with a real browser (Chrome/Firefox).

### Switching between Chrome and Firefox

1. Uncomment a respective line in `docker-compose.yml`:
 
    ```
    # Pick/uncomment one
    image: selenium/standalone-chrome
    #image: selenium/standalone-firefox
    ```

2. Update container configuration 

    ```
    docker-compose up -d
    ```

3. Update `behat.yml` as necessary
    Chrome
    ```
    browser_name: chrome
    selenium2:
      wd_host: http://browser:4444/wd/hub
      capabilities: { "browser": "chrome", "version": "*" }
    ```

    Firefox
    ```
    browser_name: firefox
    selenium2:
      wd_host: http://browser:4444/wd/hub
      capabilities: { "browser": "firefox", "version": "*" }
    ```
    
4. Run tests


### HTML report

HTML report will be generated into the `html_report` folder.  
It can be accessed by navigating to `http://<your-docker-host-ip>:8000/html_report` in your browser.  
Replace `<your-docker-host-ip>` as necessary (e.g. `localhost`).

### Bex Screenshot

The `Bex` extension will generate a screenshot when you have a failed step.

You can configure the folder that the screenshots are saved into through the `behat.yml` file. Check the example and look for `screenshot_directory` under  `Bex\Behat\ScreenshotExtension`.


## Debugging

The following command will start a bash session in the container.

```
docker run --rm -v $(pwd):/src -it --entrypoint=bash docksal/behat
```

## Usage in PhpStorm / IntelliJ
When using the Behat integration of PhpStorm, the folder `/opt/project` in the Docker service is used as project root. 
This will result in PhpStorm attempting to run the feature from this folder, instead of `/src`. 
An incorrectly generated run command will look like: 

```
[docker-compose://[{PROJECT_ROOT}/docker-compose.yml]:behat/]:php /opt/.phpstorm_helpers/behat.php --format PhpStormBehatFormatter --no-interaction --config /src/behat.yml /opt/project/features/helloworld.feature
```

Instead of 

```
[docker-compose://[{PROJECT_ROOT}/docker-compose.yml]:behat/]:php /opt/.phpstorm_helpers/behat.php --format PhpStormBehatFormatter --no-interaction --config /src/behat.yml /src/features/helloworld.feature
```

The default Docker project root location can only be configured in the IDEA configuration XML file, and not from the user interface.

1. Configure the `behat` Docker service as Remote PHP Interpreter using [the JetBrains instructions](https://www.jetbrains.com/help/phpstorm/configuring-remote-interpreters.html). Make sure to select **Docker compose** and not **Docker** from the dialog. Name the interpreter **behat**, this value is important because you will need it in step 2 and 3. 
2. Add a new `Behat by Remote Interpreter` under `Settings` > `Languages & Frameworks` > `PHP` > `Test Frameworks`. Follow the instructions in the section **Configure Behat manually [from the PhpStorm manual](https://www.jetbrains.com/help/phpstorm/using-behat-framework.html). Use these settings:
      * CLI interpreter: `behat` (the name you chose in step 1)
      * Path mappings: `<Project root>` -> `/src`
      * Path to Behat executable: `/opt/behat/bin/behat`
      * Default configuration file: `/src/behat.yml`
3. Edit the file `php.xml` in your project's `.idea` folder. Locate the `<remote_data>` tag under `project.component.interpreters.interpreter`. It may be that multiple `<remote_data>` tags exist. In this case, select the tag with the property `DOCKER_COMPOSE_SERVICE_NAME` set to the name of your interpreter from step 1 (e.g. **behat**).
  The tag should look like this:
    
   ```
   <remote_data DOCKER_ACCOUNT_NAME="Docker" DOCKER_COMPOSE_SERVICE_NAME="behat" DOCKER_REMOTE_PROJECT_PATH="/opt/project" INTERPRETER_PATH="php" HELPERS_PATH="/opt/.PhpStorm_helpers" INITIALIZED="false" VALID="true" RUN_AS_ROOT_VIA_SUDO="false">
   ```

3. Set the value of `DOCKER_REMOTE_PROJECT_PATH` to `/src`. The tag should now look like this: 

   ```
   <remote_data DOCKER_ACCOUNT_NAME="Docker" DOCKER_COMPOSE_SERVICE_NAME="behat" DOCKER_REMOTE_PROJECT_PATH="/src" INTERPRETER_PATH="php" HELPERS_PATH="/opt/.PhpStorm_helpers" INITIALIZED="false" VALID="true" RUN_AS_ROOT_VIA_SUDO="false">
   ```

4. Restart PhpStorm to reload the configuration change, and restart Docker for your changes to take effect.
