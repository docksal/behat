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
./run-behat features/blackbox.feature
```


### Behat with Selenium

To run Behat tests that require a real browser (e.g. for JavaScript support) 
a headless Selenium Chrome/Firefox can be used.

There is a Docker Compose configuration in the example folder, 
that will get you up and running with a Selenium Chrome

```
cd example
docker-compose up -d
./run-behat features/blackbox-javascript.feature
```


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


## Debugging

The following command will start a bash session in the container.

```
docker run --rm -v $(pwd):/src -it --entrypoint=bash docksal/behat
```
