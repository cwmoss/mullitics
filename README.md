# mullitics

this is highly experimental

needs php 7.4. - 8.2 with extensions: intl, pdo-sqlite, json

## todo

    - queries!
    - multisite via _sites.db
    - daily autorotation of salt
    - connect geoip

## dead simple installation

    # get the source
    git clone https://github.com/cwmoss/mullitics.git
    # move it to your server, outside the document root
    # i use rsync but you can use scp or ftp...
    rsync -avz --exclude=var --exclude=.git . USERNAME@example.met:/path/outside/docroot
    # login to your host
    cd /path/outside/docroot/mullitics
    php setup/setup.php
    # set link to docroot
    cd htdocs
    ln -s ../mu/public/index.php ping.php
    # include javascript in all your pages
    <script defer src="/ping.php?__script"></script>

## why?

nobody should send their visitors data to google analytics just because it's too hard to manage traffic data by themselfs.

looking at easy solutions i found _nullitics_ it's a highly inspiring minimalistic approach. it's written in go. i think it deserves to be ported to php. for some users that might be easier to run. i changed the store to sqlite. plus i want to be able to use one install for multiple sites. thats why i called it mullitics. phullitics would sound even more strange i think.

no personal data is stored. no cookie is set.

session/ visits are calculated like this:
`session = md5(ip adress + user agent header + date(ymd) + salt)`

# integration options

### symbolic link to htdocs

    cd htdocs
    ln -s ../mu/public/index.php ping.php

### apache alias

    Alias /ping.png /Users/rw/dev/mullitics/public/index.php
    <Directory /Users/rw/dev/mullitics/public/>
        Require all granted
    </Directory>

### apache .htaccess

    RewriteEngine On
    RewriteRule ^ping.png index.php

# the script

put this in header, with the url of your installation as data-fun attribute

    <script defer data-fun="/ping.php">
        const f = document.currentScript.getAttribute('data-fun')
    new Image().src = f +
        '?u=' + encodeURI(location.href) +
        '&r=' + encodeURI(document.referrer) +
        '&d=' + screen.width;
    </script>

or fetch from installation

    <script defer src="/ping.php?__script"></script>
