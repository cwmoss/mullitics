# mullitics

this is a experimental port of _nullitics_ by Serge Zaitsev https://nullitics.com/ to php

needs php 7.4. - 8.2 with extensions: intl, pdo-sqlite, json

## todo

- [x] queries!
- [ ] multisite via \_sites.db
- [x] daily autorotation of salt
- [x] connect geoip
- [ ] feed em logfiles!
- [ ] make it a middleware

## dead simple installation

    # download the source
    cd /some/path/outside/your/document-root
    curl -L https://github.com/cwmoss/mullitics/zipball/main -o tmp.zip; unzip tmp.zip; mv cwmoss* mullitics

    # change to the source path
    cd mullitics
    php setup/setup.php

    # set link to docroot
    cd htdocs
    ln -s ../mullitics/public/index.php friendly.php

    # include javascript in all of your pages
    <script defer src="/friendly.php?__script"></script>

    # visit your site with your browser, check if you encouter any errors
    https://yoursite.name

    # now you can look at your beautiful dashboard
    https://yoursite.name/friendly.php?__desk

    # you can optionally add geolite2 maxmind db for country detection via ip
    # download the GeoLite2-Country-CSV*.zip from your maxmind account
    php setup/geoip.php path/to/download.zip

## why?

nobody should send their visitors data to google analytics just because it's too hard to manage traffic data by themselfs.

looking for an easy solution for a friend with a one page site, who fancies a world map, i found _nullitics_. this really is a highly inspiring minimalistic approach. it has a beautiful dashboard and is written in go. i think it deserves to be ported to php. for some users that might be easier to run. i changed the store to sqlite. plus i want to be able to use one install for multiple sites. thats why i called it mullitics. phullitics would sound even more strange i think.

no personal data is stored. no cookie is set.

session/ visits are calculated like this:
`session = md5(ip adress + user agent header + date(ymd) + salt)`

this is by no means accurate. but for me -- and maybe for you too -- it is accurate enough. and it is easier than processing log files.

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

# credits

the idea is simple: just do it! count it! balance the respect for privacy with your own need to have some clues about the reception of your content. all credits to Serge Zaitsev https://zserge.com/
