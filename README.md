# Contained

Contained is a quick and easy way to run a php website and manage it in a single container.

## Installation
### Method 1: Using the Docker Hub repository
Install [docker](https://www.docker.com/) on your machine.

Run the following command:
```bash
docker run -p 80:80 --name contained-container --restart unless-stopped -d nadavtasher/contained:latest
```
### Method 2: Building a docker image from source
Install [docker](https://www.docker.com/) on your machine.

[Clone the repository](https://github.com/NadavTasher/Contained/archive/master.zip), enter the extracted directory, then run the following commands:
```bash
docker build . -t contained
docker run -p 80:80 --name contained-container --restart unless-stopped -d contained
```

## Usage
Open `http://address/contained` to manage the deployment.

## Contributing
Pull requests are welcome, but only for smaller changer.
For larger changes, open an issue so that we could discuss the change.

Bug reports and vulnerabilities are welcome too. 

## License
[MIT](https://choosealicense.com/licenses/mit/)