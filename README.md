# GEM: NovaPoshta Proxy

## Usage

List Cities  
```
https://np.vfscript.com/cities
```

Get City with Warehouses  
```
https://np.vfscript.com/cities/000655e6-4079-11de-b509-001d92f78698
```

## Setup
### Dev

Add to `/etc/hosts`
```
127.0.0.1 novaposhta.local
```

Set command aliases
```bash
source .bashrc
```

Start server
```bash
app_up
```

Schema
```bash
console doctrine:schema:diff
console doctrine:schema:migrate 
```


### Prod

Connect to server
```
ssh volfar00@volfar00.ftp.tools
r(mg4EfbFvH6qbQ{
```

Update dependencies
```
PATH=/usr/local/php72/bin:$PATH composer install
```

Database migration
```
PATH=/usr/local/php72/bin:$PATH bin/console doctrine:migrations:migrate
```
