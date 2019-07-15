Strukt Do
===

[![Latest Stable Version](https://poser.pugx.org/strukt/do/v/stable)](https://packagist.org/packages/strukt/do)
[![Total Downloads](https://poser.pugx.org/strukt/do/downloads)](https://packagist.org/packages/strukt/do)
[![Latest Unstable Version](https://poser.pugx.org/strukt/do/v/unstable)](https://packagist.org/packages/strukt/do)
[![License](https://poser.pugx.org/strukt/do/license)](https://packagist.org/packages/strukt/do)

`strukt-do` is the merging of [doctrine-orm](https://github.com/doctrine/doctrine2), [doctrine-migrations](https://github.com/doctrine/migrations) and [strukt-strukt](https://github.com/pitsolu/strukt-strukt) together.

## Getting Started

Install `strukt/strukt` framework, see [strukt-strukt](https://github.com/pitsolu/strukt-strukt) then install `strukt/do`

```
composer require strukt/do
composer exec publish-strukt-do
composer exec config-do
```

You now ready to go!

### DB Configuration File

```
cfg/
└── db.ini
```

### Console commands

Listing console commands: 

```sh
./console -l
```

There are 2 sets of commands available in `strukt-do` as listed below:

```sh
Strukt
 generate:app       Generate Application
 generate:router    Generate Module Router - ACCEPTS NO ARGS
 generate:module    Generate Application Module
 generate:loader    Generate Application Loader
 shell:exec         Strukt Shell Mode

Doctrine
 generate:models    Doctrine Generate Models
 generate:migration Doctrine Generate Migration
 generate:seeder    Generate Seeder
 migrate:exec       Doctrine Execute Migration
 seeder:exec        Execute Database Seeder
 sql:exec           Doctrine Run SQL
```

### Drop into shell

`strukt-do` uses [psysh](https://github.com/bobthecow/psysh).

```sh
$ ./console shell:exec
>>> ls
Variables: $core, $da, $registry
>>> $core->get("au.ctr.User")->getAll()
=> "AuthModule\Controller\User::getAll Not Yet Implemented!"
>>> $da->get("User")->findAll() #Doctrine Adapter
```

Have a good one!