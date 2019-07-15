Strukt Do
===

[![Latest Stable Version](https://poser.pugx.org/strukt/do/v/stable)](https://packagist.org/packages/strukt/do)
[![Total Downloads](https://poser.pugx.org/strukt/do/downloads)](https://packagist.org/packages/strukt/do)
[![Latest Unstable Version](https://poser.pugx.org/strukt/do/v/unstable)](https://packagist.org/packages/strukt/do)
[![License](https://poser.pugx.org/strukt/do/license)](https://packagist.org/packages/strukt/do)

## Getting Started

Project `strukt/do` is a `strukt` module.

`strukt/do` utilizes:

* [doctrine-orm](https://github.com/doctrine/doctrine2)
* [doctrine-migrations](https://github.com/doctrine/migrations)
* [strukt-strukt](https://github.com/pitsolu/strukt-strukt)


### Prerequisite

Install `strukt/strukt` framework via [strukt-strukt](https://github.com/pitsolu/strukt-strukt).

### Installation

Install, publish and create configuration for `strukt/do`:

```
composer require strukt/do
composer exec publish-strukt-do
composer exec config-do
```

### Database Configuration File

```
cfg/
└── db.ini
```

### Console

List the console commands: 
	
```sh
./console -l
```
Commands available in `strukt/do`:

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

### Shell

`strukt/do` console is [psysh](https://github.com/bobthecow/psysh).

Run console:

```sh
./console shell:exec
```

Example: 

```sh
>>> ls
Variables: $core, $da, $registry
>>> $core->get("au.ctr.User")->getAll()
=> "AuthModule\Controller\User::getAll Not Yet Implemented!"
>>> $da->get("User")->findAll() #Doctrine Adapter
```

Have a good one!