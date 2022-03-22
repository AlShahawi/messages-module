# Messages Module
Simple one-to-one messaging system that can be easily turn onto group chatting system.

## Installation
Note: this project uses Laravel Sail Docker stack to unify the development environments on team machines.
```bash
./vendor/bin/sail up -d
```
The project will be hosted on: [http://localhost](http://localhost).

## Migrate and Seed the Database
```bash
./vendor/bin/sail artisan migrate --seed
```

## Running Tests
```bash
./vendor/bin/sail artisan test
```
