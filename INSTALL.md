Installation and Configuration
------------------------------

<!-- BEGIN-MARKDOWN-TOC -->
* [Base installation](#base-installation)
	* [Environment](#environment)
	* [Users](#users)
	* [Print Configuration](#print-configuration)
	* [Install required packages](#install-required-packages)
	* [Clone the `ape` Repository](#clone-the-ape-repository)
* [Project structure](#project-structure)
* [Workflow](#workflow)
	* [Processing Mail](#processing-mail)
	* [Printing](#printing)
	* [Setting up cronjobs](#setting-up-cronjobs)

<!-- END-MARKDOWN-TOC -->

## Project structure

Throughout the configuration, `<mailuser>` is a placeholder for an
actual username.

APE may live in the `<mailuser>` home directory

`/home/<mailuser>/ape`

and has the following directory structure

* `/composer/`    -   Mail parser Library
* `/docs/`        -   Documentation and License information
* `/history/`     -   Copy of already printed PDFs, sorted by days
* `/html/`        -   Web interface, accessed by Apache
* `/log/`         -   Logfiles
* `/queue/`       -   PDFs awaiting to be printed with next cronjob
* `/tmp/`         -   Incoming mails as HTML files

and important files

* `cronMagazinDruck.php`  -   executed by cronjob
* `cronScanauftrag.php`   -   executed by cronjob
* `init.php`              -   startup service
* `print.conf`            -   configuration file (directories & printers)
* `printJob.class.php`    -   called by init.php

## Base installation

### Environment

- Standard Debian Jessie Installation
- Open incoming port 25

### Users

Create a dedicated user `<mailuser>`

```
sudo useradd -d <mailuser>
```

### Print Configuration

Rename [`print.conf.example`](./print.conf.example) to `print.conf`
and adapt the paths to your system.

### Install required packages

- exim4
- cups
- apache2
- libapache2-mod-php5
- php-mime-email-parser
- wkthmltopdf
- git

### Clone the `ape` Repository

```
git clone https://github.com/wagneral/ape.git
```

## Workflow

### Processing Mail
- exim4 retrieves mails on incoming port 25 (!firewall restrictions)
  and redirects them all to the `<mailuser>` as specified in `/etc/aliases`

```
*: <mailuser>
```

- the `<mailuser>` forwards incoming mail to the ape script as specified
  in `/home/<mailuser>/.forward`

```
<mailuser>@servername.de,"|php -q /home/<mailuser>/ape/init.php"
```

### Printing

- the named script parses the mail and gathers the required
  information before printing via CUPS print server

### Setting up cronjobs

See [crontab example](./examples/config/crontab.debian)