# Cs-cart code generator
## Purposes

## Features

## Getting started
### Generate
Get help
```
~: cd /path/to/ccg
~: php generator.php help
```
### Configure

### Test
```
~: cd /path/to/ccg/tests
~: phpunit --testdox
```
## Contributing
- Fork
- Code
- Pull request


## TODO
- Change namespaces to PSR-0
- Improve tests filesystem (split to directories)
- Copy files from resources to new addon destination on create addon command (ResourcesGenerator)
- Add method to LanguageGenerator to remove langvars by setting id (all variants for the setting and etc.)
- Improve tests coverage
- Add filesystem decorator for save history of files to be edited/removed